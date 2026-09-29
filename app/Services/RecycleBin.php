<?php

namespace App\Services;

use App\Models\AccountMaster;
use App\Models\CompanySetting;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\RecycleBinEntry;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Soft-deletes invoices, vouchers, receipts and payments into the recycle bin.
 *
 * Each delete keeps the side effects it has always had (stock returned, invoice
 * due amount adjusted, related vouchers removed) and records them in the entry's
 * `meta`, so restore() can reverse them exactly and purge() can remove the rows.
 */
class RecycleBin
{
    public const TYPES = ['invoice', 'voucher', 'receipt', 'payment'];

    public static function trashInvoice(Invoice $invoice): RecycleBinEntry
    {
        return DB::transaction(function () use ($invoice) {
            $voucherIds = Voucher::where('invoice_id', $invoice->id)->pluck('id')->all();
            // Receipts/payments rows were removed by the invoices foreign-key cascade on a hard delete.
            $receiptIds = Receipt::where('invoice_id', $invoice->id)->pluck('id')->all();
            $receiptVoucherIds = $receiptIds ? Voucher::whereIn('receipt_id', $receiptIds)->pluck('id')->all() : [];
            $items = InvoiceItem::where('invoice_id', $invoice->id)->get();

            $stock = [];
            AuditLogger::withoutAuditing(function () use ($items, &$stock, $voucherIds, $receiptIds, $receiptVoucherIds) {
                foreach ($items as $item) {
                    // 'Add' the item quantity back to inventory, as deleting always has.
                    $inventory = Inventory::find($item->inventory_id);
                    if ($inventory) {
                        $inventory->update(['quantity' => $inventory->quantity + $item->quantity]);
                        $stock[] = ['inventory_id' => $inventory->id, 'quantity' => (float) $item->quantity];
                    }
                    $item->delete();
                }
                Voucher::whereIn('id', array_merge($voucherIds, $receiptVoucherIds))->get()->each->delete();
                Receipt::whereIn('id', $receiptIds)->get()->each->delete();
            });

            $invoice->delete();

            return self::record($invoice, 'invoice', $invoice->invoice_number, $invoice->account_master_id, $invoice->total, [
                'voucher_ids' => array_merge($voucherIds, $receiptVoucherIds),
                'item_ids' => $items->pluck('id')->all(),
                'receipt_ids' => $receiptIds,
                'stock' => $stock,
            ]);
        });
    }

    public static function trashReceipt(Receipt $receipt): RecycleBinEntry
    {
        return DB::transaction(function () use ($receipt) {
            $voucherIds = Voucher::where('receipt_id', $receipt->id)->pluck('id')->all();
            $invoiceChange = null;

            if ($receipt->invoice_id != null && $voucherIds) {
                $invoiceChange = self::releaseInvoiceAmount($receipt->invoice_id, $receipt->amount);
            }

            Voucher::whereIn('id', $voucherIds)->get()->each->delete();
            $receipt->delete();

            return self::record($receipt, 'receipt', $receipt->receipt_number, $receipt->account_master_id, $receipt->amount, [
                'voucher_ids' => $voucherIds,
                'invoice' => $invoiceChange,
            ]);
        });
    }

    public static function trashPayment(Payment $payment): RecycleBinEntry
    {
        return DB::transaction(function () use ($payment) {
            $invoiceChange = $payment->invoice_id != null
                ? self::releaseInvoiceAmount($payment->invoice_id, $payment->amount)
                : null;

            $voucherIds = Voucher::where('payment_id', $payment->id)->pluck('id')->all();
            Voucher::whereIn('id', $voucherIds)->get()->each->delete();
            $payment->delete();

            return self::record($payment, 'payment', $payment->payment_number, $payment->account_master_id, $payment->amount, [
                'voucher_ids' => $voucherIds,
                'invoice' => $invoiceChange,
            ]);
        });
    }

    /**
     * Trash a manual voucher together with all of its legs.
     */
    public static function trashVoucher(Voucher $voucher): RecycleBinEntry
    {
        return DB::transaction(function () use ($voucher) {
            $legs = $voucher->related_voucher
                ? Voucher::where('related_voucher', $voucher->related_voucher)->get()
                : collect([$voucher]);

            $legs->each->delete();

            $party = $legs->pluck('account')->filter()->unique()->implode(' / ');

            return self::record($voucher, 'voucher', 'Voucher #' . $voucher->id, null, $legs->sum('debit'), [
                'voucher_ids' => $legs->pluck('id')->all(),
            ], $party);
        });
    }

    /**
     * Bring a trashed record back and re-apply what its delete undid.
     *
     * @throws RuntimeException when the record can no longer be restored safely.
     */
    public static function restore(RecycleBinEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $meta = $entry->meta ?? [];
            $model = self::trashedModel($entry);

            if (!$model) {
                throw new RuntimeException('This record no longer exists and cannot be restored.');
            }

            AuditLogger::withoutAuditing(function () use ($entry, $meta, $model) {
                if ($entry->resource_type === 'invoice') {
                    self::assertNumberFree(Invoice::class, 'invoice_number', $model);
                    self::retakeStock($meta['stock'] ?? [], $model->company_id);
                    InvoiceItem::onlyTrashed()->whereIn('id', $meta['item_ids'] ?? [])->restore();
                    Receipt::onlyTrashed()->whereIn('id', $meta['receipt_ids'] ?? [])->restore();
                }

                if (in_array($entry->resource_type, ['receipt', 'payment'], true)) {
                    self::assertNumberFree(get_class($model), $entry->resource_type . '_number', $model);
                    if (!empty($meta['invoice'])) {
                        self::reapplyInvoiceAmount($meta['invoice'], $model->amount);
                    }
                }

                Voucher::onlyTrashed()->whereIn('id', $meta['voucher_ids'] ?? [])->restore();
            });

            if ($entry->resource_type !== 'voucher') {
                $model->restore();
            }

            AuditLogger::log('restored', ucfirst($entry->resource_type) . ' ' . $entry->label . ' restored from recycle bin', $model);
            $entry->delete();
        });
    }

    /**
     * Permanently remove a trashed record and everything trashed with it.
     */
    public static function purge(RecycleBinEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $meta = $entry->meta ?? [];

            Voucher::onlyTrashed()->withoutGlobalScope('authenticated_company')->whereIn('id', $meta['voucher_ids'] ?? [])->forceDelete();
            InvoiceItem::onlyTrashed()->withoutGlobalScope('authenticated_company')->whereIn('id', $meta['item_ids'] ?? [])->forceDelete();
            Receipt::onlyTrashed()->withoutGlobalScope('authenticated_company')->whereIn('id', $meta['receipt_ids'] ?? [])->forceDelete();

            if ($entry->resource_type !== 'voucher') {
                $model = self::trashedModel($entry);
                if ($model) {
                    $model->forceDelete();
                }
            }

            $entry->delete();
        });
    }

    private static function record($model, string $type, $label, $accountMasterId, $amount, array $meta, $party = null): RecycleBinEntry
    {
        if ($party === null && $accountMasterId) {
            $party = AccountMaster::whereKey($accountMasterId)->value('name');
        }

        return RecycleBinEntry::create([
            'company_id' => $model->company_id,
            'resource_type' => $type,
            'resource_id' => $model->id,
            'label' => $label,
            'party' => $party,
            'amount' => $amount,
            'meta' => $meta,
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);
    }

    private static function trashedModel(RecycleBinEntry $entry)
    {
        $class = [
            'invoice' => Invoice::class,
            'voucher' => Voucher::class,
            'receipt' => Receipt::class,
            'payment' => Payment::class,
        ][$entry->resource_type] ?? null;

        return $class
            ? $class::onlyTrashed()->withoutGlobalScope('authenticated_company')
                ->where('company_id', $entry->company_id)->find($entry->resource_id)
            : null;
    }

    /**
     * Deleting a receipt/payment has always added its amount back to the invoice due
     * and reset the invoice status; remember the previous values for restore.
     */
    private static function releaseInvoiceAmount($invoiceId, $amount): ?array
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            return null;
        }

        $previous = [
            'invoice_id' => $invoice->id,
            'paid_status' => $invoice->paid_status,
            'status' => $invoice->status,
        ];

        $invoice->due_amount = ((int) $invoice->due_amount + (int) $amount);
        $invoice->paid_status = Invoice::STATUS_PAID;
        $invoice->status = Invoice::TO_BE_DISPATCH;
        $invoice->save();

        return $previous;
    }

    private static function reapplyInvoiceAmount(array $previous, $amount): void
    {
        $invoice = Invoice::find($previous['invoice_id']);
        if (!$invoice) {
            throw new RuntimeException('Its invoice has been deleted. Restore the invoice first.');
        }

        $invoice->due_amount = ((int) $invoice->due_amount - (int) $amount);
        $invoice->paid_status = $previous['paid_status'];
        $invoice->status = $previous['status'];
        $invoice->save();
    }

    private static function retakeStock(array $stock, $companyId): void
    {
        $allowNegative = 'YES' === CompanySetting::getSetting('allow_negative_inventory', $companyId);

        foreach ($stock as $line) {
            $inventory = Inventory::find($line['inventory_id']);
            if (!$inventory) {
                throw new RuntimeException('An inventory item on this invoice no longer exists.');
            }
            if (!$allowNegative && $inventory->quantity < $line['quantity']) {
                throw new RuntimeException("Not enough stock of \"{$inventory->name}\" to restore this invoice (needs {$line['quantity']}, has {$inventory->quantity}).");
            }
            $inventory->update(['quantity' => $inventory->quantity - $line['quantity']]);
        }
    }

    private static function assertNumberFree(string $class, string $column, $model): void
    {
        if (empty($model->{$column})) {
            return;
        }

        $taken = $class::where('company_id', $model->company_id)
            ->where($column, $model->{$column})
            ->whereKeyNot($model->id)
            ->exists();

        if ($taken) {
            throw new RuntimeException("Number {$model->{$column}} is already used by another record.");
        }
    }
}

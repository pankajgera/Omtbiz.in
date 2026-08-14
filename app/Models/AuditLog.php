<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'company_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_created_at',
        'module',
        'action_label',
        'document_number',
        'document_path',
        'change_summary',
    ];

    /**
     * Human-friendly labels for fields commonly changed on invoices,
     * estimates and orders. Anything not listed falls back to a
     * title-cased version of the column name.
     */
    const CHANGE_FIELD_LABELS = [
        'invoice_number' => 'Invoice Number',
        'estimate_number' => 'Estimate Number',
        'order_number' => 'Order Number',
        'reference_number' => 'Reference Number',
        'status' => 'Status',
        'paid_status' => 'Payment Status',
        'due_amount' => 'Due Amount',
        'total' => 'Total',
        'sub_total' => 'Subtotal',
        'tax' => 'Tax',
        'discount' => 'Discount',
        'discount_val' => 'Discount Amount',
        'notes' => 'Notes',
        'invoice_date' => 'Date',
        'estimate_date' => 'Date',
        'order_date' => 'Date',
        'date' => 'Date',
        'receipt_date' => 'Date',
        'due_date' => 'Due Date',
        'expiry_date' => 'Expiry Date',
        'sent' => 'Sent',
        'viewed' => 'Viewed',
    ];

    /**
     * Date-only fields that are stored/serialized as full timestamps —
     * formatted as plain dates in the change summary instead of raw
     * ISO/SQL datetime strings.
     */
    const CHANGE_FIELD_DATES = [
        'invoice_date',
        'estimate_date',
        'order_date',
        'date',
        'receipt_date',
        'due_date',
        'expiry_date',
    ];

    /**
     * Bookkeeping fields that change on every save but mean nothing to a
     * human reading the log — kept out of the "what changed" summary.
     */
    const CHANGE_FIELD_EXCLUDE = [
        'id',
        'created_at',
        'updated_at',
        'company_id',
        'user_id',
        'unique_hash',
        'invoice_template_id',
        'account_master_id',
        'dispatch_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCreatedAtAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        return Carbon::parse($this->created_at)->format('d/m/Y H:i:s');
    }

    public function getModuleAttribute()
    {
        if (!$this->auditable_type) {
            return 'Auth';
        }

        $map = [
            User::class => 'User',
            Invoice::class => 'Invoice',
            Orders::class => 'Order',
            Estimate::class => 'Estimate',
            Inventory::class => 'Inventory',
            InventoryItem::class => 'Inventory Item',
            Voucher::class => 'Voucher',
            Receipt::class => 'Receipt',
            Payment::class => 'Payment',
            Item::class => 'Bill-ty',
            Dispatch::class => 'Dispatch',
            Note::class => 'Note',
            AccountMaster::class => 'Account Master',
            AccountLedger::class => 'Account Ledger',
            AccountGroup::class => 'Account Group',
            Expense::class => 'Expense',
            ExpenseCategory::class => 'Expense Category',
            Bank::class => 'Bank',
            Company::class => 'Company',
            OrderItems::class => 'Order Item',
            InvoiceItem::class => 'Invoice Item',
            EstimateItem::class => 'Estimate Item',
        ];

        return $map[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    public function getActionLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Extract the document number (e.g. invoice/estimate/order number) from
     * the generated description, so the UI can show/search "INV-0001"
     * instead of a raw record id. Returns null for name-based labels
     * (e.g. Users, Companies) since those aren't document numbers.
     */
    public function getDocumentNumberAttribute()
    {
        if (!$this->description) {
            return null;
        }

        if (preg_match('/^\S+\s+(.+?)\s+was\s+(?:created|updated|deleted)$/i', $this->description, $matches)) {
            $value = $matches[1];

            if (strpos($value, '"') === 0) {
                return null;
            }

            return $value;
        }

        return null;
    }

    /**
     * Readable "what changed" breakdown for an update event, e.g.
     * [{ field: 'Due Amount', old: '1,500.00', new: '1,200.00' }, ...].
     * Empty for create/delete/auth events, or if only bookkeeping fields
     * changed.
     */
    public function getChangeSummaryAttribute()
    {
        if ($this->action !== 'updated' || !is_array($this->old_values) || !is_array($this->new_values)) {
            return [];
        }

        $changes = [];

        foreach ($this->new_values as $field => $newValue) {
            if (in_array($field, self::CHANGE_FIELD_EXCLUDE, true)) {
                continue;
            }

            $oldValue = array_key_exists($field, $this->old_values) ? $this->old_values[$field] : null;

            if ($oldValue === $newValue) {
                continue;
            }

            $changes[] = [
                'field' => self::CHANGE_FIELD_LABELS[$field] ?? ucwords(str_replace('_', ' ', $field)),
                'old' => $this->formatChangeValue($field, $oldValue),
                'new' => $this->formatChangeValue($field, $newValue),
            ];
        }

        return $changes;
    }

    protected function formatChangeValue($field, $value)
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (in_array($field, ['total', 'sub_total', 'tax', 'discount_val', 'due_amount'], true) && is_numeric($value)) {
            return number_format((float) $value, 2);
        }

        if (in_array($field, self::CHANGE_FIELD_DATES, true)) {
            try {
                return Carbon::parse($value)->format('d/m/Y');
            } catch (\Throwable $e) {
                return (string) $value;
            }
        }

        return (string) $value;
    }

    /**
     * Front-end route path (relative to the app) to open the record this
     * log entry belongs to, e.g. "invoices/12/view". Null for record types
     * that don't have a viewable page, or once the record no longer exists.
     */
    /**
     * Per-request memo of which (type, id) records still exist, so the
     * activity list doesn't run a fresh existence check for every row when
     * several entries reference the same record.
     */
    protected static $existenceCache = [];

    public function getDocumentPathAttribute()
    {
        if (!$this->auditable_id) {
            return null;
        }

        // Prefix each record type opens to — always its edit page.
        $prefixes = [
            Invoice::class => 'invoices',
            Estimate::class => 'estimates',
            Orders::class => 'orders',
            Receipt::class => 'receipts',
            Voucher::class => 'vouchers',
            Inventory::class => 'inventory',
        ];

        if (!isset($prefixes[$this->auditable_type])) {
            return null;
        }

        // The record may have been deleted since this log entry was
        // written (even a Created/Updated row can outlive its record) —
        // don't link to a page that will error out.
        if (!$this->documentStillExists($this->auditable_type, $this->auditable_id)) {
            return null;
        }

        return $prefixes[$this->auditable_type] . '/' . $this->auditable_id . '/edit';
    }

    protected function documentStillExists($class, $id)
    {
        $key = $class . ':' . $id;

        if (!array_key_exists($key, self::$existenceCache)) {
            self::$existenceCache[$key] = $class::whereKey($id)->exists();
        }

        return self::$existenceCache[$key];
    }

    public function scopeWhereCompany($query, $companyId)
    {
        if ($companyId) {
            return $query->where('company_id', $companyId);
        }

        return $query;
    }

    public function scopeApplyFilters($query, array $filters)
    {
        if (!empty($filters['user'])) {
            $user = $filters['user'];
            $query->where(function ($q) use ($user) {
                $q->where('user_name', 'LIKE', '%' . $user . '%')
                    ->orWhere('user_email', 'LIKE', '%' . $user . '%');
            });
        }

        if (!empty($filters['action'])) {
            $actions = is_array($filters['action']) ? $filters['action'] : explode(',', $filters['action']);
            $actions = array_filter(array_map('trim', $actions));

            if ($actions) {
                $query->whereIn('action', $actions);
            }
        }

        if (!empty($filters['module'])) {
            $modules = is_array($filters['module']) ? $filters['module'] : explode(',', $filters['module']);
            $typeMap = [
                'auth' => null,
                'user' => User::class,
                'invoice' => Invoice::class,
                'order' => Orders::class,
                'estimate' => Estimate::class,
                'inventory' => Inventory::class,
                'voucher' => Voucher::class,
                'receipt' => Receipt::class,
                'payment' => Payment::class,
                'item' => Item::class,
                'dispatch' => Dispatch::class,
                'note' => Note::class,
                'master' => AccountMaster::class,
                'ledger' => AccountLedger::class,
                'group' => AccountGroup::class,
                'expense' => Expense::class,
                'bank' => Bank::class,
                'company' => Company::class,
            ];

            $types = [];
            $includeAuth = false;

            foreach ($modules as $module) {
                $module = strtolower(trim($module));

                if (!array_key_exists($module, $typeMap)) {
                    continue;
                }

                if ($typeMap[$module] === null) {
                    $includeAuth = true;
                } else {
                    $types[] = $typeMap[$module];
                }
            }

            if ($types || $includeAuth) {
                $query->where(function ($q) use ($types, $includeAuth) {
                    if ($types) {
                        $q->orWhereIn('auditable_type', $types);
                    }
                    if ($includeAuth) {
                        $q->orWhereNull('auditable_type');
                    }
                });
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            // Match either the document number (embedded in the description,
            // e.g. "Invoice INV-0001 was created") or the customer it
            // belongs to, since a client may only remember the customer's
            // name rather than the invoice/estimate/order number.
            $customerMatches = [];
            foreach ([Invoice::class, Estimate::class, Orders::class, Receipt::class] as $class) {
                $ids = $class::whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('contact_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('company_name', 'LIKE', '%' . $search . '%');
                })->pluck('id');

                if ($ids->isNotEmpty()) {
                    $customerMatches[$class] = $ids;
                }
            }

            $query->where(function ($q) use ($search, $customerMatches) {
                $q->where('description', 'LIKE', '%' . $search . '%');

                foreach ($customerMatches as $class => $ids) {
                    $q->orWhere(function ($q2) use ($class, $ids) {
                        $q2->where('auditable_type', $class)->whereIn('auditable_id', $ids);
                    });
                }
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query;
    }
}

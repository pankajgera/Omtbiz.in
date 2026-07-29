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
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['module'])) {
            $module = $filters['module'];
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

            if (array_key_exists(strtolower($module), $typeMap)) {
                $type = $typeMap[strtolower($module)];
                if ($type === null) {
                    $query->whereNull('auditable_type');
                } else {
                    $query->where('auditable_type', $type);
                }
            }
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

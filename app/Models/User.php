<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Conversation;
use carbon\carbon;
use App\Models\MemberLoan;
use App\Models\Address;
use App\Models\Payment;
use App\Models\Company;
use App\Notifications\MailResetPasswordNotification;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Exception;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'company_id',
        'company_name',
        'role',
        'password',
        'facebook_id',
        'google_id',
        'github_id',
        'group_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = [
        'currency'
    ];

    protected $appends = [
        'formattedCreatedAt',
        'avatar'
    ];

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return \App\User
     */
    public function findForPassport($username)
    {
        return $this->where('email', $username)->first();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAccountant()
    {
        return $this->role === 'accountant';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public static function login($request)
    {
        $remember = $request->remember;
        $email = $request->email;
        $password = $request->password;
        return (\Auth::attempt(array('email' => $email, 'password' => $password), $remember));
    }

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->company_id);
        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function estimates()
    {
        return $this->hasMany(Estimate::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(Address::class)->where('type', Address::BILLING_TYPE);
    }

    public function shippingAddress()
    {
        return $this->hasOne(Address::class)->where('type', Address::SHIPPING_TYPE);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Override the mail body for reset password notification mail.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordNotification($token));
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $term . '%');
            });
        }
    }

    public function scopeWhereContactName($query, $contactName)
    {
        return $query->where('contact_name', 'LIKE', '%' . $contactName . '%');
    }

    public function scopeWhereDisplayName($query, $displayName)
    {
        return $query->where('name', 'LIKE', '%' . $displayName . '%');
    }

    public function scopeWherePhone($query, $phone)
    {
        return $query->where('phone', 'LIKE', '%' . $phone . '%');
    }

    public function scopeCustomer($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeWhereEmail($query, $email)
    {
        return $query->where('email', 'LIKE', '%' . $email . '%');
    }

    public function scopeWhereRole($query, $role)
    {
        return $query->where('role', 'LIKE', '%' . $role . '%');
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('display_name')) {
            $query->whereDisplayName($filters->get('display_name'));
        }

        if ($filters->get('email')) {
            $query->whereEmail($filters->get('email'));
        }

        if ($filters->get('role')) {
            $query->whereRole($filters->get('role'));
        }
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('users.company_id', $company_id);
    }

    public function scopeApplyInvoiceFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('d/m/Y', $filters->get('from_date'));
            $end = Carbon::createFromFormat('d/m/Y', $filters->get('to_date'));
            $query->invoicesBetween($start, $end);
        }
    }

    public function scopeInvoicesBetween($query, $start, $end)
    {
        $query->whereHas('invoices', function ($query) use ($start, $end) {
            $query->whereBetween(
                'invoice_date',
                [$start->format('Y-m-d'), $end->format('Y-m-d')]
            );
        });
    }

    public static function deleteCustomer($id)
    {
        $customer = self::find($id);

        if ($customer->estimates()->exists()) {
            $customer->estimates()->delete();
        }

        if ($customer->invoices()->exists()) {
            $customer->invoices()->delete();
        }

        if ($customer->payments()->exists()) {
            $customer->payments()->delete();
        }

        if ($customer->addresses()->exists()) {
            $customer->addresses()->delete();
        }

        $customer->delete();

        return true;
    }

    public function getAvatarAttribute()
    {
        $avatar = $this->getMedia('admin_avatar')->first();
        if ($avatar) {
            return  asset($avatar->getUrl());
        }
        return;
    }

    public function deleteUser()
    {
        try {
            $this->delete();
            return true;
        } catch (Exception $e) {
            return ['error_message' => $e->getMessage()];
        }
    }

    public function scopeAddedUsers($query)
    {
        return $query->where('role', '!=', 'customer');
    }
}

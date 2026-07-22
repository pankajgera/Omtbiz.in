<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Prevent recursive logging while writing audit rows.
     *
     * @var bool
     */
    protected static $enabled = true;

    public static function disable()
    {
        static::$enabled = false;
    }

    public static function enable()
    {
        static::$enabled = true;
    }

    public static function isEnabled()
    {
        return static::$enabled;
    }

    /**
     * Run a callback without writing audit logs (for cascading side-effects).
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutAuditing(callable $callback)
    {
        $previous = static::$enabled;
        static::$enabled = false;

        try {
            return $callback();
        } finally {
            static::$enabled = $previous;
        }
    }

    /**
     * @param  string  $action
     * @param  string|null  $description
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @param  array|null  $oldValues
     * @param  array|null  $newValues
     * @param  \App\Models\User|null  $actor
     * @return \App\Models\AuditLog|null
     */
    public static function log(
        $action,
        $description = null,
        ?Model $model = null,
        $oldValues = null,
        $newValues = null,
        $actor = null
    ) {
        if (!static::$enabled) {
            return null;
        }

        try {
            static::$enabled = false;

            $user = $actor ?: Auth::user();
            $request = request();

            $companyId = null;
            if ($request) {
                $companyId = $request->header('company') ?: $request->input('company_id');
            }
            if (!$companyId && $user) {
                $companyId = $user->company_id;
            }
            if (!$companyId && $model && isset($model->company_id)) {
                $companyId = $model->company_id;
            }

            return AuditLog::create([
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : null,
                'user_email' => $user ? $user->email : null,
                'company_id' => $companyId,
                'action' => $action,
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id' => $model ? $model->getKey() : null,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 1000) : null,
                'url' => $request ? substr((string) $request->fullUrl(), 0, 500) : null,
                'method' => $request ? $request->method() : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break the main request because of audit logging.
            report($e);

            return null;
        } finally {
            static::$enabled = true;
        }
    }

    public static function login($user)
    {
        return static::log(
            'login',
            sprintf('%s logged in', $user->name ?? $user->email),
            null,
            null,
            null,
            $user
        );
    }

    public static function logout($user)
    {
        return static::log(
            'logout',
            sprintf('%s logged out', $user->name ?? $user->email),
            null,
            null,
            null,
            $user
        );
    }

    public static function failedLogin($email)
    {
        return static::log(
            'login_failed',
            sprintf('Failed login attempt for %s', $email),
            null,
            null,
            ['email' => $email]
        );
    }
}

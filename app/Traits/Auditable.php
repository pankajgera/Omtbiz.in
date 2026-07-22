<?php

namespace App\Traits;

use App\Services\AuditLogger;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditLogger::log(
                'created',
                static::auditDescription($model, 'created'),
                $model,
                null,
                static::auditAttributes($model)
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            AuditLogger::log(
                'updated',
                static::auditDescription($model, 'updated'),
                $model,
                static::filterAuditAttributes($old),
                static::filterAuditAttributes($changes)
            );
        });

        static::deleted(function ($model) {
            AuditLogger::log(
                'deleted',
                static::auditDescription($model, 'deleted'),
                $model,
                static::auditAttributes($model),
                null
            );
        });
    }

    protected static function auditDescription($model, $action)
    {
        $label = method_exists($model, 'getAuditLabel')
            ? $model->getAuditLabel()
            : (class_basename($model) . ' #' . $model->getKey());

        return sprintf('%s was %s', $label, $action);
    }

    protected static function auditAttributes($model)
    {
        return static::filterAuditAttributes($model->attributesToArray());
    }

    protected static function filterAuditAttributes(array $attributes)
    {
        $hidden = [
            'password',
            'remember_token',
            'api_token',
        ];

        foreach ($hidden as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    public function getAuditLabel()
    {
        if (isset($this->name) && $this->name) {
            return class_basename($this) . ' "' . $this->name . '"';
        }

        foreach (['invoice_number', 'order_number', 'estimate_number', 'payment_number', 'email'] as $field) {
            if (isset($this->{$field}) && $this->{$field}) {
                return class_basename($this) . ' ' . $this->{$field};
            }
        }

        return class_basename($this) . ' #' . $this->getKey();
    }
}

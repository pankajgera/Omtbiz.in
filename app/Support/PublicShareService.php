<?php

namespace App\Support;

use App\Models\PublicShare;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PublicShareService
{
    public const DOCUMENT_TYPES = ['invoice', 'estimate', 'receipt', 'expense'];

    public const REPORT_TYPES = [
        'sales-customers',
        'sales-items',
        'expenses',
        'profit-loss',
        'customers',
        'banks',
    ];

    public function document(Model $resource, string $type, ?int $createdBy): PublicShare
    {
        $share = PublicShare::withoutGlobalScopes()
            ->active()
            ->where('company_id', $resource->company_id)
            ->where('resource_type', $type)
            ->where('resource_id', $resource->getKey())
            ->latest('id')
            ->first();

        if ($share) {
            return $share;
        }

        $preferredToken = in_array($type, ['invoice', 'estimate'], true)
            ? $resource->unique_hash
            : null;

        if ($preferredToken && PublicShare::withoutGlobalScopes()->where('token', $preferredToken)->exists()) {
            $preferredToken = null;
        }

        $token = $preferredToken ?: $this->newToken();

        if (in_array($type, ['invoice', 'estimate'], true) && $resource->unique_hash !== $token) {
            $resource->forceFill(['unique_hash' => $token]);

            if ($resource->exists) {
                $resource->saveQuietly();
            }
        }

        return PublicShare::withoutGlobalScopes()->create([
            'token' => $token,
            'company_id' => $resource->company_id,
            'created_by' => $createdBy,
            'resource_type' => $type,
            'resource_id' => $resource->getKey(),
        ]);
    }

    public function report(int $companyId, int $createdBy, string $type, array $parameters): PublicShare
    {
        return PublicShare::withoutGlobalScopes()->create([
            'token' => $this->newToken(),
            'company_id' => $companyId,
            'created_by' => $createdBy,
            'resource_type' => 'report:' . $type,
            'parameters' => $parameters,
        ]);
    }

    public function url(PublicShare $share): string
    {
        return match ($share->resource_type) {
            'invoice' => route('get.invoice.pdf', $share->token),
            'estimate' => route('get.estimate.pdf', $share->token),
            'receipt' => route('get.receipt.pdf', $share->token),
            'expense' => route('download.expense.receipt', $share->token),
            'report:sales-customers' => route('get.sales.customers', $share->token),
            'report:sales-items' => route('get.sales.items', $share->token),
            'report:expenses' => route('get.expenses.reports', $share->token),
            'report:profit-loss' => route('get.profit.loss', $share->token),
            'report:customers' => route('get.customers', $share->token),
            'report:banks' => route('get.banks', $share->token),
        };
    }

    private function newToken(): string
    {
        do {
            $token = Str::random(64);
        } while (PublicShare::withoutGlobalScopes()->where('token', $token)->exists());

        return $token;
    }
}

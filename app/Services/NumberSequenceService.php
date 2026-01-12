<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\Invoice;
use Carbon\Carbon;

class NumberSequenceService
{
    public static function getNextSequence(int $companyId, Carbon $date): array
    {
        $year = $date->year;
        $prefix = 'EST' . $year . '-';
        $lastNumber = self::getLastNumberForYear($companyId, $year);

        if ($lastNumber) {
            $parsed = self::parseNumber($lastNumber);
            if ($parsed) {
                [$series, $left, $right] = self::incrementSequence(
                    $parsed['series'],
                    $parsed['left'],
                    $parsed['right']
                );
                return self::buildSequence($year, $series, $left, $right);
            }
        }

        return self::buildSequence($year, 'A', 0, 1);
    }

    public static function getReferenceNumberForAccount(int $companyId, int $accountMasterId, Carbon $date): ?string
    {
        $dateValue = $date->toDateString();
        $firstInvoice = Invoice::where('company_id', $companyId)
            ->where('account_master_id', $accountMasterId)
            ->where('invoice_date', $dateValue)
            ->orderBy('created_at', 'asc')
            ->first();

        $firstEstimate = Estimate::where('company_id', $companyId)
            ->where('account_master_id', $accountMasterId)
            ->where('estimate_date', $dateValue)
            ->orderBy('created_at', 'asc')
            ->first();

        if ($firstInvoice && $firstEstimate) {
            return $firstInvoice->created_at <= $firstEstimate->created_at
                ? $firstInvoice->reference_number
                : $firstEstimate->reference_number;
        }

        if ($firstInvoice) {
            return $firstInvoice->reference_number;
        }

        if ($firstEstimate) {
            return $firstEstimate->reference_number;
        }

        return null;
    }

    public static function parseNumber(string $number): ?array
    {
        if (preg_match('/^EST(\d{4})-([A-Z])-([0-9]{2})\\/([0-9]{2})$/', $number, $matches)) {
            return [
                'year' => (int) $matches[1],
                'series' => $matches[2],
                'left' => (int) $matches[3],
                'right' => (int) $matches[4],
            ];
        }

        return null;
    }

    public static function incrementSequence(string $series, int $left, int $right): array
    {
        if ($left === 99 && $right === 99) {
            return [self::nextSeries($series), 0, 1];
        }

        if ($right === 99) {
            return [$series, $left + 1, 1];
        }

        return [$series, $left, $right + 1];
    }

    public static function buildSequence(int $year, string $series, int $left, int $right): array
    {
        $prefix = 'EST' . $year . '-';
        $suffix = sprintf('%s-%02d/%02d', $series, $left, $right);

        return [
            'year' => $year,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'series' => $series,
            'left' => $left,
            'right' => $right,
            'invoice_number' => $prefix . $suffix,
            'reference_number' => $suffix,
        ];
    }

    private static function getLastNumberForYear(int $companyId, int $year): ?string
    {
        $prefix = 'EST' . $year . '-';
        $lastInvoice = Invoice::where('company_id', $companyId)
            ->where('invoice_number', 'LIKE', $prefix . '%')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastEstimate = Estimate::where('company_id', $companyId)
            ->where('estimate_number', 'LIKE', $prefix . '%')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $lastInvoice && ! $lastEstimate) {
            return null;
        }

        if (! $lastEstimate) {
            return $lastInvoice->invoice_number;
        }

        if (! $lastInvoice) {
            return $lastEstimate->estimate_number;
        }

        return $lastInvoice->created_at >= $lastEstimate->created_at
            ? $lastInvoice->invoice_number
            : $lastEstimate->estimate_number;
    }

    private static function nextSeries(string $series): string
    {
        $upper = strtoupper($series);
        if ($upper < 'A' || $upper > 'Z') {
            return 'A';
        }

        if ($upper === 'Z') {
            return 'A';
        }

        return chr(ord($upper) + 1);
    }
}

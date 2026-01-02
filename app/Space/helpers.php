<?php
use App\Models\CompanySetting;
use App\Models\Currency;

/**
 * Set Active Path
 *
 * @param $path
 * @param string $active
 * @return string
 */
function set_active($path, $active = 'active') {

    return call_user_func_array('Request::is', (array)$path) ? $active : '';

}

/**
 * @param $path
 * @return mixed
 */
function is_url($path)
{
    return call_user_func_array('Request::is', (array)$path);
}

/**
 * @param $string
 * @return string
 */
function clean_slug($string)
{
    // Replaces all spaces with hyphens.
    $string = str_replace(' ', '-', $string);

    // Removes special chars.
    return \Illuminate\Support\Str::lower(preg_replace('/[^A-Za-z0-9\-]/', '', $string));
}

/**
 * @param $money
 * @return formated_money
 */
function format_money_pdf($money, $currency = null)
{
    if (!$currency) {
        $currency = Currency::findOrFail(CompanySetting::getSetting('currency', 1));
    }

    $format_money = number_format(
        $money,
        $currency->precision,
        $currency->decimal_separator,
        $currency->thousand_separator
    );

    $currency_with_symbol = '';
    if ($currency->swap_currency_symbol) {
        $currency_with_symbol = $format_money.'<span style="font-family: DejaVu Sans;">'.$currency->symbol.'</span>';
    } else {
        $currency_with_symbol = '<span style="font-family: DejaVu Sans;">'.$currency->symbol.'</span>'.$format_money;
    }
    return $currency_with_symbol;
}

/**
 * Normalize a numeric string by placing a decimal before the last digit.
 *
 * @param mixed $value
 * @return mixed
 */
function normalize_second_last_decimal($value)
{
    if ($value === null || $value === '') {
        return $value;
    }

    $string = (string) $value;
    $is_negative = false;

    if (strlen($string) && $string[0] === '-') {
        $is_negative = true;
        $string = substr($string, 1);
    }

    $digits = preg_replace('/\D+/', '', $string);
    if ($digits === '') {
        return $value;
    }

    if (strlen($digits) === 1) {
        $formatted = '0.' . $digits;
    } else {
        $formatted = substr($digits, 0, -1) . '.' . substr($digits, -1);
    }

    return $is_negative ? '-' . $formatted : $formatted;
}

/**
 * Normalize a numeric value to 2 decimal places.
 *
 * @param mixed $value
 * @return mixed
 */
function normalize_two_decimal($value)
{
    if ($value === null || $value === '') {
        return $value;
    }

    $string = str_replace([',', ' '], '', (string) $value);
    if ($string === '') {
        return $value;
    }

    $is_negative = $string[0] === '-';
    $unsigned = $is_negative ? substr($string, 1) : $string;

    if (is_numeric($unsigned) && strpos($unsigned, '.') !== false) {
        $formatted = number_format((float) $unsigned, 2, '.', '');
        return $is_negative ? '-' . $formatted : $formatted;
    }

    $digits = preg_replace('/\D+/', '', $unsigned);
    if ($digits === '') {
        return $value;
    }

    if (strlen($digits) === 1) {
        $formatted = '0.0' . $digits;
    } elseif (strlen($digits) === 2) {
        $formatted = '0.' . $digits;
    } else {
        $formatted = substr($digits, 0, -2) . '.' . substr($digits, -2);
    }

    return $is_negative ? '-' . $formatted : $formatted;
}

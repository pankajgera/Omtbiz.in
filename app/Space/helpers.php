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
 * Format a number with Indian digit grouping: 3250555.5 => "32,50,555.50".
 *
 * @param float|int|string|null $number
 * @param int $decimals
 * @return string
 */
function format_inr($number, $decimals = 2)
{
    $number = round((float) $number, $decimals);
    $sign = $number < 0 ? '-' : '';
    [$whole, $fraction] = array_pad(explode('.', number_format(abs($number), $decimals, '.', '')), 2, '');

    // Last three digits form one group; everything before that is grouped in pairs.
    if (strlen($whole) > 3) {
        $head = substr($whole, 0, -3);
        $whole = preg_replace('/\B(?=(\d{2})+$)/', ',', $head) . ',' . substr($whole, -3);
    }

    return $sign . $whole . ($decimals > 0 ? '.' . $fraction : '');
}

/**
 * @param $money
 * @return formated_money
 */
function format_money_pdf($money, $currency = null)
{
    $money = $money / 100;

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

<?php


use App\Models\ProductPayment;
use Illuminate\Support\Str;

if (!function_exists('autonumber')) {
    /**
     * Returns a human readable file size
     *
     * @param integer $bytes
     * Bytes contains the size of the bytes to convert
     *
     * @param integer $decimals
     * Number of decimal places to be returned
     *
     * @return string a string in human readable format
     *
     * */
    function autonumber()
    {
        $last = ProductPayment::orderBy("created_at", "DESC")->first();
        $number = 1;
        if ($last != null) {
            $number = intval(substr($last->no_transaction, -6)) + 1;
        }
        $number = str_pad($number, 6, "0", STR_PAD_LEFT);
        $current_month = date("m");
        $current_year = date("Y");
        

        return Str::random(5) . $current_month . $current_year .  $number;
    }
}

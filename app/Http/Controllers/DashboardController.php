<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getMonthTransaction() {
        $query = "SELECT DISTINCT Month(created_at) as bulan FROM product_payments order by bulan asc";
        $result = DB::select($query);
        return response()->json($result, 200);
    }

    public function getYearTransaction() {
        $query = "SELECT DISTINCT YEAR(created_at) as tahun FROM product_payments order by tahun desc";
        $result = DB::select($query);
        return response()->json($result, 200);
    }

    public function grafikVolumeTransaction($tahun) {
        $query = "SELECT
            b.name AS category,
            MONTH(a.created_at) AS bulan,
            SUM(a.amount) AS jumlah
            FROM
                product_payments a
            INNER JOIN categories b ON
                a.category_id = b.id
            WHERE
                YEAR(a.created_at) = $tahun and a.status = 'success'
            GROUP BY
                b.name,
                MONTH(a.created_at)";

        $result = DB::select($query);

        return response()->json($result, 200);
    }

    public function grafikVolumeTransactionBySim($tahun) {
        $query = "SELECT
            a.sim_type AS sim_type,
            YEAR(a.created_at) AS tahun,
            SUM(b.amount) AS jumlah
            FROM
                product_results a
            INNER JOIN product_payments b ON
                a.product_payment_id = b.id
            WHERE
                YEAR(a.created_at) = $tahun and b.status = 'success'
            GROUP BY
                a.sim_type,
                YEAR(a.created_at)";

        $result = DB::select($query);

        return response()->json($result, 200);
    }

    public function grafikVolumeTransactionByPaymentMethod($tahun) {
        $query = "SELECT
            a.payment_method AS payment_method,
            YEAR(a.created_at) AS tahun,
            SUM(a.amount) AS jumlah
            FROM
                product_payments a
            WHERE
                YEAR(a.created_at) = $tahun and a.status = 'success'
            GROUP BY
                a.payment_method,
                YEAR(a.created_at)";

        $result = DB::select($query);

        return response()->json($result, 200);
    }
}

<?php

namespace App\Exports;


use App\Models\DailyReport;
use Maatwebsite\Excel\Concerns\FromCollection;

class DailyReportExport implements FromCollection
{
    public function collection()
    {
        return DailyReport::all();
    }
}
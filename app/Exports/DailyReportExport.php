<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyReportExport implements FromCollection, WithHeadings
{

    protected $dailyreport;

    public function __construct($dailyreport_)
    {
        $this->dailyreport = $dailyreport_;
    }

    public function headings(): array
    {
        return [
            'email',
            'nama',
            'nip',
            'pangkat',
            'jabatan',
            'tanggal',
            'laporan'
        ];
    }

    public function collection()
    {
        return $this->dailyreport;
    }
}
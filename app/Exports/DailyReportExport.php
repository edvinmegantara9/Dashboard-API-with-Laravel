<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class DailyReportExport implements FromCollection
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
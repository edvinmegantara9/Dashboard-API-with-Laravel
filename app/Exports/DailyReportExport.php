<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DailyReportExport implements FromCollection, WithHeadings, WithColumnFormatting
{

    protected $dailyreport;

    public function __construct($dailyreport_)
    {
        $this->dailyreport = $dailyreport_;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT
        ];
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
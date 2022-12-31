<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductPaymentExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithCustomValueBinder
{

    protected $dailyreport;

    public function __construct($dailyreport_)
    {
        $this->dailyreport = $dailyreport_;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT
        ];
    }

    public function headings(): array
    {
        return [
            'KATEGORI', 'NO. TRANSAKSI', 'USER', 'PRODUCT', 'AMOUNT', 'PAYMENT METHOD', 'PAYMENT CHANNEL', 'STATUS', 'NOTE'
        ];
    }

    public function collection()
    {
        return $this->dailyreport;
    }
}
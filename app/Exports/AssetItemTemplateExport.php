<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetItemTemplateExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths
{
    public function collection()
    {
        return new Collection([]);
    }

    public function headings(): array
    {
        return [
            'name',
            'department',
            'initial_quantity',
            'initial_unit_cost',
            'initial_purchase_date (YYYY-MM-DD)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 45,
        ];
    }
}

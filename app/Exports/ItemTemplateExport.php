<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemTemplateExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths
{
    /**
     * No data rows (just template)
     */
    public function collection()
    {
        return new Collection([]);
    }

    /**
     * Column headers
     */
    public function headings(): array
    {
        return [
            'name',
            'barcode (optional)',
            'category',
            'supplier (optional)',
            'initial_stock',
            'reorder_level',
            'is_sale_item (true/false)',
            'is_stock_item (true/false)',
            'is_auto_tracked (true/false)',
            'is_active (true/false)',
            'smallest_unit',
            'buying_price',
            'selling_price',
            'location',
        ];
    }

    /**
     * Styles (bold header)
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ // Row 1 (header)
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25, // name
            'B' => 20, // barcode
            'C' => 20, // category
            'D' => 20, // supplier
            'E' => 15, // initial_stock
            'F' => 15, // reorder_level
            'G' => 25, // is_sale_item
            'H' => 25, // is_stock_item
            'I' => 25, // is_auto_tracked
            'J' => 20, // is_active
            'K' => 20, // smallest_unit
            'L' => 15, // buying_price
            'M' => 15, // selling_price
            'N' => 20, // location
        ];
    }
}

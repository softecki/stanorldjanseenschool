<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentQuickBooksTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['Date', 'Num', 'Name', 'Amount', 'Open Balance'];
    }

    public function array(): array
    {
        return [[
            '2025-01-09',
            '1094',
            'CLASS 1 B:SHURAIMU MRISHO',
            '300000',
            '70000',
        ]];
    }
}


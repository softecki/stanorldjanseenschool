<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentCrdbTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'SNo',
            'Class',
            'Stream/Combination',
            'Student Name',
            'Admission No.',
            'Reference No.',
            'Fee Amount',
            'Paid Amount',
            'Balance Amount',
            'Currency',
        ];
    }

    public function array(): array
    {
        return [[
            '1',
            'BABY 1',
            'A',
            'BATILDA BONIVENTURA MWACHA',
            'BB12026',
            'S000001365699',
            '800000',
            '350000',
            '450000',
            'TZS',
        ]];
    }
}


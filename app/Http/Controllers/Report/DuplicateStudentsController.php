<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\StudentInfo\StudentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;

class DuplicateStudentsController extends Controller
{
    private $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = 'Duplicate Students';
        $data['duplicates'] = [];
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['duplicates'], 'meta' => $this->duplicateMeta($data['title'], $data['duplicates'])]);
        }
        return view('backend.report.duplicate-students', compact('data'));
    }

    public function search(Request $request): JsonResponse|View
    {
        $data['title'] = 'Duplicate Students';
        $duplicates = $this->studentRepo->findDuplicateStudents();
        $data['duplicates'] = $duplicates;
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['duplicates'], 'meta' => $this->duplicateMeta($data['title'], $data['duplicates'])]);
        }
        return view('backend.report.duplicate-students', compact('data'));
    }

    public function generatePDF()
    {
        $data['title'] = 'Duplicate Students';
        $data['duplicates'] = $this->studentRepo->findDuplicateStudents();
        $pdf = PDF::loadView('backend.report.duplicate-studentsPDF', compact('data'));

        return $pdf->download('duplicate_students_' . date('d_m_Y') . '.pdf');
    }

    public function generateExcel()
    {
        $rows = collect($this->studentRepo->findDuplicateStudents())->map(function ($duplicate) {
            return [
                $duplicate['type'] === 'name' ? 'Same Name' : 'Same Phone',
                ($duplicate['class'] ?? '') . ' (' . ($duplicate['section'] ?? '') . ')',
                $duplicate['student_1']['id'] ?? '',
                $duplicate['student_1']['name'] ?? '',
                $duplicate['student_1']['mobile'] ?? '',
                $duplicate['student_2']['id'] ?? '',
                $duplicate['student_2']['name'] ?? '',
                $duplicate['student_2']['mobile'] ?? '',
            ];
        })->values()->all();

        $export = new class($rows) implements FromArray, WithHeadings, WithEvents {
            protected $rows;

            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return ['Duplicate Type', 'Class (Section)', 'Student 1 ID', 'Student 1 Name', 'Student 1 Phone', 'Student 2 ID', 'Student 2 Name', 'Student 2 Phone'];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $event->sheet->getDelegate()->getStyle('A1:H1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Duplicate_Students.xlsx');
    }

    private function duplicateMeta(string $title, $duplicates): array
    {
        return [
            'title' => $title,
            'total' => count($duplicates),
            'pdf_download_url' => route('report-duplicate-students.pdf-generate', [], false),
            'excel_download_url' => route('report-duplicate-students.excel-generate', [], false),
        ];
    }
}


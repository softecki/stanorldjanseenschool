<?php

namespace App\Http\Controllers\StudentInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Log;

class CombineExcelController extends Controller
{
    /**
     * Combine all Excel files from GloryLandSchool folder into one Excel file
     */
    public function combineAllExcelFiles()
    {
        try {
            $folderPath = base_path('GloryLandSchool');
            $allData = [];
            $headers = [];
            $processedFiles = [];
            $skippedFiles = [];

            // Get all Excel files from the folder
            $files = glob($folderPath . '/*.xlsx');
            $files = array_merge($files, glob($folderPath . '/*.xls'));

            foreach ($files as $file) {
                $fileName = basename($file);
                
                // Skip temporary files (starting with ~$)
                if (strpos($fileName, '~$') === 0) {
                    $skippedFiles[] = $fileName . ' (temporary file)';
                    continue;
                }

                try {
                    // Read Excel file using PhpSpreadsheet directly for better compatibility
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($file);
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                    $sheetData = $worksheet->toArray(null, true, true, true);
                    
                    if (empty($sheetData)) {
                        $skippedFiles[] = $fileName . ' (empty file)';
                        $spreadsheet->disconnectWorksheets();
                        continue;
                    }
                    
                    // Get headers from first row
                    if (empty($headers) && !empty($sheetData[0])) {
                        $headers = array_keys($sheetData[0]);
                    }

                    // Process each row
                    $firstRow = true;
                    foreach ($sheetData as $rowIndex => $row) {
                        // Skip empty rows
                        $rowValues = array_values($row);
                        if (empty(array_filter($rowValues))) {
                            continue;
                        }

                        // Check if first row is headers
                        if ($firstRow && empty($headers)) {
                            // Check if this looks like a header row
                            $isHeader = false;
                            foreach ($rowValues as $value) {
                                if (is_string($value) && (
                                    stripos($value, 'student') !== false ||
                                    stripos($value, 'name') !== false ||
                                    stripos($value, 'class') !== false ||
                                    stripos($value, 'fee') !== false ||
                                    stripos($value, 'balance') !== false ||
                                    stripos($value, 'paid') !== false
                                )) {
                                    $isHeader = true;
                                    break;
                                }
                            }
                            
                            if ($isHeader) {
                                // Use this row as headers
                                $headers = array_values($row);
                                // Clean headers - remove any null/empty keys
                                $headers = array_map(function($h) {
                                    return $h ?? '';
                                }, $headers);
                                $firstRow = false;
                                continue;
                            }
                        }

                        // Convert row to associative array if headers exist
                        if (!empty($headers)) {
                            $rowArray = [];
                            $rowValues = array_values($row);
                            foreach ($headers as $index => $header) {
                                $rowArray[$header] = $rowValues[$index] ?? '';
                            }
                            $rowArray['source_file'] = $fileName;
                            $allData[] = $rowArray;
                        } else {
                            // No headers yet, store as indexed array
                            $rowArray = array_values($row);
                            $rowArray[] = $fileName;
                            $allData[] = $rowArray;
                        }
                        
                        $firstRow = false;
                    }
                    
                    $spreadsheet->disconnectWorksheets();

                    $processedFiles[] = $fileName;
                    Log::info("Processed file: {$fileName} - " . (count($sheetData) - $startRow) . " rows");

                } catch (\Exception $e) {
                    $skippedFiles[] = $fileName . ' (error: ' . $e->getMessage() . ')';
                    Log::error("Error processing file {$fileName}: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($allData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No data found in any Excel files',
                    'processed_files' => $processedFiles,
                    'skipped_files' => $skippedFiles
                ], 400);
            }

            // If no headers were detected, create default headers
            if (empty($headers) && !empty($allData)) {
                $firstRow = $allData[0];
                $maxColumns = is_array($firstRow) ? count($firstRow) : 0;
                $headers = [];
                for ($i = 0; $i < $maxColumns - 1; $i++) {
                    $headers[] = 'Column_' . ($i + 1);
                }
                $headers[] = 'source_file';
            } else {
                // Add source_file to headers if not already present
                if (!in_array('source_file', $headers)) {
                    $headers[] = 'source_file';
                }
            }

            // Create export
            $export = new class($allData, $headers, $processedFiles, $skippedFiles) implements FromArray, WithHeadings, WithEvents {
                protected $data;
                protected $headers;
                protected $processedFiles;
                protected $skippedFiles;

                public function __construct(array $data, array $headers, array $processedFiles, array $skippedFiles)
                {
                    $this->data = $data;
                    $this->headers = $headers;
                    $this->processedFiles = $processedFiles;
                    $this->skippedFiles = $skippedFiles;
                }

                public function array(): array
                {
                    // Ensure all rows have the same structure
                    $result = [];
                    foreach ($this->data as $row) {
                        $formattedRow = [];
                        foreach ($this->headers as $header) {
                            $formattedRow[$header] = $row[$header] ?? '';
                        }
                        $result[] = $formattedRow;
                    }
                    return $result;
                }

                public function headings(): array
                {
                    return $this->headers;
                }

                public function registerEvents(): array
                {
                    return [
                        AfterSheet::class => function (AfterSheet $event) {
                            $sheet = $event->getSheet();
                            
                            // Style header row
                            $lastColumn = $sheet->getHighestColumn();
                            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => 'FFFFFF'],
                                    'size' => 12,
                                ],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => '4472C4'],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                ],
                            ]);

                            // Auto-size columns
                            foreach (range('A', $lastColumn) as $col) {
                                $sheet->getColumnDimension($col)->setAutoSize(true);
                            }

                            // Freeze first row
                            $sheet->freezePane('A2');
                        },
                    ];
                }
            };

            $fileName = 'Combined_All_Students_' . date('Y-m-d_His') . '.xlsx';
            
            Log::info("Combined Excel Export", [
                'total_rows' => count($allData),
                'processed_files' => count($processedFiles),
                'skipped_files' => count($skippedFiles),
                'files' => $processedFiles
            ]);

            return Excel::download($export, $fileName);

        } catch (\Exception $e) {
            Log::error("Error combining Excel files: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error combining Excel files: ' . $e->getMessage()
            ], 500);
        }
    }
}


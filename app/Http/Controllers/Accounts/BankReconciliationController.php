<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\StudentInfo\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BankReconciliationController extends Controller
{
    /**
     * Show the bank reconciliation upload page
     */
    public function index(Request $request): JsonResponse|View
    {
        $data = [
            'title' => __('Bank Reconciliation'),
        ];
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return view('backend.accounts.reports.bank-reconciliation', compact('data'));
    }

    /**
     * Parse the uploaded Excel file
     */
    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $filePath = $file->store('temp', 'local');
            $fullPath = storage_path('app/' . $filePath);

            // Extract data from Excel
            $excelData = $this->extractExcelData($fullPath);

            if (empty($excelData['transactions']) || !is_array($excelData['transactions'])) {
                throw new \Exception(__('No transactions found in Excel file. Please check the file format and ensure it contains data with columns: Posting Date, Details, Value Date, Debit, Credit, Book Balance.'));
            }

            // Enrich transactions with reference numbers and student data
            $transactions = $this->enrichTransactions($excelData['transactions']);

            // Store in session for processing, and store original excel rows for export
            session([
                'bank_transactions' => $transactions,
                'bank_transactions_excel' => [
                    'headers' => $excelData['headers'] ?? [],
                    'rows' => $excelData['rows'] ?? [],
                ],
            ]);

            // Delete temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Bank statement uploaded successfully. Found :count transactions.', ['count' => count($transactions)]),
                    'data' => $transactions,
                ]);
            }
            return redirect()->route('accounting.bank-reconciliation.process')
                ->with('success', __('Bank statement uploaded successfully. Found :count transactions.', ['count' => count($transactions)]));
        } catch (\Exception $e) {
            Log::error('Bank Reconciliation Error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            if ($request->expectsJson()) {
                return response()->json(['message' => __('Error processing Excel file: ') . $e->getMessage()], 422);
            }
            return redirect()->back()
                ->with('error', __('Error processing Excel file: ') . $e->getMessage());
        }
    }

    /**
     * Extract data from Excel file
     */
    private function extractExcelData($filePath)
    {
        $transactions = [];

        try {
            // Read the Excel file
            $spreadsheet = Excel::toArray([], $filePath);
            
            if (empty($spreadsheet) || !is_array($spreadsheet)) {
                throw new \Exception(__('Could not read Excel file'));
            }

            // Get the first sheet
            $sheet = $spreadsheet[0];

            if (empty($sheet)) {
                throw new \Exception(__('Excel sheet is empty'));
            }

            // Find header row (first row with data)
            $headerRow = null;
            $headerKeyWords = ['posting date', 'details', 'value date', 'debit', 'credit', 'book balance'];
            $rawHeaders = [];
            $rows = [];

            foreach ($sheet as $index => $row) {
                $rowLower = array_map('strtolower', $row);

                // Check if this row contains our expected headers
                $matchCount = 0;
                foreach ($headerKeyWords as $header) {
                    foreach ($rowLower as $cell) {
                        if (stripos($cell, $header) !== false) {
                            $matchCount++;
                            break;
                        }
                    }
                }

                if ($matchCount >= 4 && $headerRow === null) {
                    $headerRow = $index;
                    $rawHeaders = array_map('\trim', $row);
                    continue;
                }

                // If we already determined header row, start collecting rows
                if ($headerRow !== null && $index > $headerRow) {
                    // Skip empty rows
                    if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                        continue;
                    }

                    $rows[] = $row;
                    $transaction = $this->buildTransactionFromExcelRow($row);
                    if (is_array($transaction) && !empty($transaction)) {
                        $transactions[] = $transaction;
                    }
                }
            }

            // If no header row found, assume first row is headers and process subsequently
            if ($headerRow === null && !empty($sheet)) {
                $headerRow = 0;
                $rawHeaders = array_map('\trim', $sheet[0]);

                for ($i = 1; $i < count($sheet); $i++) {
                    $row = $sheet[$i];
                    if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                        continue;
                    }

                    $rows[] = $row;
                    $transaction = $this->buildTransactionFromExcelRow($row);
                    if (is_array($transaction) && !empty($transaction)) {
                        $transactions[] = $transaction;
                    }
                }
            }

            return [
                'headers' => $rawHeaders,
                'rows' => $rows,
                'transactions' => $transactions,
            ];
        } catch (\Exception $e) {
            Log::error('Excel extraction error: ' . $e->getMessage());
            throw new \Exception(__('Failed to extract Excel data: ') . $e->getMessage());
        }
    }

    /**
     * Build a transaction from Excel row data
     */
    private function buildTransactionFromExcelRow($row)
    {
        if (!is_array($row) || count($row) < 3) {
            return null;
        }

        // Get data from row (assuming columns: Posting Date, Details, Value Date, Debit, Credit, Book Balance)
        $postingDate = trim($row[0] ?? '');
        $details = trim($row[1] ?? '');
        $valueDate = trim($row[2] ?? '');
        $debit = 0;
        $credit = 0;
        $bookBalance = 0;

        // Handle numeric values
        if (isset($row[3])) {
            $debit = (float) str_replace([',', ' '], '.', $row[3]);
        }
        
        if (isset($row[4])) {
            $credit = (float) str_replace([',', ' '], '.', $row[4]);
        }
        
        if (isset($row[5])) {
            $bookBalance = (float) str_replace([',', ' '], '.', $row[5]);
        }

        // Skip if no posting date
        if (empty($postingDate)) {
            return null;
        }

        // Initialize transaction array
        $transaction = [
            'posting_date' => $postingDate,
            'details' => $details,
            'value_date' => $valueDate,
            'debit' => $debit,
            'credit' => $credit,
            'book_balance' => $bookBalance,
            'reference_number' => '',
            'control_number' => '',
            'student_name' => '',
        ];

        // Extract reference number from details
        if (preg_match('/REF\s*:\s*([A-Za-z0-9]+)/i', $details, $matches)) {
            $transaction['reference_number'] = $matches[1];
        } elseif (preg_match('/\b([A-Za-z0-9]{16,})\b/', $details, $matches)) {
            // Fallback: long alphanumeric string may be reference
            $transaction['reference_number'] = $matches[1];
        }

        // Normalize reference (remove non-alphanumeric)
        $transaction['reference_number'] = trim(preg_replace('/[^A-Za-z0-9]/', '', $transaction['reference_number']));

        return $transaction;
    }

    /**
     * Extract text from PDF file using available methods
     */
    private function extractPdfText($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception(__('File not found'));
        }

        $text = '';

        // Method 1: Try using smalot/pdfparser if available
        try {
            if (class_exists('Smalot\PdfParser\Parser')) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                if ($pdf && is_object($pdf)) {
                    $text = $pdf->getText();
                    if (!empty($text) && is_string($text)) {
                        return $text;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('Smalot PDF Parser error: ' . $e->getMessage());
        }

        // Method 2: Use pdftotext command if available
        try {
            $text = $this->extractUsingCommand($filePath);
            if (!empty($text) && is_string($text)) {
                return $text;
            }
        } catch (\Exception $e) {
            Log::debug('Pdftotext command error: ' . $e->getMessage());
        }

        // Method 3: Use PHP binary parsing
        try {
            $text = $this->extractUsingPhpOptions($filePath);
            if (!empty($text) && is_string($text)) {
                return $text;
            }
        } catch (\Exception $e) {
            Log::debug('PHP options extraction error: ' . $e->getMessage());
        }

        // Method 4: Fallback - binary parsing
        try {
            $text = $this->extractUsingBinaryParsing($filePath);
            if (!empty($text) && is_string($text)) {
                return $text;
            }
        } catch (\Exception $e) {
            Log::debug('Binary parsing error: ' . $e->getMessage());
        }

        throw new \Exception(__('PDF text extraction failed. None of the available extraction methods worked.'));
    }

    /**
     * Extract PDF text using system command (pdftotext)
     */
    private function extractUsingCommand($filePath)
    {
        $outputFile = storage_path('app/temp/pdf_text_' . time() . '_' . rand(1000, 9999) . '.txt');
        
        // Ensure temp directory exists
        @mkdir(dirname($outputFile), 0755, true);

        $escapedInput = escapeshellarg($filePath);
        $escapedOutput = escapeshellarg($outputFile);
        $command = "pdftotext {$escapedInput} {$escapedOutput} 2>&1";
        
        exec($command, $output, $returnCode);

        if (file_exists($outputFile) && file_get_contents($outputFile)) {
            $text = file_get_contents($outputFile);
            @unlink($outputFile);
            return $text;
        }

        if (file_exists($outputFile)) {
            @unlink($outputFile);
        }

        return '';
    }

    /**
     * Extract text from PDF using PHP binary reading
     */
    private function extractUsingPhpOptions($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return '';
        }

        $content = '';
        while (!feof($handle)) {
            $content .= fread($handle, 1024);
        }
        fclose($handle);

        // Simple regex to extract text streams from PDF
        preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches);
        
        $text = '';
        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                // Try to decode the stream
                $decoded = $this->decodePdfStream($match);
                $text .= $decoded . "\n";
            }
        }

        // Also try extracting from text objects
        preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $textMatches);
        if (!empty($textMatches[1])) {
            foreach ($textMatches[1] as $match) {
                preg_match_all('/\((.*?)\)/', $match, $stringMatches);
                foreach ($stringMatches[1] as $string) {
                    $text .= $string . ' ';
                }
            }
        }

        return $text;
    }

    /**
     * Decode PDF stream content
     */
    private function decodePdfStream($stream)
    {
        // Remove non-printable characters except common delimiters
        $cleaned = preg_replace('/[^\x20-\x7E\n\r\t-]/', '', $stream);
        return $cleaned;
    }

    /**
     * Extract text from PDF using binary parsing
     */
    private function extractUsingBinaryParsing($filePath)
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return '';
        }

        // Remove binary characters but keep text
        $text = '';
        $length = strlen($content);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $content[$i];
            $ascii = ord($char);
            
            // Keep printable ASCII and common characters
            if (($ascii >= 32 && $ascii <= 126) || $ascii == 10 || $ascii == 13 || $ascii == 9) {
                $text .= $char;
            } elseif ($ascii >= 192) {
                // Keep UTF-8 characters
                $text .= $char;
            }
        }

        return $text;
    }

    /**
     * Process and display transactions with student matching
     */
    public function process(Request $request): JsonResponse|RedirectResponse
    {
        $transactions = session('bank_transactions', []);

        if (!is_array($transactions) || empty($transactions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('No transactions to process. Please upload an Excel file.')], 422);
            }
            return redirect()->route('accounting.bank-reconciliation.index')
                ->with('error', __('No transactions to process. Please upload an Excel file.'));
        }

        $data = [
            'title' => __('Bank Reconciliation - Process'),
            'transactions' => $transactions,
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $transactions, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/reports/accounting/bank-reconciliation/process'));
    }

    /**
     * Generate PDF report with student names
     */
    public function generatePdf()
    {
        $transactions = session('bank_transactions', []);

        if (!is_array($transactions) || empty($transactions)) {
            return redirect()->route('accounting.bank-reconciliation.index')
                ->with('error', __('No transactions to process'));
        }

        $data = [
            'title' => __('Bank Reconciliation Report'),
            'transactions' => $transactions,
            'generated_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $pdf = Pdf::loadView('backend.accounts.reports.bank-reconciliation-pdf', compact('data'));
        
        return $pdf->download('bank-reconciliation-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate Excel report with all transaction columns + student names
     */
    public function generateExcel()
    {
        $transactions = session('bank_transactions', []);

        if (!is_array($transactions) || empty($transactions)) {
            return redirect()->route('accounting.bank-reconciliation.index')
                ->with('error', __('No transactions to process'));
        }

        try {
            // Create a new Spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Bank Reconciliation');

            $excelBackup = session('bank_transactions_excel', []);
            $originalHeaders = $excelBackup['headers'] ?? [];
            $originalRows = $excelBackup['rows'] ?? [];

            if (!empty($originalHeaders)) {
                $headers = $originalHeaders;
            } else {
                $headers = [
                    __('Posting Date'),
                    __('Details'),
                    __('Reference Number'),
                    __('Value Date'),
                    __('Debit'),
                    __('Credit'),
                    __('Book Balance'),
                    __('Student Name'),
                ];
            }

            if (!in_array(__('Reference Number'), $headers, true)) {
                $headers[] = __('Reference Number');
            }
            if (!in_array(__('Control Number'), $headers, true)) {
                $headers[] = __('Control Number');
            }
            if (!in_array(__('Student Name'), $headers, true)) {
                $headers[] = __('Student Name');
            }

            // Add header row
            foreach ($headers as $col => $header) {
                $columnLetter = chr(65 + $col);
                $sheet->setCellValue($columnLetter . '1', $header);
                $sheet->getStyle($columnLetter . '1')->getFont()->setBold(true);
                $sheet->getStyle($columnLetter . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD3D3D3');
            }

            // Add transaction data
            $row = 2;
            $totalTransactions = count($transactions);
            $matchedCount = 0;

            if (!empty($originalRows)) {
                foreach ($originalRows as $index => $originalRow) {
                    $colIndex = 0;
                    foreach ($originalRow as $cellValue) {
                        $sheet->setCellValue(chr(65 + $colIndex) . $row, $cellValue);
                        $colIndex++;
                    }

                    $ref = $transactions[$index]['reference_number'] ?? '';
                    $control = $transactions[$index]['control_number'] ?? '';
                    $studentName = $transactions[$index]['student_name'] ?? '';

                    $sheet->setCellValue(chr(65 + $colIndex) . $row, $ref);
                    $colIndex++;
                    $sheet->setCellValue(chr(65 + $colIndex) . $row, $control);
                    $colIndex++;
                    $sheet->setCellValue(chr(65 + $colIndex) . $row, $studentName);

                    if (!empty($studentName)) {
                        $matchedCount++;
                    }

                    $row++;
                }
            } else {
                foreach ($transactions as $transaction) {
                    $sheet->setCellValue('A' . $row, $transaction['posting_date'] ?? '');
                    $sheet->setCellValue('B' . $row, $transaction['details'] ?? '');
                    $sheet->setCellValue('C' . $row, $transaction['reference_number'] ?? '');
                    $sheet->setCellValue('D' . $row, $transaction['control_number'] ?? '');
                    $sheet->setCellValue('E' . $row, $transaction['value_date'] ?? '');
                    $sheet->setCellValue('F' . $row, is_numeric($transaction['debit'] ?? 0) ? (float)$transaction['debit'] : 0);
                    $sheet->setCellValue('G' . $row, is_numeric($transaction['credit'] ?? 0) ? (float)$transaction['credit'] : 0);
                    $sheet->setCellValue('H' . $row, is_numeric($transaction['book_balance'] ?? 0) ? (float)$transaction['book_balance'] : 0);
                    $sheet->setCellValue('I' . $row, $transaction['student_name'] ?? '');

                    if (!empty($transaction['student_name'])) {
                        $matchedCount++;
                    }

                    $row++;
                }
            }


            // Add summary section
            $summaryRow = $row + 2;
            
            $sheet->setCellValue('A' . $summaryRow, __('Summary Statistics'));
            $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
            
            $summaryRow++;
            $sheet->setCellValue('A' . $summaryRow, __('Total Transactions'));
            $sheet->setCellValue('B' . $summaryRow, $totalTransactions);
            
            $summaryRow++;
            $sheet->setCellValue('A' . $summaryRow, __('Matched'));
            $sheet->setCellValue('B' . $summaryRow, $matchedCount);
            
            $summaryRow++;
            $unmatchedCount = $totalTransactions - $matchedCount;
            $sheet->setCellValue('A' . $summaryRow, __('Unmatched'));
            $sheet->setCellValue('B' . $summaryRow, $unmatchedCount);
            
            $summaryRow++;
            $matchRate = $totalTransactions > 0 ? round(($matchedCount / $totalTransactions) * 100, 2) : 0;
            $sheet->setCellValue('A' . $summaryRow, __('Match Rate (%)'));
            $sheet->setCellValue('B' . $summaryRow, $matchRate . '%');

            // Auto-size columns
            $countColumns = count($headers);
            $lastColumn = chr(65 + max(0, $countColumns - 1));
            foreach (range('A', $lastColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set minimum widths for better readability when default columns exist
            if (isset($headers[0])) {
                $sheet->getColumnDimension('A')->setWidth(15); // Posting Date or first header
            }
            if (isset($headers[1])) {
                $sheet->getColumnDimension('B')->setWidth(40); // Details or second header
            }
            if (isset($headers[2])) {
                $sheet->getColumnDimension('C')->setWidth(22); // Reference Number or third header
            }
            if (isset($headers[3])) {
                $sheet->getColumnDimension('D')->setWidth(15); // Value Date or fourth header
            }
            if (isset($headers[4])) {
                $sheet->getColumnDimension('E')->setWidth(12); // Debit or fifth header
            }
            if (isset($headers[5])) {
                $sheet->getColumnDimension('F')->setWidth(12); // Credit or sixth header
            }
            if (isset($headers[6])) {
                $sheet->getColumnDimension('G')->setWidth(15); // Book Balance or seventh header
            }
            if (isset($headers[7])) {
                $sheet->getColumnDimension('H')->setWidth(20); // Control Number or eighth header
            }
            if (isset($headers[8])) {
                $sheet->getColumnDimension('I')->setWidth(25); // Student Name or ninth header
            }

            // Create writer and output
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Create temporary file
            $temp_file = storage_path('app/temp/bank-reconciliation-' . time() . '.xlsx');
            @mkdir(dirname($temp_file), 0755, true);
            $writer->save($temp_file);

            // Download and delete
            $response = response()->download(
                $temp_file,
                'bank-reconciliation-' . Carbon::now()->format('Y-m-d') . '.xlsx'
            );

            // Clean up after download
            return $response->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Excel Export Error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return redirect()->route('accounting.bank-reconciliation.process')
                ->with('error', __('Error generating Excel report: ') . $e->getMessage());
        }
    }

    /**
     * Parse transactions from PDF text
     */
    private function parseTransactions($text)
    {
        if (!is_string($text) || empty(trim($text))) {
            return [];
        }

        $transactions = [];
        $lines = array_filter(explode("\n", $text), function ($line) {
            return !empty(trim($line));
        });

        $currentTransaction = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }

            // Check if this is a posting date line (starts with date format: 12.02.2026 or 12/02/2026)
            if (preg_match('/^\d{1,2}[.\/-]\d{1,2}[.\/-]\d{4}/', $line)) {
                // Save previous transaction if exists
                if (!empty($currentTransaction)) {
                    $transaction = $this->buildTransaction($currentTransaction);
                    if (is_array($transaction) && !empty($transaction)) {
                        $transactions[] = $transaction;
                    }
                }
                // Start new transaction
                $currentTransaction = [$line];
            } else {
                // Continue building transaction
                if (!empty($currentTransaction)) {
                    $currentTransaction[] = $line;
                }
            }
        }

        // Don't forget the last transaction
        if (!empty($currentTransaction)) {
            $transaction = $this->buildTransaction($currentTransaction);
            if (is_array($transaction) && !empty($transaction)) {
                $transactions[] = $transaction;
            }
        }

        return is_array($transactions) ? $transactions : [];
    }

    /**
     * Build a single transaction from lines
     */
    private function buildTransaction($lines)
    {
        if (!is_array($lines) || count($lines) < 1) {
            return null;
        }

        $postingDate = trim($lines[0]);
        $details = '';
        
        // Combine all lines after the first as details
        if (count($lines) > 1) {
            $detailLines = array_slice($lines, 1);
            $details = trim(implode(' ', $detailLines));
        }

        // Initialize transaction array
        $transaction = [
            'posting_date' => $postingDate,
            'details' => $details,
            'value_date' => '',
            'debit' => 0,
            'credit' => 0,
            'book_balance' => 0,
            'reference_number' => '',
            'student_name' => '',
        ];

        // Extract reference number from details (REF:xxxxx)
        if (preg_match('/REF:([A-Za-z0-9]+)/', $details, $matches)) {
            $transaction['reference_number'] = $matches[1];
        }

        // Extract numeric values
        if (!empty($details)) {
            $remaining = implode(' ', array_slice($lines, 1));
            $parts = preg_split('/\s+/', $remaining);
            
            $numericValues = [];
            foreach ($parts as $part) {
                // Look for another date for value_date
                if (preg_match('/^\d{1,2}[.\/-]\d{1,2}[.\/-]\d{4}/', $part) && $part !== $postingDate && empty($transaction['value_date'])) {
                    $transaction['value_date'] = $part;
                }
                
                // Parse numeric values (amounts can have , or . as decimal)
                if (preg_match('/^[\d,\.]+$/', $part)) {
                    $num = (float) str_replace(',', '.', $part);
                    if ($num > 0) {
                        $numericValues[] = $num;
                    }
                }
            }
            
            // Assign values based on pattern
            if (!empty($numericValues)) {
                if (count($numericValues) >= 3) {
                    $transaction['debit'] = $numericValues[0];
                    $transaction['credit'] = $numericValues[1];
                    $transaction['book_balance'] = end($numericValues);
                } elseif (count($numericValues) == 2) {
                    $transaction['debit'] = $numericValues[0];
                    $transaction['book_balance'] = $numericValues[1];
                } elseif (count($numericValues) == 1) {
                    $transaction['book_balance'] = $numericValues[0];
                }
            }
        }

        return $transaction;
    }

    /**
     * Enrich transactions with student data by matching reference numbers
     */
    private function enrichTransactions(&$transactions)
    {
        if (!is_array($transactions)) {
            return $transactions;
        }

        foreach ($transactions as &$transaction) {
            // Try to find student by reference number in push_transactions
            $referenceNumber = $transaction['reference_number'] ?? '';
            $details = $transaction['details'] ?? '';

            if (!empty($referenceNumber) || !empty($details)) {
                $result = $this->findStudentByReference($referenceNumber, $details);
                $student = $result['student'] ?? null;
                $controlNumber = $result['control_number'] ?? '';

                $transaction['control_number'] = $controlNumber;

                if ($student) {
                    $transaction['student_name'] = $student->student_name ?? $student->full_name ?? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                    continue;
                }
            }

            // Fallback: try to match by details text
            $transaction['student_name'] = $this->matchStudentName($details);
        }

        return $transactions;
    }

    /**
     * Find student by reference number in push_transactions table
     */
    private function findStudentByReference($referenceNumber, $details = '')
    {
        try {
            if (empty($referenceNumber) && !empty($details)) {
                if (preg_match('/REF\s*:\s*([A-Za-z0-9]+)/i', $details, $matches)) {
                    $referenceNumber = $matches[1];
                } elseif (preg_match('/\b([A-Za-z0-9]{16,})\b/', $details, $matches)) {
                    $referenceNumber = $matches[1];
                }
            }

            $referenceNumber = trim(strtolower(preg_replace('/[^A-Za-z0-9]/', '', $referenceNumber)));

            if (empty($referenceNumber)) {
                return null;
            }

            // build queries to be robust against stored formats
            $searchValues = [
                $referenceNumber,
                'ref:' . $referenceNumber,
            ];

            $pushTransaction = DB::table('push_transactions')
                ->where(function ($query) use ($searchValues) {
                    foreach ($searchValues as $value) {
                        $query->orWhereRaw('LOWER(settlement_receipt) = ?', [$value])
                              ->orWhereRaw('LOWER(reference) = ?', [$value])
                              ->orWhereRaw('LOWER(settlement_receipt) LIKE ?', ['%' . $value . '%'])
                              ->orWhereRaw('LOWER(reference) LIKE ?', ['%' . $value . '%']);
                    }
                })
                ->first();

            if ($pushTransaction) {
                $controlNumber = $pushTransaction->control_number ?? '';

                // First try control number from push_transactions to students table
                if (!empty($controlNumber)) {
                    $student = Student::where('control_number', $controlNumber)
                        ->where('status', 1)
                        ->first();

                    if ($student) {
                        return ['student' => $student, 'control_number' => $controlNumber];
                    }
                }

                // Fallback: try to get student by fees_assign_children_id
                if (!empty($pushTransaction->fees_assign_children_id)) {
                    $feesAssignChildren = DB::table('fees_assign_children')
                        ->where('id', $pushTransaction->fees_assign_children_id)
                        ->first();

                    if ($feesAssignChildren && !empty($feesAssignChildren->student_id)) {
                        $student = Student::where('id', $feesAssignChildren->student_id)
                            ->where('status', 1)
                            ->first();

                        if ($student) {
                            return ['student' => $student, 'control_number' => $controlNumber];
                        }
                    }
                }

                // Fallback: try to get from phone number if available
                if (!empty($pushTransaction->phone)) {
                    $parent = DB::table('parent_guardians')
                        ->where('phone', $pushTransaction->phone)
                        ->first();

                    if ($parent && !empty($parent->student_id)) {
                        $student = Student::where('id', $parent->student_id)
                            ->where('status', 1)
                            ->first();

                        if ($student) {
                            return ['student' => $student, 'control_number' => $controlNumber];
                        }
                    }
                }

                // return control number even if no student found (for report only)
                return ['student' => null, 'control_number' => $controlNumber];
            }
        } catch (\Exception $e) {
            Log::debug('Error finding student by reference: ' . $e->getMessage());
        }

        return ['student' => null, 'control_number' => ''];
    }

    /**
     * Try to match student name from transaction details
     */
    private function matchStudentName($details)
    {
        if (empty($details) || !is_string($details)) {
            return '';
        }

        try {
            // Extract student name from details (usually after "TO" or "UBX TO")
            if (preg_match('/(?:TO|FILLBERT)\s+([A-Z\s]+?)(?:\s+EUSABIUS|\s+$|\n)/', $details, $matches)) {
                $potentialName = trim($matches[1]);
                
                // Try to find exact match
                $student = Student::where('is_deleted', 0)
                    ->whereRaw('LOWER(student_name) = ?', [strtolower($potentialName)])
                    ->first();

                if ($student) {
                    return $student->student_name;
                }
            }

            // Try partial match with student names
            $students = Student::where('is_deleted', 0)
                ->select('id', 'student_name', 'student_id')
                ->limit(100)
                ->get();

            $detailsLower = strtolower($details);

            foreach ($students as $student) {
                if (!empty($student->student_name)) {
                    $nameLower = strtolower($student->student_name);
                    if (strpos($detailsLower, $nameLower) !== false) {
                        return $student->student_name;
                    }
                }

                // Try partial match with student ID
                if (!empty($student->student_id) && strpos($details, $student->student_id) !== false) {
                    return $student->student_name;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Error matching student name: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * Clear session data
     */
    public function reset(Request $request): JsonResponse|RedirectResponse
    {
        session()->forget('bank_transactions');
        session()->forget('bank_transactions_excel');

        if ($request->expectsJson()) {
            return response()->json(['message' => __('Session cleared. Ready for new upload.')]);
        }

        return redirect()->route('accounting.bank-reconciliation.index')
            ->with('success', __('Session cleared. Ready for new upload.'));
    }
}



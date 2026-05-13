<?php

namespace App\Http\Controllers\Report;

use App\Enums\AccountHeadType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Accounts\AccountHeadRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Report\AccountRepository;
use App\Repositories\Report\FeesCollectionRepository;
use App\Repositories\Report\MeritListRepository;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;

class AccountController extends Controller
{
    private $repo;
    private $accountHeadRepo;

    function __construct(
        AccountRepository      $repo,
        AccountHeadRepository  $accountHeadRepo,
    )
    {
        $this->repo              = $repo;
        $this->accountHeadRepo   = $accountHeadRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['account_head'] = $this->accountHeadRepo->getIncomeHeads();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return view('backend.report.account', compact('data'));
    }

    public function search(Request $request): JsonResponse|View
    {
        $data                 = $this->repo->search($request);
        $data['request']      = $request;

        if($data['request']->type == AccountHeadType::INCOME)
            $data['account_head'] = $this->accountHeadRepo->getIncomeHeads();
        else
            $data['account_head'] = $this->accountHeadRepo->getExpenseHeads();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['result'] ?? [],
                'meta' => [
                    'account_head' => $data['account_head'],
                    'type' => $request->type,
                    'head' => $request->head,
                    'dates' => $request->dates ?? null,
                    'sum' => $data['sum'] ?? 0,
                    'cash' => $data['cash'] ?? 0,
                    'bank' => $data['bank'] ?? 0,
                    'pdf_download_url' => route('report-account.pdf-generate', [], false) . '?' . http_build_query([
                        'type' => $request->type,
                        'head' => $request->head,
                        'date' => $request->dates,
                    ]),
                    'excel_download_url' => route('report-account.excel-generate', [], false) . '?' . http_build_query([
                        'type' => $request->type,
                        'head' => $request->head,
                        'date' => $request->dates,
                    ]),
                ],
            ]);
        }
        return view('backend.report.account', compact('data'));
    }

    public function getAccountTypes(Request $request){
        if($request->id == AccountHeadType::INCOME)
            return $this->accountHeadRepo->getIncomeHeads();
        else
            return $this->accountHeadRepo->getExpenseHeads();
    }

    public function generatePDF(Request $request)
    {
        try {
            $reportRequest = $this->exportRequest($request);
            $data = $this->repo->searchPDF($reportRequest);
            $pdf = PDF::loadView('backend.report.accountPDF', compact('data'));

            return $pdf->download('account_' . date('d_m_Y') . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    public function generateExcel(Request $request)
    {
        $data = $this->repo->searchPDF($this->exportRequest($request));
        $rows = collect($data['result'] ?? [])->map(function ($item) {
            return [
                $item->date,
                $item->name,
                optional($item->head)->name,
                $item->description,
                (float) $item->amount,
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
                return ['Date', 'Name', 'Head', 'Description', 'Amount'];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $event->sheet->getDelegate()->getStyle('A1:E1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Account_Report.xlsx');
    }

    private function exportRequest(Request $request): Request
    {
        return new Request([
            'type'  => $request->type,
            'head'  => $request->head,
            'dates' => $request->date ?? $request->dates,
        ]);
    }
}

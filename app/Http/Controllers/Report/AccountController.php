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
                // Prepare the request data
                $request = new Request([
                    'type'  => $request->type,
                    'head'  => $request->head,
                    'dates' => $request->date,
                ]);

                Log::info($request);

                // Fetch data using the repository
                $data = $this->repo->searchPDF($request);
                Log::info($data);
                // Load the PDF view
                $pdf = PDF::loadView('backend.report.accountPDF', compact('data'));

                // Return the downloaded PDF
                return $pdf->download('account_' . date('d_m_Y') . '.pdf');
            } catch (\Exception $e) {
                // Log the error
                Log::error('PDF generation failed: ' . $e->getMessage());

                // Redirect back with an error message
                return back()->with('error', 'Failed to generate PDF. Please try again later.');
            }
        }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Models\Role;
use App\Models\User;
use App\Models\Search;
use App\Models\Language;
use App\Models\Permission;
use App\Models\Staff\Designation;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use App\Models\Accounts\Expense;
use App\Models\Accounts\Income;
use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Services\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use PDF;

class DashboardController extends Controller
{
    private $repo;


    function __construct(DashboardRepository $repo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->repo       = $repo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        return view('spa.app');
    }

    public function getTermSummary(Request $request)
        {
             $term = $request->input('term');
            $collection_summary = $this->repo->getSummaryByTerm($term); // your term-wise logic here
            Log::info( 'collection_summary');
            Log::info( $collection_summary);
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => ['collection_summary' => $collection_summary],
                    'meta' => ['term' => $term],
                ]);
            }
            return redirect()->to(spa_url('backend/dashboardtable'));
        }

    public function feesCollectionMonthly() {
        return $this->repo->feesCollectionYearly();
    }

    public function revenueYearly() {
        return $this->repo->revenueYearly();
    }

    public function feesCollectionCurrentMonth()
    {
        return $this->repo->feesCollection();
    }

    public function incomeExpenseCurrentMonth()
    {
        return $this->repo->incomeExpense();
    }

    public function todayAttendance()
    {
        return $this->repo->attendance();
    }

    public function eventsCurrentMonth()
    {
        return $this->repo->eventsCurrentMonth();
    }

    /**
     * Income vs expense totals for a period (day|weekly|monthly|yearly|custom).
     * GET: period, and for custom: start_date, end_date (Y-m-d).
     */
    public function incomeExpenseByPeriod(Request $request)
    {
        $period = $request->input('period', 'yearly');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->repo->getIncomeExpenseByPeriod($period, $startDate, $endDate);
        return response()->json($data);
    }

    /**
     * Generate and download dashboard as PDF (backend).
     */
    public function exportPdf()
    {
        $data = $this->repo->index();
        $pdf = PDF::loadView('backend.dashboard-pdf', compact('data'));
        $pdf->setPaper('a4', 'portrait');
        $filename = 'dashboard-' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function searchMenuData(Request $request){
        try {
            $search = Search::query();
            if ($request->has('search')) {
                $search->where('title', 'like', '%' . $request->search . '%');
            }
            $search = $search->where('user_type', 'Admin')->take(10)->get()->map(function ($item) {
                return [
                    'title' => $item->title,
                    'route_name' => route($item->route_name),
                ];
            });
            return response()->json($search);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

  public function chat_index(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => 'AI Assistant']]);
        }
        return redirect()->to(spa_url('backend/master'));
    }

    public function send(Request $request, OpenAIService $openAI)
    {
        $messages = [
            ['role' => 'user', 'content' => $request->prompt],
        ];

        $response = $openAI->askChatGPT($messages);
        Log::info('ChatGPT response:', $response);
        $reply = $response['choices'][0]['message']['content'] ?? 'No response';

        return redirect()->route('chat.index')->with([
            'reply' => $reply,
            'prompt' => $request->prompt,
        ]);
    }
    
}

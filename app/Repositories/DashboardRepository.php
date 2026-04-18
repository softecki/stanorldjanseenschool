<?php

namespace App\Repositories;

use App\Enums\AttendanceType;
use App\Interfaces\DashboardInterface;
use App\Models\Academic\Classes;
use App\Models\Academic\ClassSetup;
use App\Models\Accounts\Expense;
use App\Models\Accounts\Income;
use App\Models\Attendance\Attendance;
use App\Models\Event;
use App\Models\Fees\FeesAssignChildren;
use App\Models\Fees\FeesCollect;
use App\Models\Fees\FeesGroup;
use App\Models\Role;
use App\Models\Session;
use App\Models\Staff\Staff;
use App\Models\StudentInfo\ParentGuardian;
use App\Models\StudentInfo\SessionClassStudent;
use App\Models\StudentInfo\Student;
use Illuminate\Support\Facades\DB;
 use Illuminate\Support\Facades\Schema;

class DashboardRepository implements DashboardInterface
{

    public function index()
    {
        $data['student'] = SessionClassStudent::join('students','students.id','=','session_class_students.student_id')->where('session_id', setting('session'))->where('status','1')->count();
        $data['parent']  = ParentGuardian::where('status',1)->count();
        $data['teacher'] = Staff::where('role_id',5)->count();
        $data['session'] = Session::count();

        $data['events']  = Event::where('session_id', setting('session'))->active()->where('date', '>=', date('Y-m-d'))->orderBy('date')->take(5)->get();
        // Total collection = sum of amounts collected in the current session only
        $data['fees_collect'] = FeesCollect::where('session_id', setting('session'))->sum('amount');

$data['unpaid_amount'] = FeesAssignChildren::join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
    ->where('students.status', 1)
    ->sum('fees_assign_childrens.remained_amount');
        $data['income']  = Income::join('fees_collects', 'incomes.fees_collect_id', '=', 'fees_collects.id')
        ->where('incomes.session_id', setting('session'))
        ->sum('incomes.amount');
        $data['expense'] = Expense::where('session_id', setting('session'))->sum('amount');
       $data['fees_groups'] = FeesGroup::where('dashboard_order','!=', 0)
            ->orderBy('dashboard_order')
            ->get();

        $data['collection_summary'] = [];

        foreach ($data['fees_groups'] as $group) {
            $data['collection_summary'][$group->name] = [
                'total' => $this->getTotal($group->id),
                'paid' => $this->getTotalPaid($group->id)
            ];
        }
        $data['expense_list'] = Expense::where('session_id', setting('session'))
            ->orderBy('id', 'desc') // or 'created_at' if you prefer
            ->limit(5)
            ->get();
            $data['balance'] = $data['income'] - $data['expense'];

        // Quarter-based summary per fee group (expected per quarter = fees_amount/4; remained = quater_one..quater_four; paid = total - remained; % remaining per quarter)
        $data['quarter_summary_by_group'] = $this->getQuarterSummaryByFeesGroups();

        // Last 5 fee collections (student name, class, amount, date)
        $data['last_fees_collects'] = FeesCollect::query()
            ->where('session_id', setting('session'))
            ->with(['student', 'feesAssignChild.feesMaster'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Attach student class name (current session) for each fee collect
        $studentIds = $data['last_fees_collects']->pluck('student_id')->unique()->filter()->values();
        $classMap = [];
        if ($studentIds->isNotEmpty()) {
            $sessionId = setting('session');
            $assignments = SessionClassStudent::whereIn('student_id', $studentIds)
                ->where('session_id', $sessionId)
                ->with('class')
                ->get();
            foreach ($assignments as $scs) {
                $classMap[$scs->student_id] = $scs->class ? $scs->class->name : '—';
            }
        }
        foreach ($data['last_fees_collects'] as $fc) {
            $fc->student_class_name = $classMap[$fc->student_id ?? 0] ?? '—';
        }

        return $data;
    }

    /**
     * Per fee group: quarter expected (fees_amount/4 summed), remained (quater_one..four), paid, and % remaining per quarter.
     * Uses fees_assign_childrens; if quater_* columns missing, only total expected from fees_amount/4 is used.
     */
    public function getQuarterSummaryByFeesGroups(): array
    {
        $groups = FeesGroup::where('dashboard_order', '!=', 0)->orderBy('dashboard_order')->get();
        $result = [];
        $hasQuarterColumns = Schema::hasColumns('fees_assign_childrens', ['quater_one', 'quater_two', 'quater_three', 'quater_four']);

        foreach ($groups as $group) {
            $baseQuery = "FROM fees_assign_childrens
                INNER JOIN students ON students.id = fees_assign_childrens.student_id
                INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id
                WHERE fees_masters.fees_group_id = ? AND students.status = 1";
            $params = [$group->id];

            $expectedPerQuarter = DB::selectOne("
                SELECT SUM(COALESCE(fees_amount, 0) / 4) AS total
                $baseQuery
            ", $params);
            $expectedQ = (float) ($expectedPerQuarter->total ?? 0);

            $remainedQ1 = $remainedQ2 = $remainedQ3 = $remainedQ4 = 0;
            if ($hasQuarterColumns) {
                $rem = DB::selectOne("
                    SELECT
                        SUM(COALESCE(quater_one, 0)) AS r1,
                        SUM(COALESCE(quater_two, 0)) AS r2,
                        SUM(COALESCE(quater_three, 0)) AS r3,
                        SUM(COALESCE(quater_four, 0)) AS r4
                    $baseQuery
                ", $params);
                if ($rem) {
                    $remainedQ1 = (float) ($rem->r1 ?? 0);
                    $remainedQ2 = (float) ($rem->r2 ?? 0);
                    $remainedQ3 = (float) ($rem->r3 ?? 0);
                    $remainedQ4 = (float) ($rem->r4 ?? 0);
                }
            }

            $totalExpected = $expectedQ * 4;
            $totalRemained = $remainedQ1 + $remainedQ2 + $remainedQ3 + $remainedQ4;
            $totalPaid = $totalExpected - $totalRemained;

            $pct1 = $expectedQ > 0 ? round(($remainedQ1 / $expectedQ) * 100, 1) : 0;
            $pct2 = $expectedQ > 0 ? round(($remainedQ2 / $expectedQ) * 100, 1) : 0;
            $pct3 = $expectedQ > 0 ? round(($remainedQ3 / $expectedQ) * 100, 1) : 0;
            $pct4 = $expectedQ > 0 ? round(($remainedQ4 / $expectedQ) * 100, 1) : 0;

            $result[$group->name] = [
                'group_name'       => $group->name,
                'quarters'        => [
                    'Quarter 1' => ['expected' => $expectedQ, 'remained' => $remainedQ1, 'paid' => $expectedQ - $remainedQ1, 'pct_remaining' => $pct1],
                    'Quarter 2' => ['expected' => $expectedQ, 'remained' => $remainedQ2, 'paid' => $expectedQ - $remainedQ2, 'pct_remaining' => $pct2],
                    'Quarter 3' => ['expected' => $expectedQ, 'remained' => $remainedQ3, 'paid' => $expectedQ - $remainedQ3, 'pct_remaining' => $pct3],
                    'Quarter 4' => ['expected' => $expectedQ, 'remained' => $remainedQ4, 'paid' => $expectedQ - $remainedQ4, 'pct_remaining' => $pct4],
                ],
                'total_expected'   => $totalExpected,
                'total_remained'   => $totalRemained,
                'total_paid'      => $totalPaid,
            ];
        }

        return $result;
    }

    public function getSummaryByTerm($term)
    {
        
       $data['fees_groups'] = FeesGroup::where('dashboard_order','!=', 0)
            ->orderBy('dashboard_order')
            ->get();

        $data['collection_summary'] = [];

        foreach ($data['fees_groups'] as $group) {
            $data['collection_summary'][$group->name] = [
                'total' => $this->getTotalPerTerm($group->id,$term),
                'paid' => $this->getTotalPaidPerTerm($group->id,$term)
            ];
        }
        return $data;
    }

    public function getTotal($id)
    {
        $total = DB::select("
            SELECT SUM(fees_amount) AS total 
            FROM fees_assign_childrens 
            INNER JOIN students ON students.id = fees_assign_childrens.student_id
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id 
            WHERE fees_masters.fees_group_id = ? AND students.status = ?", [$id,"1"]);

        return $total[0]->total ?? 0;
    }

   public function getTotalPerTerm($id, $term)
{
    // Map term to columns
    $columns = [
        '1' => 'quater_one',
        '2' => 'quater_one + quater_two',
        '3' => 'quater_one + quater_two + quater_three',
        '4' => 'quater_one + quater_two + quater_three + quater_four',
    ];

    // Ensure a valid term is provided
    if (!array_key_exists($term, $columns)) {
        return 0;
    }

    // Construct and run the query
    $query = "
        SELECT SUM({$columns[$term]}) AS total 
        FROM fees_assign_childrens 
         INNER JOIN students ON students.id = fees_assign_childrens.student_id
        INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id 
        WHERE fees_masters.fees_group_id = ? AND students.status = ?
    ";

    $result = DB::select($query, [$id,"1"]);

    return $result[0]->total ?? 0;
}


      public function getTotalPaid($id)
    {
        $total = DB::select("
            SELECT SUM(paid_amount) AS total 
            FROM fees_assign_childrens 
            INNER JOIN students ON students.id = fees_assign_childrens.student_id
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id 
            WHERE fees_masters.fees_group_id = ? AND students.status = ?", [$id,"1"]);

        return $total[0]->total ?? 0;
    }

     public function getTotalPaidPerTerm($id, $term)
{
    // Define how many quarters to include based on the term
    $quarters = [
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
    ];

    if (!isset($quarters[$term])) {
        return 0;
    }

    // Build the total quater_amount sum expression
    $totalQuaterAmount = implode(' + ', array_fill(0, $quarters[$term], 'quater_amount'));

    // Build the quarter deduction expression
    $deductions = [];
    if ($term >= 1) $deductions[] = 'quater_one';
    if ($term >= 2) $deductions[] = 'quater_two';
    if ($term >= 3) $deductions[] = 'quater_three';
    if ($term >= 4) $deductions[] = 'quater_four';

    $deductionExpr = implode(' + ', $deductions);

    // Final SQL expression: (total quater_amount sum - sum of deducted quarters)
    $sql = "
        SELECT SUM(($totalQuaterAmount) - ($deductionExpr)) AS total 
        FROM fees_assign_childrens 
        INNER JOIN students ON students.id = fees_assign_childrens.student_id
        INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id 
        WHERE fees_masters.fees_group_id =  ? AND students.status = ?
    ";

    $result = DB::select($sql, [$id,"1"]);

    return $result[0]->total ?? 0;
}


    public function feesCollectionYearly() {
        $data = [];
        for($i = 1; $i <= 12; $i++) {
            $data[] = FeesCollect::where('session_id', setting('session'))->whereMonth('date', $i)->sum('amount');
        }
        return $data;
    }

    public function revenueYearly() {
        $data['income']  = [];
        $data['expense'] = [];
        $data['revenue'] = [];

        $n = 0;
        for($i = 1; $i <= date('m'); $i++) {
            $data['income'][]  = Income::where('session_id', setting('session'))->whereMonth('date', $i)->sum('amount');
            $data['expense'][] = Expense::where('session_id', setting('session'))->whereMonth('date', $i)->sum('amount');
            $data['revenue'][] = $data['income'][$n] - $data['expense'][$n];
            $n++;
        }
        return $data;
    }

    public function feesCollection() {
        for ($i = 1; $i <=  date('t'); $i++) {
            $data['collection'][] = FeesCollect::where('session_id', setting('session'))->whereMonth('date', date('m'))->whereDay('date', $i)->sum('amount');
            $data['dates'][]      = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return response()->json($data, 200);
    }

    public function incomeExpense() {
        for ($i = 1; $i <=  date('t'); $i++) {
            $data['incomes'][]  = Income::where('session_id', setting('session'))->whereMonth('date', date('m'))->whereDay('date', $i)->sum('amount');
            $data['expenses'][] = Expense::where('session_id', setting('session'))->whereMonth('date', date('m'))->whereDay('date', $i)->sum('amount');
            $data['dates'][]    = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return response()->json($data, 200);
    }

    public function attendance() {
        $items = ClassSetup::active()->where('session_id', setting('session'))->get();

        $data['classes'] = [];
        $data['present'] = [];
        $data['absent']  = [];

        $data['classes'] = [];
        foreach ($items as $key => $value) {
            $data['classes'][] = $value->class->name;
            $data['present'][] = Attendance::where('session_id', setting('session'))
                                ->where('classes_id', $value->classes_id)
                                ->whereDay('date', date('d'))
                                ->whereIn('attendance', [AttendanceType::PRESENT, AttendanceType::LATE, AttendanceType::HALFDAY])
                                ->count();
            $data['absent'][]  = Attendance::where('session_id', setting('session'))
                                ->where('classes_id', $value->classes_id)
                                ->whereDay('date', date('d'))
                                ->where('attendance', AttendanceType::ABSENT)
                                ->count();
        }
        return $data;
    }

    public function eventsCurrentMonth() {
        $events = Event::where('session_id', setting('session'))->active()->whereMonth('date', date('m'))->orderBy('date')->get();
        $data = [];
        foreach ($events as $key => $value) {
            $data[] = [
                'title' => $value->title,
                'start' => $value->date.'T'.$value->start_time,
                'end'   => $value->date.'T'.$value->end_time,
            ];
        }
        return response()->json($data, 200);
    }

    /**
     * Income and expense totals for a given period. Dates in Y-m-d.
     * period: day|weekly|monthly|yearly|custom (custom requires start_date, end_date).
     */
    public function getIncomeExpenseByPeriod(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $sessionId = setting('session');
        $today = date('Y-m-d');

        switch ($period) {
            case 'day':
                $from = $to = $today;
                break;
            case 'weekly':
                $to = $today;
                $from = date('Y-m-d', strtotime('-6 days', strtotime($today)));
                break;
            case 'monthly':
                $from = date('Y-m-01');
                $to = date('Y-m-t');
                break;
            case 'yearly':
                $from = date('Y-01-01');
                $to = date('Y-12-31');
                break;
            case 'custom':
                $from = $startDate ? date('Y-m-d', strtotime($startDate)) : $today;
                $to = $endDate ? date('Y-m-d', strtotime($endDate)) : $today;
                if ($from > $to) {
                    [$from, $to] = [$to, $from];
                }
                break;
            default:
                $from = date('Y-01-01');
                $to = date('Y-12-31');
        }

        $income = Income::where('session_id', $sessionId)
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $expense = Expense::where('session_id', $sessionId)
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        return [
            'income'   => (float) $income,
            'expense'  => (float) $expense,
            'balance'  => (float) ($income - $expense),
            'from'     => $from,
            'to'       => $to,
        ];
    }

}

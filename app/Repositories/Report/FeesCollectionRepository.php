<?php

namespace App\Repositories\Report;

use App\Interfaces\Report\FeesCollectionInterface;
use App\Traits\ReturnFormatTrait;
use App\Models\Fees\FeesAssignChildren;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeesCollectionRepository implements FeesCollectionInterface
{
    use ReturnFormatTrait;

    private function dateRangeFromRequest($request): array
    {
        $from = trim((string) $request->input('date_from', ''));
        $to = trim((string) $request->input('date_to', ''));
        if ($from !== '' && $to !== '') {
            return [date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to))];
        }

        $dates = trim((string) $request->input('dates', ''));
        if ($dates === '') {
            $year = date('Y');

            return [$year . '-01-01', $year . '-12-31'];
        }

        $parts = explode(' - ', $dates);
        if (count($parts) !== 2) {
            $year = date('Y');

            return [$year . '-01-01', $year . '-12-31'];
        }

        $startDate = date('Y-m-d', strtotime($parts[0]));
        $endDate = date('Y-m-d', strtotime($parts[1]));

        if (! $startDate || ! $endDate) {
            $year = date('Y');

            return [$year . '-01-01', $year . '-12-31'];
        }

        return [$startDate, $endDate];
    }

    private function collectionReportQuery($request)
    {
        [$startDate, $endDate] = $this->dateRangeFromRequest($request);
        $balanceStatus = (string) $request->input('balance_status', '0');
        $paymentPercentage = $request->input('payment_percentage');

        $query = DB::table('fees_assign_childrens as fac')
            ->join('fees_assigns as fa', function ($join) {
                $join->on('fa.id', '=', 'fac.fees_assign_id')
                    ->where('fa.session_id', setting('session'));
            })
            ->join('students', 'students.id', '=', 'fac.student_id')
            ->join('classes', 'classes.id', '=', 'fa.classes_id')
            ->leftJoin('sections', 'sections.id', '=', 'fa.section_id')
            ->leftJoin('fees_masters as fm', 'fm.id', '=', 'fac.fees_master_id')
            ->leftJoin('fees_types as ft', 'ft.id', '=', 'fm.fees_type_id')
            ->leftJoin('fees_groups as fg', 'fg.id', '=', 'fa.fees_group_id')
            ->where('fa.classes_id', '!=', 11)
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.mobile',
                'students.admission_no',
                'classes.name as class_name',
                'sections.name as section_name',
                DB::raw('COALESCE(SUM(fac.fees_amount), 0) as total_fees_amount'),
                DB::raw('COALESCE(SUM(fac.paid_amount), 0) as paid_amount'),
                DB::raw('COALESCE(SUM(fac.remained_amount), 0) as remained_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN fa.fees_group_id = 1 THEN fac.fees_amount ELSE 0 END), 0) as outstanding_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN fa.fees_group_id = 1 THEN fac.paid_amount ELSE 0 END), 0) as outstanding_paid_amount'),
                DB::raw('COALESCE(SUM(CASE WHEN fa.fees_group_id = 1 THEN fac.remained_amount ELSE 0 END), 0) as outstanding_remained_amount'),
                DB::raw('CASE WHEN COALESCE(SUM(fac.fees_amount), 0) > 0 THEN ROUND((COALESCE(SUM(fac.paid_amount), 0) / COALESCE(SUM(fac.fees_amount), 0)) * 100, 2) ELSE 0 END as paid_percentage'),
                DB::raw('COUNT(DISTINCT fac.id) as assigned_items_count'),
                DB::raw('MAX(fac.updated_at) as last_activity_at')
            );

        if ($request->filled('class') && $request->class != '0') {
            $query->where('fa.classes_id', $request->class);
        }

        if ($request->filled('section') && $request->section != '0') {
            $query->where('fa.section_id', $request->section);
        }

        if ($request->filled('fee_group_id') && $request->fee_group_id != '0') {
            $query->where('fa.fees_group_id', $request->fee_group_id);
        }

        if ($startDate && $endDate) {
            $query->whereDate('fac.created_at', '>=', $startDate)
                ->whereDate('fac.created_at', '<=', $endDate);
        }

        $query->groupBy(
            'students.id',
            'students.first_name',
            'students.last_name',
            'students.mobile',
            'students.admission_no',
            'classes.name',
            'sections.name'
        );

        if ($balanceStatus === '1' || $balanceStatus === 'paid') {
            $query->havingRaw('remained_amount <= 0');
        } elseif ($balanceStatus === '2' || $balanceStatus === 'with_balance') {
            $query->havingRaw('remained_amount > 0');
        } elseif ($balanceStatus === 'partial') {
            $query->havingRaw('paid_amount > 0 AND remained_amount > 0');
        } elseif ($balanceStatus === 'unpaid') {
            $query->havingRaw('paid_amount <= 0 AND remained_amount > 0');
        }

        if ($paymentPercentage !== null && $paymentPercentage !== '' && is_numeric($paymentPercentage)) {
            $query->havingRaw('paid_percentage <= ?', [(float) $paymentPercentage]);
        }

        return $query->orderBy('classes.name')->orderBy('students.first_name')->orderBy('students.last_name');
    }

    public function collectionReport($request, bool $paginate = true, int $perPage = 20)
    {
        $query = $this->collectionReportQuery($request);

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    public function collectionReportTotals($request): array
    {
        $totals = DB::query()
            ->fromSub($this->collectionReportQuery($request), 'fees_collection_report')
            ->selectRaw('
                COUNT(*) as students_count,
                COALESCE(SUM(total_fees_amount), 0) as total_fees_amount,
                COALESCE(SUM(paid_amount), 0) as paid_amount,
                COALESCE(SUM(remained_amount), 0) as remained_amount,
                COALESCE(SUM(outstanding_amount), 0) as outstanding_amount,
                COALESCE(SUM(outstanding_paid_amount), 0) as outstanding_paid_amount,
                COALESCE(SUM(outstanding_remained_amount), 0) as outstanding_remained_amount,
                COALESCE(SUM(assigned_items_count), 0) as assigned_items_count
            ')
            ->first();

        return [
            'students_count' => (int) ($totals->students_count ?? 0),
            'total_fees_amount' => (float) ($totals->total_fees_amount ?? 0),
            'paid_amount' => (float) ($totals->paid_amount ?? 0),
            'remained_amount' => (float) ($totals->remained_amount ?? 0),
            'outstanding_amount' => (float) ($totals->outstanding_amount ?? 0),
            'outstanding_paid_amount' => (float) ($totals->outstanding_paid_amount ?? 0),
            'outstanding_remained_amount' => (float) ($totals->outstanding_remained_amount ?? 0),
            'assigned_items_count' => (int) ($totals->assigned_items_count ?? 0),
        ];
    }

    public function getAll()
    {

        
    // $groups = DB::table('fees_assign_childrens')
    // ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
    // ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
    // ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
    // ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
    // ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id')

    // // Only include MAIN fees (not group_id = 1)
    // ->where('fees_assigns.fees_group_id', '!=', 1)
    // ->where('fees_assigns.session_id', setting('session'))

    // // LEFT JOIN to subquery for group_id = 1 to get optional outstanding_remained
    // ->leftJoin(DB::raw('
    //     (
    //         SELECT 
    //             student_id,
    //             SUM(remained_amount) as outstanding_remained
    //         FROM fees_assign_childrens
    //         JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
    //         WHERE fees_assigns.fees_group_id = 1
    //         GROUP BY student_id
    //     ) as group1_fees
    // '), 'students.id', '=', 'group1_fees.student_id')

    // ->select(
    //     'students.first_name',
    //     'students.last_name',
    //     'fees_assign_childrens.fees_amount',
    //     'fees_assign_childrens.paid_amount',
    //     'fees_assign_childrens.quater_one',
    //     'fees_assign_childrens.quater_two',
    //     'fees_assign_childrens.remained_amount',
    //     'group1_fees.outstanding_remained',
    //     'classes.name as class_name',
    //     'fees_types.name as type_name'
    // )
    // ->groupBy(
    //     'students.id',
    //     'students.first_name',
    //     'students.last_name',
    //     'fees_assign_childrens.fees_amount',
    //     'fees_assign_childrens.paid_amount',
    //     'fees_assign_childrens.remained_amount',
    //     'group1_fees.outstanding_remained',
    //     'fees_assign_childrens.quater_one',
    //     'fees_assign_childrens.quater_two',
    //     'classes.name',
    //     'fees_types.name'
    // )
    // ->paginate(20);
    $groups = DB::table('students')
    ->select(
        'students.id',
        'students.first_name',
        'students.last_name',
        'students.mobile',
        'classes.name as class_name',

        // Group 1: fees_group_id = 1
        DB::raw("COALESCE(SUM(CASE WHEN fa1.fees_group_id = 1 THEN fc1.fees_amount ELSE 0 END), 0) AS outstanding_amount"),
        DB::raw("COALESCE(SUM(CASE WHEN fa1.fees_group_id = 1 THEN fc1.paid_amount ELSE 0 END), 0) AS outstanding_paid_amount"),
        DB::raw("COALESCE(SUM(CASE WHEN fa1.fees_group_id = 1 THEN fc1.remained_amount ELSE 0 END), 0) AS outstanding_remained_amount"),

        // Group 2: fees_group_id = 2
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.fees_amount ELSE 0 END), 0) AS fees_amount"),
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.quater_one ELSE 0 END), 0) AS quater_one"),
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.quater_two ELSE 0 END), 0) AS quater_two"),
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.quater_three ELSE 0 END), 0) AS quater_three"),
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.quater_four ELSE 0 END), 0) AS quater_four"),
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.remained_amount ELSE 0 END), 0) AS remained_amount"),
        DB::raw("COALESCE(SUM(CASE WHEN fa2.fees_group_id = 2 THEN fc2.paid_amount ELSE 0 END), 0) AS paid_amount"),

        // Group 3: fees_group_id = 3
        DB::raw("COALESCE(SUM(CASE WHEN fa3.fees_group_id = 3 THEN fc3.fees_amount ELSE 0 END), 0) AS group3_amount")
    )
    ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
    ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
    ->where('session_class_students.classes_id', '!=', 11)

    // Joins for group 1
    ->leftJoin('fees_assign_childrens as fc1', 'fc1.student_id', '=', 'students.id')
    ->leftJoin('fees_assigns as fa1', function($join) {
        $join->on('fa1.id', '=', 'fc1.fees_assign_id')
             ->where('fa1.session_id', '=', setting('session'));
    })

    // Joins for group 2
    ->leftJoin('fees_assign_childrens as fc2', 'fc2.student_id', '=', 'students.id')
    ->leftJoin('fees_assigns as fa2', function($join) {
        $join->on('fa2.id', '=', 'fc2.fees_assign_id')
             ->where('fa2.session_id', '=', setting('session'));
    })

    // Joins for group 3
    ->leftJoin('fees_assign_childrens as fc3', 'fc3.student_id', '=', 'students.id')
    ->leftJoin('fees_assigns as fa3', function($join) {
        $join->on('fa3.id', '=', 'fc3.fees_assign_id')
             ->where('fa3.session_id', '=', setting('session'));
    })

    ->groupBy(
        'students.id',
        'students.first_name',
        'students.last_name',
        'students.mobile',
        'classes.name'
    )
    ->paginate(20);


        return $groups;
    }

    public function getAllStudents(){
        $groups = DB::table('students')
        ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
        ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
        ->join('sections', 'sections.id', '=', 'session_class_students.section_id')
        ->join('parent_guardians', 'parent_guardians.id', '=', 'students.parent_guardian_id')
        ->where('session_class_students.session_id', setting('session'))
        ->where('session_class_students.classes_id', '!=', 11)
        ->select('students.*','classes.name as class_name','sections.name as section_name','parent_guardians.guardian_mobile')
        ->paginate(10);
        return $groups;
    }
    public function getAllStudentsSearch($class,$dates){

        
        $groups = DB::table('students')
        ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
        ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
        ->join('sections', 'sections.id', '=', 'session_class_students.section_id')
        ->join('parent_guardians', 'parent_guardians.id', '=', 'students.parent_guardian_id')
        ->where('session_class_students.session_id', setting('session'))
        ->where('session_class_students.classes_id', '!=', 11)
        ->select('students.*','classes.name as class_name','sections.name as section_name','parent_guardians.guardian_mobile')
        ->orderBy('students.first_name', 'asc');
        if ($class != "") {
            if ($class != "0" && $class != "N") {
                if($class == "SHIFTED"){
                    $groups = $groups->where('students.active', "2");
                }else{
                    $groups = $groups->where('classes.id', $class);
                }
                
            }
        }
    if($class == "N"){
         if($dates != ""){
            if (!empty($dates) ) {
                [$startDate, $endDate] = explode(' - ', $dates);
        $startDateFormatted = date('Y-m-d', strtotime($startDate));
        $endDateFormatted   = date('Y-m-d', strtotime($endDate));
                $groups = $groups->whereBetween('admission_date', [
                    $startDateFormatted ,
                    $endDateFormatted ,
                ]);
            }
        }
    }
         $groups = $groups->paginate(400);
        return $groups;
    }

    public function search($request)
    {
        

        $groups = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
            ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
            ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id')
            ->where('fees_assigns.session_id', setting('session'))
            ->where('fees_assigns.classes_id', '!=', 11)
            ->select(
                'students.*',
                'fees_assign_childrens.*',
                'fees_assigns.*',
                'classes.name as class_name',
                'fees_types.name as type_name'
            );

        if ($request->class != "") {
            if ($request->class != "0") {
                $groups = $groups->where('fees_assigns.classes_id', $request->class);
            }
        }

        if (!empty($request->section)) {
            if ($request->section != "0") {
                if ($request->section != "1") {
                    $groups = $groups->where('fees_assign_childrens.remained_amount', '>', 0);
                } else {
                    $groups = $groups->where('fees_assign_childrens.remained_amount', '=', 0);
                }
            }
        }

        if ($request->dates != "") {
            $startDate = date('Y-m-d', strtotime(substr($request->dates, 0, 10))); // Extract start date
            $endDate = date('Y-m-d', strtotime(substr($request->dates, 13, 23))); // Extract end date
            $groups = $groups->whereBetween('fees_assign_childrens.created_at', [$startDate, $endDate]); // Ensure `fees_collect` table is joined
        }

        $groups = $groups->paginate(20);
        return $groups;


    }

    public function getAllSumary($request)
{
    try {
        // Log::info($request);
        // $groups = DB::table('fees_assign_childrens')
        //     ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
        //     ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
        //     ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
        //     ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
        //     ->leftJoin('fees_assign_childrens as a', function ($join) {
        //         $join->on('students.id', '=', 'a.student_id')
        //             ->whereRaw('fees_assign_childrens.id != a.id');
        //     })
        //     ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id')
        //     ->where('fees_assigns.session_id', setting('session'))
        //     ->where('fees_assigns.fees_group_id', '!=', "1")
        //     ->select(
        //         DB::raw('SUM(a.remained_amount) as outstanding_remained'),
        //         'students.first_name',
        //         'students.last_name',
        //         'fees_assign_childrens.fees_amount',
        //         'fees_assign_childrens.paid_amount',
        //         'fees_assign_childrens.remained_amount',
        //         'classes.name as class_name',
        //         'fees_types.name as type_name'
        //     );

        $sessionId = setting('session');

        $feeGroupId = (string) ($request->fee_group_id ?? '0');

        $feesCollectTotals = DB::table('fees_collects')
            ->select(
                'fees_collects.student_id',
                DB::raw('SUM(COALESCE(fees_collects.amount, 0) + COALESCE(fees_collects.fine_amount, 0)) AS paid_from_collections')
            )
            ->where('fees_collects.session_id', $sessionId);

        if ($feeGroupId !== '1' && $feeGroupId !== '2' && $feeGroupId !== '3' && $feeGroupId !== '4') {
            if (! empty($request->dates)) {
                $dates = explode(' - ', $request->dates);
                if (count($dates) === 2) {
                    $startDate = date('Y-m-d', strtotime($dates[0]));
                    $endDate = date('Y-m-d', strtotime($dates[1]));
                    $feesCollectTotals->whereBetween('fees_collects.date', [$startDate, $endDate]);
                }
            }
        }

        $feesCollectTotals = $feesCollectTotals->groupBy('fees_collects.student_id');

        $assignCommentsSub = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->where('fees_assigns.session_id', $sessionId)
            ->select(
                'fees_assign_childrens.student_id',
                DB::raw("GROUP_CONCAT(DISTINCT CASE WHEN fees_assign_childrens.comment IS NOT NULL AND TRIM(fees_assign_childrens.comment) <> '' AND TRIM(fees_assign_childrens.comment) <> 'completed' THEN TRIM(fees_assign_childrens.comment) END ORDER BY fees_assign_childrens.comment SEPARATOR ' | ') AS assign_comments")
            )
            ->groupBy('fees_assign_childrens.student_id');

        $groups = DB::table('students')
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.mobile',
                'students.active',
                DB::raw('COALESCE(SUM(group1.fees_amount), 0) AS outstanding_amount'),
                DB::raw('COALESCE(SUM(group1.paid_amount), 0) AS outstanding_paid_amount'),
                DB::raw('COALESCE(SUM(group1.remained_amount), 0) AS outstanding_remained_amount'),
                DB::raw('COALESCE(SUM(group2.fees_amount), 0) AS fees_amount'),
                DB::raw('COALESCE(SUM(group2.quater_one), 0) AS quater_one'),
                DB::raw('COALESCE(SUM(group2.quater_two), 0) AS quater_two'),
                DB::raw('COALESCE(SUM(group2.quater_three), 0) AS quater_three'),
                DB::raw('COALESCE(SUM(group2.quater_four), 0) AS quater_four'),
                DB::raw('COALESCE(SUM(group2.remained_amount), 0) AS remained_amount'),
                DB::raw('COALESCE(SUM(group2.paid_amount), 0) AS paid_amount'),
                DB::raw('COALESCE(SUM(group3.fees_amount), 0) AS group3_amount'),
                DB::raw('COALESCE(SUM(group3.paid_amount), 0) AS group3_paid_amount'),
                DB::raw('COALESCE(SUM(group3.remained_amount), 0) AS group3_remained_amount'),
                DB::raw('COALESCE(SUM(group4.fees_amount), 0) AS group4_amount'),
                DB::raw('COALESCE(SUM(group4.paid_amount), 0) AS group4_paid_amount'),
                DB::raw('COALESCE(SUM(group4.remained_amount), 0) AS group4_remained_amount'),
                DB::raw('COALESCE(SUM(group_admission.fees_amount), 0) AS admission_fees_amount'),
                DB::raw('COALESCE(SUM(group_admission.paid_amount), 0) AS admission_paid_amount'),
                DB::raw('COALESCE(SUM(group_admission.remained_amount), 0) AS admission_remained_amount'),
                DB::raw('(COALESCE(SUM(group2.fees_amount), 0) + COALESCE(SUM(group3.fees_amount), 0) + COALESCE(SUM(group4.fees_amount), 0) + COALESCE(SUM(group_admission.fees_amount), 0)) AS fees_amount_excluding_outstanding'),
                DB::raw('(COALESCE(SUM(group1.fees_amount), 0) + COALESCE(SUM(group2.fees_amount), 0) + COALESCE(SUM(group3.fees_amount), 0) + COALESCE(SUM(group4.fees_amount), 0) + COALESCE(SUM(group_admission.fees_amount), 0)) AS total_assigned_amount'),
                DB::raw('COALESCE(MAX(fee_collects_sum.paid_from_collections), 0) AS paid_from_collections'),
                DB::raw('(COALESCE(SUM(group1.fees_amount), 0) + COALESCE(SUM(group2.fees_amount), 0) + COALESCE(SUM(group3.fees_amount), 0) + COALESCE(SUM(group4.fees_amount), 0) + COALESCE(SUM(group_admission.fees_amount), 0) - COALESCE(MAX(fee_collects_sum.paid_from_collections), 0)) AS remained_after_collections'),
                DB::raw("COALESCE(MAX(assign_comments_sub.assign_comments), '') AS assign_comments"),
                'classes.name as class_name'
            )
            ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
            ->where('session_class_students.session_id', $sessionId)
            ->where('session_class_students.classes_id', '!=', 11)
            ->leftJoinSub($feesCollectTotals, 'fee_collects_sum', function ($join) {
                $join->on('fee_collects_sum.student_id', '=', 'students.id');
            })
            ->leftJoinSub($assignCommentsSub, 'assign_comments_sub', function ($join) {
                $join->on('assign_comments_sub.student_id', '=', 'students.id');
            })
    ->leftJoin('fees_assign_childrens as group1', function ($join) {
        $join->on('group1.student_id', '=', 'students.id')
             ->whereIn('group1.fees_assign_id', function ($query) {
                 $query->select('id')->from('fees_assigns')->where('fees_group_id', 1);
             });
    })
    ->leftJoin('fees_assign_childrens as group2', function ($join) {
        $join->on('group2.student_id', '=', 'students.id')
             ->whereIn('group2.fees_assign_id', function ($query) {
                 $query->select('id')->from('fees_assigns')->where('fees_group_id', 2);
             });
    })
    ->leftJoin('fees_assign_childrens as group3', function ($join) {
        $join->on('group3.student_id', '=', 'students.id')
             ->whereIn('group3.fees_assign_id', function ($query) {
                 $query->select('id')->from('fees_assigns')->where('fees_group_id', 3);
             });
    })
    ->leftJoin('fees_assign_childrens as group4', function ($join) {
        $join->on('group4.student_id', '=', 'students.id')
             ->whereIn('group4.fees_assign_id', function ($query) {
                 $query->select('id')->from('fees_assigns')->where('fees_group_id', 4);
             });
    })
    ->leftJoin('fees_assign_childrens as group_admission', function ($join) {
        $join->on('group_admission.student_id', '=', 'students.id')
             ->whereIn('group_admission.fees_assign_id', function ($query) {
                 $query->select('fees_assigns.id')
                     ->from('fees_assigns')
                     ->join('fees_groups', 'fees_groups.id', '=', 'fees_assigns.fees_group_id')
                     ->where('fees_groups.name', 'Admission Fee');
             });
    });

// 🔍 Filter by Class
if (!empty($request->class) && $request->class != "0") {
    $groups->where('session_class_students.classes_id', $request->class);
}

if (!empty($request->fee_group_id) && $request->fee_group_id != "0") {
    if ($request->fee_group_id == "1") {
        $groups->havingRaw('outstanding_amount > 0 OR outstanding_remained_amount > 0');
    } elseif ($request->fee_group_id == "2") {
        $groups->havingRaw('fees_amount > 0');
    } elseif ($request->fee_group_id == "3") {
        $groups->havingRaw('group3_amount > 0');
    } elseif ($request->fee_group_id == "4") {
        $groups->havingRaw('group4_amount > 0');
    }
}

// 🔍 Filter by Section
if (!empty($request->section) && $request->section != "0") {
    if ($request->section == "2") {
        $groups->havingRaw('paid_amount < fees_amount');
    } else {
        $groups->havingRaw('paid_amount >= fees_amount');
    }
}

if (!empty($request->amount) && $request->amount != "0") {
    $groups->havingRaw('remained_amount > ?', [(float) $request->amount]);
}

// 🔍 Filter by Date (only if fee_group_id is not 1, 2, 3, or 4)
if ($feeGroupId !== '1' && $feeGroupId !== '2' && $feeGroupId !== '3' && $feeGroupId !== '4') {
    if (!empty($request->dates)) {
        $dates = explode(' - ', $request->dates);
        if (count($dates) == 2) {
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
            $groups->whereBetween('group2.created_at', [$startDate, $endDate]);
        }
    }
}

// 📦 Group & Paginate
$groups = $groups
    ->groupBy(
        'students.id',
        'students.first_name',
        'students.last_name',
        'students.mobile',
        'students.active',
        'classes.name'
    )
    ->paginate(400);
        
        return $groups;

    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error in getAllSumary: ' . $e->getMessage());

        // Return a generic error response or empty result set
        return response()->json([
            'message' => 'Something went wrong while fetching the summary.',
            'error' => $e->getMessage()
        ], 500);
    }
}


//     public function getAllSumary($request)
//     {
        

//         $groups = DB::table('fees_assign_childrens')
//     ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
//     ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
//     ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
//     ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
//     ->leftJoin('fees_assign_childrens as a', function ($join) {
//         $join->on('students.id', '=', 'a.student_id')
//              ->whereRaw('fees_assign_childrens.id != a.id'); // Ensuring alias works correctly
//     })
//     ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id')
//     ->where('fees_assigns.session_id', setting('session'))
//     ->where('fees_assigns.fees_group_id','!=', "1")
//     ->select(
//         DB::raw('SUM(a.remained_amount) as outstanding_remained'), // Ensure aggregation works correctly
//         'students.first_name',
//         'students.last_name',
//         'fees_assign_childrens.fees_amount',
//         'fees_assign_childrens.paid_amount',
//         'fees_assign_childrens.remained_amount',
//         'classes.name as class_name',
//         'fees_types.name as type_name'
//     );

// // **Filter by Class**
// if (!empty($request->class) && $request->class != "0") {
//     $groups = $groups->where('fees_assigns.classes_id', $request->class);
// }

// // **Filter by Section**
// if (!empty($request->section) && $request->section != "0") {
//     if ($request->section == "2") {
//         $groups = $groups->whereRaw('fees_assign_childrens.paid_amount < fees_assign_childrens.fees_amount');
//     } else {
//         $groups = $groups->whereRaw('fees_assign_childrens.paid_amount >= fees_assign_childrens.fees_amount');
//     }
// }

// // **Filter by Date**
// if (!empty($request->dates)) {
//     $dates = explode(' - ', $request->dates); // Assuming format "YYYY-MM-DD - YYYY-MM-DD"
//     if (count($dates) == 2) {
//         $startDate = date('Y-m-d', strtotime($dates[0]));
//         $endDate = date('Y-m-d', strtotime($dates[1]));
//         $groups = $groups->whereBetween('fees_assign_childrens.created_at', [$startDate, $endDate]);
//     }
// }

// // **Group & Paginate**
// $groups = $groups->groupBy(
//     'fees_types.name', 'classes.name', 'students.id', 'students.first_name',
//     'students.last_name', 'fees_assign_childrens.fees_amount',
//     'fees_assign_childrens.paid_amount', 'fees_assign_childrens.remained_amount',
//     'fees_assign_childrens.id', 'fees_assigns.id', 'classes.id', 'fees_types.id'
// )->paginate(20);

        
//         return $groups;


//     }

    public function searchPDF($request)
    {
        // dd($request->all());
        $groups = FeesAssignChildren::withCount('feesCollect')->with('feesCollect')->having('fees_collect_count', '>=', 1);

        $groups = $groups->whereHas('feesAssign', function ($query) use ($request) {
            return $query->where('session_id', setting('session'))
                        ->where('classes_id', '!=', 11);
        });

        if ($request->class != "") {
            $groups = $groups->whereHas('student', function ($query) use ($request) {
                $query->whereHas('sessionStudentDetails', function ($query) use ($request) {
                    if ($request->class != "0") {
                        $query->where('classes_id', $request->class);
                    }                });
            });
        }

        if (!empty($request->section)) {
            if ($request->section != "0") {
                if ($request->section != "1") {
                    $groups = $groups->where('remained_amount', '>', 0);
                }else{
                    $groups = $groups->where('remained_amount', '=', 0);
                }
            }
        }

        if ($request->dates != "") {
            $groups = $groups->whereHas('feesCollect', function ($query) use ($request) {
                return $query->whereBetween('date', [
                    date('Y-m-d', strtotime(substr($request->dates, 0, 10))), // start date
                    date('Y-m-d', strtotime(substr($request->dates, 13, 23))) // end date
                ]);
            });
        }

        return $groups->get();
    }
}

  public function generateSummaryExcelBkp($class, $section, $dates, $fee_group_id)
    {
        $request = new Request([
            'class'        => $class,
            'dates'        => Crypt::decryptString($dates),
            'section'      => $section,
            'fee_group_id' => $fee_group_id,
        ]);

        $dates = explode(' - ', $request->dates);

        // Parse start and end dates
        $startDate = date('Y-m-d', strtotime($dates[0] ?? 'now'));
        $endDate   = date('Y-m-d', strtotime($dates[1] ?? 'now'));

        $groups = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
            ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
            ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id')
            ->where('fees_assigns.session_id', setting('session'));

// Apply Fee Group Filter
        // if (isset($request->fee_group_id)) {
        //     if (in_array($request->fee_group_id, [1, 2, 3])) {
        //         $groups = $groups->whereExists(function ($query) use ($request) {
        //             $query->select(DB::raw(1))
        //                 ->from('fees_assigns')
        //                 ->whereColumn('fees_assigns.id', 'fees_assign_childrens.fees_assign_id')
        //                 ->where('fees_assigns.fees_group_id', $request->fee_group_id);
        //         });
        //     }else{
        //         $groups = $groups->whereExists(function ($query) use ($request) {
        //             $query->select(DB::raw(1))
        //                 ->from('fees_assigns')
        //                 ->whereColumn('fees_assigns.id', 'fees_assign_childrens.fees_assign_id')
        //                 ->where('fees_assigns.fees_group_id', "2");
        //         }); 
        //     }
        // }

// Only join group tables if needed (either for all groups or specific group filtering)
        if (! isset($request->fee_group_id) || $request->fee_group_id == 0 ) {
            $groups = $groups
                ->leftJoin('fees_assign_childrens as group1', function ($join) {
                    $join->on('students.id', '=', 'group1.student_id')
                        ->on('group1.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 1 LIMIT 1)'));
                })
                ->leftJoin('fees_assign_childrens as group2', function ($join) {
                    $join->on('students.id', '=', 'group2.student_id')
                        ->on('group2.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 2 LIMIT 1)'));
                })
                ->leftJoin('fees_assign_childrens as group3', function ($join) {
                    $join->on('students.id', '=', 'group3.student_id')
                        ->on('group3.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 3 LIMIT 1)'));
                });
        }else if ( $request->fee_group_id == 1){
            $groups = $groups
                ->leftJoin('fees_assign_childrens as group1', function ($join) {
                    $join->on('students.id', '=', 'group1.student_id')
                        ->on('group1.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 1 LIMIT 1)'));
                })
                ->leftJoin('fees_assign_childrens as group2', function ($join) {
                    $join->on('students.id', '=', 'group2.student_id')
                        ->on('group2.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 2 LIMIT 1)'));
                })
                ->leftJoin('fees_assign_childrens as group3', function ($join) {
                    $join->on('students.id', '=', 'group3.student_id')
                        ->on('group3.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 3 LIMIT 1)'));
                });
        }

// Select fields
        $groups = $groups->select(
            DB::raw(
                 'COALESCE(SUM(fees_assign_childrens.fees_amount), 0) as outstanding_remained'
            ),
            'students.first_name',
            'students.last_name',
            'students.mobile',
            'fees_assign_childrens.fees_amount',
            'fees_assign_childrens.paid_amount',
            'fees_assign_childrens.remained_amount',
            'classes.name as class_name',
            'fees_types.name as type_name',
            'fees_assign_childrens.quater_one',
            'fees_assign_childrens.quater_two',
            'fees_assign_childrens.quater_three',
            'fees_assign_childrens.quater_four'
        );

// Filter by Class

    if (!empty($request->fee_group_id) && $request->fee_group_id == "4") {
        $groups = $groups->where('fees_assign_childrens.quater_two', '>', 0);
    }


        if (! empty($request->class) && $request->class != "0") {
            $groups = $groups->where('fees_assigns.classes_id', $request->class);
        }

// Filter by Section
        if (! empty($request->section) && $request->section != "0") {
            if ($request->section == "2") {
                $groups = $groups->whereRaw('fees_assign_childrens.paid_amount < fees_assign_childrens.fees_amount');
            } else {
                $groups = $groups->whereRaw('fees_assign_childrens.paid_amount >= fees_assign_childrens.fees_amount');
            }
        }

// Filter by Date
        if($request->fee_group_id != 1 && $request->fee_group_id != 2){
        if (! empty($request->dates)) {
            $dates = explode(' - ', $request->dates);
            if (count($dates) == 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate   = date('Y-m-d', strtotime($dates[1]));
                // $groups    = $groups->whereBetween('fees_assign_childrens.created_at', [$startDate, $endDate]);
            }
        }
    }

// Group By
        $groups = $groups->groupBy(
            'fees_types.name', 'classes.name', 'students.id', 'students.first_name',
            'students.last_name', 'fees_assign_childrens.fees_amount',
            'fees_assign_childrens.paid_amount', 'fees_assign_childrens.remained_amount',
            'fees_assign_childrens.id', 'fees_assigns.id', 'classes.id', 'fees_types.id',
            'fees_assign_childrens.quater_one', 'fees_assign_childrens.quater_two',
            'fees_assign_childrens.quater_three', 'fees_assign_childrens.quater_four',
            'students.mobile'
        );

        $groups->orderBy(DB::raw("CONCAT(students.first_name, ' ', students.last_name)"), 'ASC');
        $data = $groups->get()->toArray();

        // Prepare the data for the report
        if(false){
            $reportData = $this->formatSummaryForDayCareReportData($data);
             // Generate Excel
        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Mobile',
                    'Class',
                    'Fee Type',
                    'Fees Amount',
                    'Paid',
                    'Remained',
                    'Outstanding',
                    'Total Remained',
                    'Monthly',
                    'January',
                    'February'    ,
                    'March'   ,
                    'April'  ,
                    'May'   ,
                    'June' ,
                    'July'  ,
                    'August'   ,
                    'September'   ,
                    'October'  ,
                    'November' ,
                    'December'  ,
                    'Term One',
                    'Term Two',
                    'Term Three',
                    'Term Four',
                    
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };
        }else if($request->fee_group_id == 1){
            $reportData = $this->formatSummaryReportDataOut($data);
             // Generate Excel
        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Mobile',
                    'Class',
                    // 'Fee Type',
                    'Fees Amount',
                    'Paid Amount',
                    'Remained',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };
        }else{
            $reportData = $this->formatSummaryReportData($data);
             // Generate Excel
        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Mobile',
                    'Class',
                    'Fee Type',
                    'Fees Amount',
                    'Paid Amount',
                    // 'Paid Term Two',
                    'Remained',
                    'Outstanding',
                    'Paid Outstanding',
                    'Remained Outstanding',
                    'Total Remained',
                    'Remained Term One',
                    'Remained Term Two',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };
        }
        

       

        return Excel::download($export, 'Collection_Report.xlsx');
    }
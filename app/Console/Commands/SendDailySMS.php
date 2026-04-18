<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Accounts\Expense;
use App\Models\Accounts\Income;
use App\Enums\AccountHeadType;

class SendDailySMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily SMS messages to all users';

    /**
     * Execute the console command.
     *
     * @return int
     */

       public function searchPDF()
{
    $isIncome = AccountHeadType::INCOME; // You can pass this as a param to generalize

    // Set date range
    $startDate = today();
    $endDate = today();

    // Build base query
    $query =  Income::query() ;
      $query->where('session_id', setting('session'))
      ->whereBetween('date', [
          $startDate->format('Y-m-d'),
          $endDate->format('Y-m-d')
      ]);

    // Execute query
    $results = $query->get();

    // Prepare report data
    return [
        'result'     => $results,
        'report'     => $isIncome ? 'INCOME' : 'EXPENSES',
        'start_date' => $startDate->toDateString(),
        'end_date'   => $endDate->toDateString(),
        'sum'        => $results->sum('amount'),
        'cash'       => $results->sum('amount'),
        'bank'       => $results->where('account_number', '!=', '5')->sum('amount'),
    ];
}

   public function searchPDF2()
{
    $isIncome = AccountHeadType::EXPENSE; // You can pass this as a param to generalize

    // Set date range
    $startDate = today();
    $endDate = today();

    // Build base query
    $query =  Expense::query();
       $query->where('session_id', setting('session'))
      ->whereBetween('date', [
          $startDate->format('Y-m-d'),
          $endDate->format('Y-m-d')
      ]);

    // Execute query
    $results = $query->get();

    // Prepare report data
    return [
        'result'     => $results,
        'report'     =>  'EXPENSES',
        'start_date' => $startDate->toDateString(),
        'end_date'   => $endDate->toDateString(),
        'sum'        => $results->sum('amount'),
        'cash'       => $results->sum('amount'),
        'bank'       => $results->where('account_number', '!=', '5')->sum('amount'),
    ];
}
    public function handle()
    {

        // Replace this with your dynamic logic or pull from DB
        $data2 = $this->searchPDF2();
        $data = $this->searchPDF();
        //Rockland
         $mobiles = ['+255765438924,+255673508104','+255766025356' ];
         //Narivas School
        $smsText = 'Good Evening Sir, Todays ' . now()->format('Y-m-d H:i:s') . ' collection report summary, Income cash ' . number_format($data['cash']) . '. Expense ' . number_format($data2['cash']) . '. For more details report has been sent to your email. Thank you';

        $messages = [];
        foreach ($mobiles as $mobile) {
            $messages[] = [
                'from' => 'SCHOOL',
                'to' => $mobile,
                'text' => $smsText
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://messaging-service.co.tz/api/sms/v1/text/multi');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = [
            'messages' => $messages,
            'reference' => 'bulk-' . now()->format('YmdHis')
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers = [
            'Authorization: Basic ZmlsYmVydG46RXVzYWJpdXMxNzEwLg==', // Secure this via env
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->error('SMS Error: ' . curl_error($ch));
        } else {
            $this->info('SMS Sent: ' . $result);
        }

        curl_close($ch);

        // return Command::SUCCESS;
    }
}

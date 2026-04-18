<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
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
use Illuminate\Support\Facades\Log;
use App\Models\Accounts\Expense;
use App\Models\Accounts\Income;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Daily Report Mail',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
   public function content()
{
    return new Content(
        view: 'emails.daily_report',
    );
}

public function build()
{
    $data = $this->searchPDF();
    $data2 = $this->searchPDF2();

    Log::info($data); // Optional: log for debugging

    // Generate the PDF
    $pdf = PDF::loadView('backend.report.accountPDF', ['data' => $data]);
    $pdf2 = PDF::loadView('backend.report.accountPDF', ['data' => $data2]);

    // Attach the PDF to the email
    return $this->subject('Daily Report Mail')
                ->view('emails.daily_report')
                ->attachData($pdf->output(), 'daily_report_income.pdf', [
                    'mime' => 'application/pdf',
                ])
                ->attachData($pdf2->output(), 'daily_report_expense.pdf', [
                    'mime' => 'application/pdf',
                ]);
}

   public function searchPDF()
{
    $isIncome = AccountHeadType::INCOME; // You can pass this as a param to generalize

    // Set date range
    $startDate = today();
    $endDate = today();

    // Build base query
    $query = $isIncome ? Income::query() : Expense::query();
    $query->where('session_id', setting('session'))
          ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

    // Execute query
    $results = $query->get();

    // Prepare report data
    return [
        'result'     => $results,
        'report'     => $isIncome ? 'INCOME' : 'EXPENSES',
        'start_date' => $startDate->toDateString(),
        'end_date'   => $endDate->toDateString(),
        'sum'        => $results->sum('amount'),
        'cash'       => $results->where('account_number', '5')->sum('amount'),
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
    $query = $isIncome ? Income::query() : Expense::query();
    $query->where('session_id', setting('session'))
          ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

    // Execute query
    $results = $query->get();

    // Prepare report data
    return [
        'result'     => $results,
        'report'     => $isIncome ? 'INCOME' : 'EXPENSES',
        'start_date' => $startDate->toDateString(),
        'end_date'   => $endDate->toDateString(),
        'sum'        => $results->sum('amount'),
        'cash'       => $results->where('account_number', '5')->sum('amount'),
        'bank'       => $results->where('account_number', '!=', '5')->sum('amount'),
    ];
}


    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\DailyReportMail;
use Illuminate\Support\Facades\Mail;

class SendDailyEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily email at 7:00 PM';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        Mail::to('feusebius1710@gmail.com')->send(new DailyReportMail());
        Mail::to('mazullacharles0@gmail.com')->send(new DailyReportMail());

        $this->info('Daily email sent at 7 PM.');
        // return Command::SUCCESS;
    }
}

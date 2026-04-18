<?php

namespace App\Http\Controllers;

use App\Jobs\SendMailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        return view('home');
    }

    function mail(){

        try {
            $mail_driver = setting('mail_drive');
            $mail_host = setting('mail_host');
            $mail_address = setting('mail_address');
            $from_name = setting('from_name');
            $mail_username = setting('mail_username');
            $mail_password = setting('mail_password');
            $mail_port = setting('mail_port');
            $encryption = setting('encryption');

            $setting = $mail_driver && $mail_host && $mail_address && $from_name && $mail_username  && $mail_password && $mail_port && $encryption;
            if (!$setting) {
                return;
            }



            $emailData['driver'] = $mail_driver;
            $emailData['reciver_email'] = 'onestdev125@gmail.com';
            $emailData['receiver_name'] = 'Test name';
            $emailData['sender_name'] = $from_name;
            $emailData['sender_email'] = $mail_address;
            $emailData['subject'] = "Test Subject Email";

            $body = "Hello We are greeting from Onest Tech";

            if(env('NOTIFICATION_JOB') == 'queue'){
                dispatch(new SendMailJob($body, $emailData));
            }else{
                dispatch(new SendMailJob($body, $emailData))->handle();
            }
        } catch (\Throwable $th) {
            dd($th);
        }

    }
}

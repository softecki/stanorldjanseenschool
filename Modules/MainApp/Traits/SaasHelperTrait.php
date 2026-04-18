<?php

namespace Modules\MainApp\Traits;

use App\Enums\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
trait SaasHelperTrait {

    protected $school;
    protected $subscription;
    
    protected function subscriptionUpdateInTenant($subscription, $sub_domain_key)
    {
        $this->subscription           = $subscription;
        $this->school->sub_domain_key = $sub_domain_key;


        // Switch to the main database connection
        config(['database.connections.mysql']);
        DB::reconnect('mysql');

        $db = config('tenancy.database.prefix') . $this->school->sub_domain_key;

        // // Define your dynamic database configuration
        $databaseConfig = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => $db,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ];


        // Set the configuration for the new connection
        Config::set('database.connections.dynamic_connection', $databaseConfig);
        \App\Models\Subscription::on('dynamic_connection')->update(['status' => 0]);

        \App\Models\Subscription::on('dynamic_connection')->create([
            'payment_type'  => @$this->subscription->package->payment_type,
            'name'          => @$this->subscription->package->name,
            'price'         => $this->subscription->price,
            'student_limit' => $this->subscription->student_limit,
            'staff_limit'   => $this->subscription->staff_limit,
            'expiry_date'   => $this->subscription->expiry_date ? date('Y-m-d', strtotime($this->subscription->expiry_date)) : null,
            'features_name' => $this->subscription->features_name,
            'features'      => $this->subscription->features,
            'trx_id'        => $this->subscription->trx_id,
            'method'        => $this->subscription->method,
            'status'        => Status::ACTIVE,
        ]);
    }
    
}
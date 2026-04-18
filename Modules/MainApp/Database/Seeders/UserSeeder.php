<?php

namespace Modules\MainApp\Database\Seeders;

use App\Models\Upload;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Modules\MainApp\Entities\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");
        Upload::create([
            'path'              => 'backend/uploads/users/user.png',
        ]);
        
        User::create([
            'name'              => 'Super Admin',
            'phone'             => '01811000000',
            'email'             => 'superadmin@onest.com',
            'email_verified_at' => now(),
            'password'          => Hash::make(123456),
            'remember_token'    => Str::random(10),
            'date_of_birth'     => '2022-09-07',
            'upload_id'         => 1,
        ]);
    }
}

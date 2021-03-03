<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'email' => 'admin@App\Modelsapp.com',
            'name' => 'Jane Doe',
            'role' => 'admin',
            'password' => Hash::make('crater@123')
        ]);

        Setting::setSetting('profile_complete', 0);
    }
}

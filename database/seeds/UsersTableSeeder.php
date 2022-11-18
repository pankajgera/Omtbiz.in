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
            'email' => 'testing@gmail',
            'name' => 'testing',
            'role' => 'admin',
            'password' => Hash::make('testing@123')
        ]);

        Setting::setSetting('profile_complete', 0);
    }
}

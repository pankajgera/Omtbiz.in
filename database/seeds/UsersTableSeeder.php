<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use App\Support\InitialAdminCredentials;
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
        $credentials = InitialAdminCredentials::fromConfig();

        User::create([
            'email' => $credentials['email'],
            'name' => $credentials['name'],
            'role' => 'admin',
            'password' => Hash::make($credentials['password']),
        ]);

        Setting::setSetting('profile_complete', 0);
    }
}

<?php

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Address::create([
            'name' => 'Down town',
            'address_street_1' => '12 house of street',
            'address_street_2' => '',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'zip' => '302001',
            'user_id' => 1,
            'phone' => '',
            'fax' => '',
            'type' => '',
        ]);
    }
}

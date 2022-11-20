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
            'country_id' => '101',
            'zip' => '302001',
            'phone' => '',
            'fax' => '',
            'type' => '',
            'user_id' => '1',
            'company_id' => 1,
        ]);
    }
}

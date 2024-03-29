<?php

use Database\Seeders\AccountGroupsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(EstimateTemplateSeeder::class);
        $this->call(InvoiceTemplateSeeder::class);
        $this->call(AddressSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(AccountGroupsSeeder::class);
    }
}

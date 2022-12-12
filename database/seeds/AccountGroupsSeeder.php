<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountGroup::truncate();

        DB::table('account_groups')->insert([
            ["id" => 1, "name" => "Bank Accounts", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 2, "name" => "Bank OCC A/C", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 3, "name" => "Bank OD A/C", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 4, "name" => "Branch/Divisions", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 5, "name" => "Capital Account", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 6, "name" => "Cash-in-Hand", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 7, "name" => "Current Assets", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 8, "name" => "Current Liabilities", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 9, "name" => "Deposits (Asset)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 10, "name" => "Direct Expenses", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 11, "name" => "Direct Incomes", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 12, "name" => "Duties & Taxes", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 13, "name" => "Expenses (Direct)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 14, "name" => "Expenses (Indirect)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 15, "name" => "Fixed Assets", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 16, "name" => "Income (Direct)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 17, "name" => "Income (Indirect)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 18, "name" => "Indirect Expenses", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 19, "name" => "Indirect Incomes", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 20, "name" => "Investments", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 21, "name" => "Loans & Advances (Asset)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 22, "name" => "Loans (Liability)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 23, "name" => "Misc. Expenses (Asset)", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 24, "name" => "Provisions", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 25, "name" => "Purchase Accounts", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 26, "name" => "Reserves & Surplus", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 27, "name" => "Retained Earnings", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 28, "name" => "Sales Accounts", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 29, "name" => "Secured Loans", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 30, "name" => "Stock-in-Hand", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 31, "name" => "Sundry Creditors", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 32, "name" => "Sundry Debtors", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
            ["id" => 33, "name" => "Suspense A/C", "created_at" => Carbon::now(), "updated_at" => Carbon::now()],
        ]);
    }
}

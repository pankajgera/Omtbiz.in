<?php

use App\Models\Address;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'name' => 'Local Company',
            'logo' => null,
            'unique_hash' => 'Rd3ZDw9IiawGQZDJAVRSsUNjNHtpskFGOeqhKN0f4uln8aIJSbNpC5k9Z4ff',
        ]);

        CompanySetting::create(['option' => 'notification_email', 'value' => 'testing@gmail.com', 'company_id' => 1]);
        CompanySetting::create(['option' => 'currency', 'value' => '12', 'company_id' => 1]);
        CompanySetting::create(['option' => 'time_zone', 'value' => 'UTC', 'company_id' => 1]);
        CompanySetting::create(['option' => 'language', 'value' => 'en', 'company_id' => 1]);
        CompanySetting::create(['option' => 'carbon_date_format', 'value' => 'd M Y', 'company_id' => 1]);
        CompanySetting::create(['option' => 'moment_date_format', 'value' => 'DD MMM YYYY', 'company_id' => 1]);
        CompanySetting::create(['option' => 'fiscal_year', 'value' => '3-2', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_auto_generate', 'value' => 'YES', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_prefix', 'value' => 'INV', 'company_id' => 1]);
        CompanySetting::create(['option' => 'estimate_prefix', 'value' => 'EST', 'company_id' => 1]);
        CompanySetting::create(['option' => 'receipt_prefix', 'value' => 'REC', 'company_id' => 1]);
        CompanySetting::create(['option' => 'estimate_auto_generate', 'value' => 'YES', 'company_id' => 1]);
        CompanySetting::create(['option' => 'receipt_auto_generate', 'value' => 'YES', 'company_id' => 1]);
        CompanySetting::create(['option' => 'payment_prefix', 'value' => 'PAY', 'company_id' => 1]);
        CompanySetting::create(['option' => 'payment_auto_generate', 'value' => 'YES', 'company_id' => 1]);
        CompanySetting::create(['option' => 'primary_text_color', 'value' => '#5851D8', 'company_id' => 1]);
        CompanySetting::create(['option' => 'heading_text_color', 'value' => '#595959', 'company_id' => 1]);
        CompanySetting::create(['option' => 'section_heading_text_color', 'value' => '#040405', 'company_id' => 1]);
        CompanySetting::create(['option' => 'border_color', 'value' => '#EAF1FB', 'company_id' => 1]);
        CompanySetting::create(['option' => 'body_text_color', 'value' => '#595959', 'company_id' => 1]);
        CompanySetting::create(['option' => 'footer_text_color', 'value' => '#595959', 'company_id' => 1]);
        CompanySetting::create(['option' => 'footer_total_color', 'value' => '#5851D8', 'company_id' => 1]);
        CompanySetting::create(['option' => 'footer_bg_color', 'value' => '#F9FBFF', 'company_id' => 1]);
        CompanySetting::create(['option' => 'date_text_color', 'value' => '#595959', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_primary_color', 'value' => '#5851D8', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_column_heading', 'value' => '#55547A', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_field_label', 'value' => '#55547A', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_field_value', 'value' => '#040405', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_body_text', 'value' => '#040405', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_description_text', 'value' => '#595959', 'company_id' => 1]);
        CompanySetting::create(['option' => 'invoice_border_color', 'value' => '#EAF1FB', 'company_id' => 1]);
        CompanySetting::create(['option' => 'allow_negative_inventory', 'value' => 'NO', 'company_id' => 1]);


        User::where('id', 1)->update([
            'company_id' => 1,
            'company_name' => 'Local Company',
        ]);

        Address::where('id', 1)->update([
            'country_id' => 101,
            'company_id' => 1,
        ]);
    }
}

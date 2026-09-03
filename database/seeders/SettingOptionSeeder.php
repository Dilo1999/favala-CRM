<?php

namespace Database\Seeders;

use App\Models\SettingOption;
use Illuminate\Database\Seeder;

class SettingOptionSeeder extends Seeder
{
    /** The nine configurable option lists and their current defaults (spec §7). */
    public function run(): void
    {
        $lists = [
            SettingOption::LEAD_SOURCE => ['Viber', 'WhatsApp', 'Facebook', 'Meeting', 'Call'],
            SettingOption::CUSTOMER_TYPE => ['Retail', 'B2B', 'Contractor'],
            SettingOption::LEAD_STATUS => ['New', 'Potential', 'Not Qualified', 'Customer'],
            SettingOption::PRODUCT_CATEGORY => [
                'Cement', 'Steel', 'Wood', 'Aggregates', 'Roofing',
                'Plumbing', 'Electrical', 'Paint', 'Tools', 'Safety Gear',
            ],
            SettingOption::TASK_TYPE => ['Call', 'Meeting', 'Email', 'Site Visit'],
            SettingOption::REQUEST_SOURCE => ['Call', 'WhatsApp', 'Facebook', 'Meeting', 'Viber'],
            SettingOption::ACTIVITY_OUTCOME => ['Interested', 'No Answer', 'Follow-up', 'Quotation Requested', 'Not Interested'],
            SettingOption::QUERY_SOURCE => ['Facebook', 'Viber', 'WhatsApp', 'Phone Call'],
            SettingOption::QUERY_TYPE => ['Information', 'Quotation Request', 'Price Check'],
        ];

        foreach ($lists as $group => $values) {
            foreach ($values as $index => $value) {
                SettingOption::updateOrCreate(
                    ['group' => $group, 'value' => $value],
                    ['sort_order' => $index]
                );
            }
        }
    }
}

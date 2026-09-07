<?php

return [
    // Business domain defaults (spec §2) — configurable per deployment via .env.
    'currency' => env('CRM_CURRENCY', 'MVR'),
    'gst_percent' => (float) env('CRM_GST_PERCENT', 8),
    'default_markup_percent' => (float) env('CRM_DEFAULT_MARKUP_PERCENT', 15),
    'document_expiry_days' => (int) env('CRM_DOCUMENT_EXPIRY_DAYS', 5),

    // Printed letterhead (quotations, invoices, delivery notes).
    'company' => [
        'name' => env('CRM_COMPANY_NAME', 'FAVALA'),
        'address_lines' => [
            env('CRM_COMPANY_ADDRESS_1', '2nd Floor, H.Hameedhee Manzil, Janavaree Magu'),
            env('CRM_COMPANY_ADDRESS_2', "Male', Maldives"),
        ],
        'phones' => array_filter(explode(',', env('CRM_COMPANY_PHONES', '+960 7661315'))),
        'emails' => array_filter(explode(',', env('CRM_COMPANY_EMAILS', 'wholesale@favala.mv'))),
        'gst_number' => env('CRM_COMPANY_GST_NUMBER', '1099603GST501'),
        'cheque_payable_to' => env('CRM_COMPANY_CHEQUE_PAYABLE_TO', 'Favala Decor'),
        'bank_accounts' => [
            'MVR' => env('CRM_COMPANY_BANK_MVR', '7770000053152'),
            'USD' => env('CRM_COMPANY_BANK_USD', '7770000053153'),
        ],
        'bank_name' => env('CRM_COMPANY_BANK_NAME', 'BML'),
    ],
];

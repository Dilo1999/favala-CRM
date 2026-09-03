<?php

return [
    // Business domain defaults (spec §2) — configurable per deployment via .env.
    'currency' => env('CRM_CURRENCY', 'MVR'),
    'gst_percent' => (float) env('CRM_GST_PERCENT', 8),
    'default_markup_percent' => (float) env('CRM_DEFAULT_MARKUP_PERCENT', 15),
    'document_expiry_days' => (int) env('CRM_DOCUMENT_EXPIRY_DAYS', 5),
];

<?php

return [
    'trader_name' => env('LEGAL_TRADER_NAME', 'VoidForgeStore'),
    'trader_address' => env('LEGAL_TRADER_ADDRESS', '13 Void Circuit, Sofia'),
    'trader_registration_number' => env('LEGAL_TRADER_REGISTRATION_NUMBER'),
    'trader_vat_number' => env('LEGAL_TRADER_VAT_NUMBER'),
    'support_email' => env('LEGAL_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@voidforgestore.com')),
    'privacy_email' => env('LEGAL_PRIVACY_EMAIL', env('LEGAL_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@voidforgestore.com'))),
    'complaints_email' => env('LEGAL_COMPLAINTS_EMAIL', env('LEGAL_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@voidforgestore.com'))),
    'support_phone' => env('LEGAL_SUPPORT_PHONE', '+359 2 555 0142'),
    'returns_window_days' => (int) env('LEGAL_RETURNS_WINDOW_DAYS', 14),
    'shipping_regions' => env('LEGAL_SHIPPING_REGIONS', 'European Union'),
    'dispatch_window' => env('LEGAL_DISPATCH_WINDOW', '1 to 3 business days after payment confirmation'),
    'refund_window' => env('LEGAL_REFUND_WINDOW', 'within 14 days after the returned goods are received and reviewed'),
];

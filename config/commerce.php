<?php

return [

    'version' => '1.0.0',

    'available_locales' => [
        'en' => 'English',
    ],

    'default_currency' => 'USD',

    'date_format' => 'Y-m-d',

    'pagination' => 25,

    /*
     | Third-party gateway module classes.
     | Each class must implement App\Gateways\Contracts\PaymentGatewayModule.
     | Built-in gateways (Stripe, Bank Transfer) are registered automatically.
     */
    'gateway_modules' => [
        // \MyVendor\MyGateway\MyGatewayModule::class,
    ],

    'billing' => [
        'invoice_prefix'       => 'INV-',
        'invoice_yearly_reset' => true,
        'invoice_due_days'     => 7,
        'invoice_advance_days' => 7,  // generate renewal invoice this many days before next_due_date
        'grace_period_days'    => 5,
        'auto_close_days'      => 14,
    ],

];

<?php

return [

    'version' => '1.0.0',

    'available_locales' => [
        'en' => 'English',
    ],

    'default_currency' => 'USD',

    'date_format' => 'Y-m-d',

    'pagination' => 25,

    'billing' => [
        'invoice_prefix'      => 'INV-',
        'invoice_yearly_reset' => true,
        'invoice_due_days'    => 7,
        'grace_period_days'   => 5,
        'auto_close_days'     => 5,
    ],

];

<?php

return [
    'settings' => 'Settings',
    'saved' => 'Settings saved successfully.',

    // Categories
    'company' => 'Company',
    'branding' => 'Branding',
    'currencies' => 'Currencies',

    // Company
    'company_name' => 'Company Name',
    'company_address' => 'Address',
    'company_city' => 'City',
    'company_state' => 'State / Province',
    'company_postcode' => 'Postcode / ZIP',
    'company_country' => 'Country',
    'company_phone' => 'Phone',
    'company_email' => 'Email',
    'company_tax_id' => 'Tax / VAT ID',
    'company_website' => 'Website',

    // Branding
    'brand_name' => 'Portal Name',
    'brand_name_help' => 'Shown in the client portal header and emails.',
    'brand_logo' => 'Logo',
    'brand_logo_help' => 'Recommended: 200x50px PNG or SVG.',
    'brand_favicon' => 'Favicon',
    'brand_primary_color' => 'Primary Color',

    // Currencies
    'currency_code' => 'Code',
    'currency_name' => 'Name',
    'currency_symbol' => 'Symbol',
    'currency_prefix' => 'Prefix',
    'currency_suffix' => 'Suffix',
    'currency_decimals' => 'Decimal Places',
    'currency_exchange_rate' => 'Exchange Rate',
    'currency_default' => 'Default Currency',
    'currency_active' => 'Active',
    'add_currency' => 'Add Currency',
    'edit_currency' => 'Edit Currency',
    'currency_created' => 'Currency added successfully.',
    'currency_updated' => 'Currency updated successfully.',
    'currency_deleted' => 'Currency deleted successfully.',
    'cannot_delete_default' => 'Cannot delete the default currency.',

    // Navigation
    'nav_company'    => 'Company Info',
    'nav_branding'   => 'Branding',
    'nav_portal'     => 'Client Portal',
    'nav_currencies' => 'Currencies',
    'nav_billing'    => 'Billing',
    'nav_tickets'    => 'Tickets',
    'nav_registrar'  => 'Registrar',

    // Portal builder
    'portal_appearance'              => 'Appearance',
    'portal_hero_title'              => 'Hero Title',
    'portal_hero_title_help'         => 'Main headline shown in the portal hero section.',
    'portal_hero_subtitle'           => 'Hero Subtitle',
    'portal_hero_subtitle_help'      => 'Supporting text shown below the headline.',
    'portal_primary_color'           => 'Accent Color',
    'portal_primary_color_help'      => 'Used for buttons and highlighted elements across the portal.',
    'portal_navigation'              => 'Navigation Links',
    'portal_nav_links_help'          => 'Add custom links to the portal navigation bar and footer.',
    'portal_nav_label'               => 'Label',
    'portal_nav_url'                 => 'URL',
    'portal_nav_new_tab'             => 'New tab',
    'portal_nav_empty'               => 'No links added yet.',
    'portal_nav_add'                 => 'Add Link',
    'portal_sections'                => 'Sections',
    'portal_sections_help'           => 'Choose which sections are visible on the portal homepage.',
    'portal_show_hero'               => 'Hero Section',
    'portal_show_hero_help'          => 'Display the headline and call-to-action at the top of the page.',
    'portal_show_products'           => 'Products Catalog',
    'portal_show_products_help'      => 'Display available hosting plans and services.',
    'portal_show_domain_search'      => 'Domain Search',
    'portal_show_domain_search_help' => 'Show a domain availability search bar.',

    // Billing
    'billing'                    => 'Billing',
    'invoice_settings'           => 'Invoice Settings',
    'invoice_prefix'             => 'Invoice Prefix',
    'invoice_prefix_hint'        => 'e.g. INV-',
    'invoice_yearly_reset'       => 'Yearly Number Reset',
    'invoice_yearly_reset_hint'  => 'Reset invoice counter each year',
    'invoice_due_days'           => 'Invoice Due Days',
    'invoice_due_days_hint'      => 'Days until invoice is due after generation',
    'grace_period_days'          => 'Grace Period (days)',
    'grace_period_days_hint'     => 'Days after due date before suspension',
    'auto_close_days'            => 'Auto-close After (days)',
    'auto_close_days_hint'       => 'Auto-close answered tickets with no reply after this many days',

    // Payment gateways
    'payment_gateways'           => 'Payment Gateways',
    'enable_gateway'             => 'Enable :name',
    'not_configured'             => 'Not Configured',
    'stripe_webhook_url'         => 'Stripe webhook URL',
];

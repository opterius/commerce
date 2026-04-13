<?php

return [
    // Page titles / headings
    'staff_members'   => 'Staff Members',
    'add_staff'       => 'Add Staff Member',
    'edit_staff'      => 'Edit Staff Member',
    'profile'         => 'Profile',

    // Roles
    'role'            => 'Role',
    'role_super_admin'=> 'Super Admin',
    'role_admin'      => 'Admin',
    'role_support'    => 'Support',
    'role_billing'    => 'Billing',

    // Table
    'permissions_count' => 'Permissions',
    'last_login'      => 'Last Login',
    'all_permissions' => 'All (super admin)',
    'active_account'  => 'Active account',

    // Permissions section
    'permissions'     => 'Permissions',
    'load_preset'     => 'Load role preset',
    'super_admin_note'=> 'Super admins bypass all permission checks and have full access.',
    'super_admin_all_access' => 'Super admins have unrestricted access to all areas. No individual permission settings apply.',

    // Permission area labels
    'area_clients'    => 'Clients',
    'area_invoices'   => 'Invoices',
    'area_orders'     => 'Orders',
    'area_services'   => 'Services',
    'area_domains'    => 'Domains',
    'area_tickets'    => 'Support Tickets',
    'area_products'   => 'Products',
    'area_servers'    => 'Servers',
    'area_reports'    => 'Reports',
    'area_settings'   => 'Settings',
    'area_staff'      => 'Staff Management',

    // CRUD messages
    'created'         => ':name has been added.',
    'updated'         => ':name has been updated.',
    'deleted'         => 'Staff member deleted.',
    'create_account'  => 'Create Account',

    // Delete confirm
    'delete_confirm_title' => 'Delete Staff Member',
    'delete_confirm_body'  => 'Are you sure you want to delete :name? This cannot be undone.',

    // Guard errors
    'last_super_admin'   => 'You cannot change or remove the last super admin account.',
    'cannot_delete_self' => 'You cannot delete your own account.',
];

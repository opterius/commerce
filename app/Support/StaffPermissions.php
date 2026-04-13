<?php

namespace App\Support;

/**
 * Canonical registry of all staff permission slugs.
 *
 * Format: {area}.{action}
 *
 * Usage:
 *   StaffPermissions::all()              — all slugs
 *   StaffPermissions::grouped()          — grouped by area for UI rendering
 *   StaffPermissions::forRole('support') — default slugs for a role preset
 */
class StaffPermissions
{
    /**
     * All permission slugs, grouped by area.
     * Order here determines order in the admin UI.
     */
    public const GROUPS = [
        'clients' => [
            'clients.view'    => 'View clients',
            'clients.create'  => 'Create clients',
            'clients.edit'    => 'Edit clients',
            'clients.delete'  => 'Delete clients',
            'clients.notes'   => 'Manage notes',
            'clients.contacts'=> 'Manage contacts',
            'clients.impersonate' => 'Login as client',
        ],
        'invoices' => [
            'invoices.view'           => 'View invoices',
            'invoices.create'         => 'Create invoices',
            'invoices.edit'           => 'Edit invoices',
            'invoices.void'           => 'Void invoices',
            'invoices.record-payment' => 'Record manual payment',
            'invoices.refund'         => 'Issue refunds',
            'invoices.apply-credit'   => 'Apply credit',
        ],
        'orders' => [
            'orders.view'   => 'View orders',
            'orders.manage' => 'Manage orders (approve, reject, cancel)',
        ],
        'services' => [
            'services.view'      => 'View services',
            'services.manage'    => 'Manage services (suspend, unsuspend, provision)',
            'services.terminate' => 'Terminate services',
        ],
        'domains' => [
            'domains.view'   => 'View domains',
            'domains.manage' => 'Manage domains',
        ],
        'tickets' => [
            'tickets.view'   => 'View tickets',
            'tickets.reply'  => 'Reply to tickets',
            'tickets.assign' => 'Assign & transfer tickets',
            'tickets.delete' => 'Delete tickets',
        ],
        'products' => [
            'products.view'   => 'View products & pricing',
            'products.manage' => 'Manage products, groups & options',
        ],
        'servers' => [
            'servers.view'   => 'View servers & groups',
            'servers.manage' => 'Manage servers & groups',
        ],
        'reports' => [
            'reports.view' => 'View reports & activity logs',
        ],
        'settings' => [
            'settings.view'   => 'View settings',
            'settings.manage' => 'Manage settings',
        ],
        'staff' => [
            'staff.view'   => 'View staff members',
            'staff.manage' => 'Create, edit & delete staff',
        ],
    ];

    /**
     * Role presets — define the default permission set for each role.
     * super_admin is not listed; it bypasses all checks in the Gate.
     */
    public const PRESETS = [
        'admin' => [
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'clients.notes', 'clients.contacts', 'clients.impersonate',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.void',
            'invoices.record-payment', 'invoices.refund', 'invoices.apply-credit',
            'orders.view', 'orders.manage',
            'services.view', 'services.manage', 'services.terminate',
            'domains.view', 'domains.manage',
            'tickets.view', 'tickets.reply', 'tickets.assign', 'tickets.delete',
            'products.view', 'products.manage',
            'servers.view', 'servers.manage',
            'reports.view',
            'settings.view', 'settings.manage',
            'staff.view', 'staff.manage',
        ],
        'support' => [
            'clients.view', 'clients.notes', 'clients.contacts',
            'invoices.view',
            'orders.view',
            'services.view',
            'domains.view',
            'tickets.view', 'tickets.reply', 'tickets.assign',
            'reports.view',
        ],
        'billing' => [
            'clients.view', 'clients.edit', 'clients.notes',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.void',
            'invoices.record-payment', 'invoices.refund', 'invoices.apply-credit',
            'orders.view', 'orders.manage',
            'services.view',
            'domains.view',
            'reports.view',
        ],
    ];

    /** Returns all permission slugs as a flat array. */
    public static function all(): array
    {
        return array_keys(array_merge(...array_values(self::GROUPS)));
    }

    /** Returns the GROUPS array (for UI rendering). */
    public static function grouped(): array
    {
        return self::GROUPS;
    }

    /** Returns the default permission slugs for a given role. */
    public static function forRole(string $role): array
    {
        return self::PRESETS[$role] ?? [];
    }
}

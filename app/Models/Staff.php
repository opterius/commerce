<?php

namespace App\Models;

use App\Support\StaffPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'locale',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'password'               => 'hashed',
            'permissions'            => 'array',
            'is_active'              => 'boolean',
            'last_login_at'          => 'datetime',
            'two_factor_confirmed_at'=> 'datetime',
        ];
    }

    // ── Permission checks ─────────────────────────────────────────────────────

    /**
     * super_admin bypasses all permission checks.
     * Everyone else is checked against the stored permissions array.
     * If permissions is null (legacy record), fall back to role preset.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        $granted = $this->permissions ?? StaffPermissions::forRole($this->role);

        return in_array($permission, $granted, true);
    }

    /**
     * Grant a single permission (persists on save).
     */
    public function grantPermission(string $permission): static
    {
        $current = $this->permissions ?? StaffPermissions::forRole($this->role);

        if (! in_array($permission, $current, true)) {
            $current[] = $permission;
        }

        $this->permissions = array_values($current);

        return $this;
    }

    /**
     * Revoke a single permission (persists on save).
     */
    public function revokePermission(string $permission): static
    {
        $current = $this->permissions ?? StaffPermissions::forRole($this->role);

        $this->permissions = array_values(array_filter(
            $current,
            fn ($p) => $p !== $permission,
        ));

        return $this;
    }

    /**
     * Replace all permissions with a fresh role preset.
     * Call ->save() afterwards.
     */
    public function applyRolePreset(string $role): static
    {
        $this->role        = $role;
        $this->permissions = StaffPermissions::forRole($role);

        return $this;
    }

    // ── Legacy role helpers (kept for any existing usage) ─────────────────────

    public function isSuper(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }
}

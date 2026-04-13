<?php

namespace App\Http\Controllers\Admin;

use App\Models\Staff;
use App\Support\StaffPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffController extends AdminController
{
    public function index()
    {
        $this->authorize('staff.view');

        $staffMembers = Staff::orderBy('name')->get();

        return view('admin.staff.index', compact('staffMembers'));
    }

    public function create()
    {
        $this->authorize('staff.manage');

        $grouped = StaffPermissions::grouped();
        $presets = array_keys(StaffPermissions::PRESETS);
        $staff   = null;

        return view('admin.staff.form', compact('grouped', 'presets', 'staff'));
    }

    public function store(Request $request)
    {
        $this->authorize('staff.manage');

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:staff,email'],
            'password' => ['required', Password::min(8)],
            'role'     => ['required', Rule::in(['super_admin', 'admin', 'support', 'billing'])],
            'is_active'=> ['boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(StaffPermissions::all())],
        ]);

        $staff = Staff::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'is_active'   => $request->boolean('is_active', true),
            'permissions' => $data['role'] === 'super_admin'
                ? []
                : ($data['permissions'] ?? StaffPermissions::forRole($data['role'])),
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', __('staff.created', ['name' => $staff->name]));
    }

    public function edit(Staff $staff)
    {
        $this->authorize('staff.manage');

        $grouped = StaffPermissions::grouped();
        $presets = array_keys(StaffPermissions::PRESETS);

        // Resolve effective permissions for display
        $effective = $staff->permissions ?? StaffPermissions::forRole($staff->role);

        return view('admin.staff.form', compact('staff', 'grouped', 'presets', 'effective'));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorize('staff.manage');

        // Prevent the last super_admin from losing their role
        if ($staff->role === 'super_admin' && $request->input('role') !== 'super_admin') {
            $superCount = Staff::where('role', 'super_admin')->count();
            if ($superCount <= 1) {
                return back()->with('error', __('staff.last_super_admin'));
            }
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('staff')->ignore($staff->id)],
            'password' => ['nullable', Password::min(8)],
            'role'     => ['required', Rule::in(['super_admin', 'admin', 'support', 'billing'])],
            'is_active'=> ['boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(StaffPermissions::all())],
        ]);

        $staff->name      = $data['name'];
        $staff->email     = $data['email'];
        $staff->role      = $data['role'];
        $staff->is_active = $request->boolean('is_active', true);

        if (! empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }

        $staff->permissions = $data['role'] === 'super_admin'
            ? []
            : ($data['permissions'] ?? []);

        $staff->save();

        return redirect()->route('admin.staff.index')
            ->with('success', __('staff.updated', ['name' => $staff->name]));
    }

    public function destroy(Staff $staff)
    {
        $this->authorize('staff.manage');

        // Prevent deleting yourself
        if ($staff->id === auth('staff')->id()) {
            return back()->with('error', __('staff.cannot_delete_self'));
        }

        // Prevent deleting the last super_admin
        if ($staff->role === 'super_admin') {
            $superCount = Staff::where('role', 'super_admin')->count();
            if ($superCount <= 1) {
                return back()->with('error', __('staff.last_super_admin'));
            }
        }

        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', __('staff.deleted'));
    }
}

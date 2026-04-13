<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketDepartment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TicketDepartmentController extends Controller
{
    public function index()
    {
        $departments = TicketDepartment::withCount('tickets')->orderBy('sort_order')->get();

        return view('admin.ticket-departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.ticket-departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $dept = TicketDepartment::create($data);

        ActivityLogger::log('ticket_department.created', 'ticket_department', $dept->id, $dept->name, null);

        return redirect()->route('admin.ticket-departments.index')
            ->with('success', "Department \"{$dept->name}\" created.");
    }

    public function edit(TicketDepartment $ticketDepartment)
    {
        return view('admin.ticket-departments.edit', compact('ticketDepartment'));
    }

    public function update(Request $request, TicketDepartment $ticketDepartment)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $ticketDepartment->update($data);

        ActivityLogger::log('ticket_department.updated', 'ticket_department', $ticketDepartment->id, $ticketDepartment->name, null);

        return redirect()->route('admin.ticket-departments.index')
            ->with('success', "Department \"{$ticketDepartment->name}\" updated.");
    }

    public function destroy(TicketDepartment $ticketDepartment)
    {
        $name = $ticketDepartment->name;
        $ticketDepartment->delete();

        ActivityLogger::log('ticket_department.deleted', 'ticket_department', $ticketDepartment->id, $name, null);

        return redirect()->route('admin.ticket-departments.index')
            ->with('success', "Department \"{$name}\" deleted.");
    }
}

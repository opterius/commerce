<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use App\Models\TicketDepartment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CannedResponseController extends Controller
{
    public function index()
    {
        $responses = CannedResponse::with('department', 'staff')->orderBy('title')->get();

        return view('admin.canned-responses.index', compact('responses'));
    }

    public function create()
    {
        $departments = TicketDepartment::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.canned-responses.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string',
            'department_id' => 'nullable|exists:ticket_departments,id',
        ]);

        $data['staff_id'] = auth('staff')->id();

        $response = CannedResponse::create($data);

        ActivityLogger::log('canned_response.created', 'canned_response', $response->id, $response->title, null);

        return redirect()->route('admin.canned-responses.index')
            ->with('success', "Canned response \"{$response->title}\" created.");
    }

    public function edit(CannedResponse $cannedResponse)
    {
        $departments = TicketDepartment::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.canned-responses.edit', compact('cannedResponse', 'departments'));
    }

    public function update(Request $request, CannedResponse $cannedResponse)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string',
            'department_id' => 'nullable|exists:ticket_departments,id',
        ]);

        $cannedResponse->update($data);

        ActivityLogger::log('canned_response.updated', 'canned_response', $cannedResponse->id, $cannedResponse->title, null);

        return redirect()->route('admin.canned-responses.index')
            ->with('success', "Canned response \"{$cannedResponse->title}\" updated.");
    }

    public function destroy(CannedResponse $cannedResponse)
    {
        $title = $cannedResponse->title;
        $cannedResponse->delete();

        ActivityLogger::log('canned_response.deleted', 'canned_response', $cannedResponse->id, $title, null);

        return redirect()->route('admin.canned-responses.index')
            ->with('success', "Canned response \"{$title}\" deleted.");
    }
}

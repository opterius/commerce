<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServerGroup;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ServerGroupController extends Controller
{
    public function index()
    {
        $groups = ServerGroup::withCount('servers')->orderBy('name')->get();

        return view('admin.server-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.server-groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $group = ServerGroup::create($data);

        ActivityLogger::log('server_group.created', 'server_group', $group->id, $group->name, null);

        return redirect()->route('admin.server-groups.index')
            ->with('success', "Server group \"{$group->name}\" created.");
    }

    public function edit(ServerGroup $serverGroup)
    {
        return view('admin.server-groups.edit', compact('serverGroup'));
    }

    public function update(Request $request, ServerGroup $serverGroup)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $serverGroup->update($data);

        ActivityLogger::log('server_group.updated', 'server_group', $serverGroup->id, $serverGroup->name, null);

        return redirect()->route('admin.server-groups.index')
            ->with('success', "Server group \"{$serverGroup->name}\" updated.");
    }

    public function destroy(Request $request, ServerGroup $serverGroup)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $name = $serverGroup->name;
        $serverGroup->delete();

        ActivityLogger::log('server_group.deleted', 'server_group', $serverGroup->id, $name, null);

        return redirect()->route('admin.server-groups.index')
            ->with('success', "Server group \"{$name}\" deleted.");
    }
}

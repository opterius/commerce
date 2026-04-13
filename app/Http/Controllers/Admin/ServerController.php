<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Provisioning\Modules\OpteriusPanelModule;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        $servers = Server::with('serverGroup')->orderBy('name')->get();

        return view('admin.servers.index', compact('servers'));
    }

    public function create()
    {
        $serverGroups = ServerGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.servers.create', compact('serverGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'server_group_id' => 'nullable|exists:server_groups,id',
            'name'            => 'required|string|max:255',
            'hostname'        => 'required|string|max:255',
            'ip_address'      => 'nullable|string|max:45',
            'api_url'         => 'required|url|max:500',
            'api_token'       => 'required|string|max:500',
            'max_accounts'    => 'required|integer|min:0',
            'ns1'             => 'nullable|string|max:255',
            'ns2'             => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $server = Server::create($data);

        ActivityLogger::log('server.created', 'server', $server->id, $server->name, null);

        return redirect()->route('admin.servers.index')
            ->with('success', "Server \"{$server->name}\" created.");
    }

    public function edit(Server $server)
    {
        $serverGroups = ServerGroup::where('is_active', true)->orderBy('name')->get();

        return view('admin.servers.edit', compact('server', 'serverGroups'));
    }

    public function update(Request $request, Server $server)
    {
        $data = $request->validate([
            'server_group_id' => 'nullable|exists:server_groups,id',
            'name'            => 'required|string|max:255',
            'hostname'        => 'required|string|max:255',
            'ip_address'      => 'nullable|string|max:45',
            'api_url'         => 'required|url|max:500',
            'api_token'       => 'nullable|string|max:500',
            'max_accounts'    => 'required|integer|min:0',
            'ns1'             => 'nullable|string|max:255',
            'ns2'             => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Don't overwrite token if left blank
        if (empty($data['api_token'])) {
            unset($data['api_token']);
        }

        $server->update($data);

        ActivityLogger::log('server.updated', 'server', $server->id, $server->name, null);

        return redirect()->route('admin.servers.index')
            ->with('success', "Server \"{$server->name}\" updated.");
    }

    public function destroy(Server $server)
    {
        $name = $server->name;
        $server->delete();

        ActivityLogger::log('server.deleted', 'server', $server->id, $name, null);

        return redirect()->route('admin.servers.index')
            ->with('success', "Server \"{$name}\" deleted.");
    }

    public function testConnection(Server $server)
    {
        $module = app(OpteriusPanelModule::class);
        $result = $module->testConnection($server);

        if ($result->success) {
            return back()->with('success', "Connection to \"{$server->name}\" successful.");
        }

        return back()->with('error', "Connection failed: {$result->error}");
    }
}

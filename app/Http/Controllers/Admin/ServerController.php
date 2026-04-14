<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerGroup;
use App\Provisioning\ProvisioningModuleRegistry;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function __construct(private ProvisioningModuleRegistry $registry) {}

    public function index()
    {
        $servers = Server::with('serverGroup')->orderBy('name')->get();

        return view('admin.servers.index', compact('servers'));
    }

    public function create()
    {
        $serverGroups = ServerGroup::where('is_active', true)->orderBy('name')->get();
        $modules      = $this->registry->all();

        return view('admin.servers.create', compact('serverGroups', 'modules'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'opterius');

        $data = $request->validate([
            'server_group_id' => 'nullable|exists:server_groups,id',
            'type'            => 'required|string',
            'name'            => 'required|string|max:255',
            'hostname'        => 'required|string|max:255',
            'ip_address'      => 'nullable|string|max:45',
            'credentials'     => 'nullable|array',
            'credentials.*'   => 'nullable|string|max:1000',
            'max_accounts'    => 'required|integer|min:0',
            'ns1'             => 'nullable|string|max:255',
            'ns2'             => 'nullable|string|max:255',
            'ns3'             => 'nullable|string|max:255',
            'ns4'             => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        $data['is_active']    = $request->boolean('is_active', true);
        $data['credentials']  = $request->input('credentials', []);

        $server = Server::create($data);

        ActivityLogger::log('server.created', 'server', $server->id, $server->name, null);

        return redirect()->route('admin.servers.index')
            ->with('success', "Server \"{$server->name}\" created.");
    }

    public function edit(Server $server)
    {
        $serverGroups = ServerGroup::where('is_active', true)->orderBy('name')->get();
        $modules      = $this->registry->all();

        return view('admin.servers.edit', compact('server', 'serverGroups', 'modules'));
    }

    public function update(Request $request, Server $server)
    {
        $data = $request->validate([
            'server_group_id' => 'nullable|exists:server_groups,id',
            'type'            => 'required|string',
            'name'            => 'required|string|max:255',
            'hostname'        => 'required|string|max:255',
            'ip_address'      => 'nullable|string|max:45',
            'credentials'     => 'nullable|array',
            'credentials.*'   => 'nullable|string|max:1000',
            'max_accounts'    => 'required|integer|min:0',
            'ns1'             => 'nullable|string|max:255',
            'ns2'             => 'nullable|string|max:255',
            'ns3'             => 'nullable|string|max:255',
            'ns4'             => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // For secret fields (type=password), keep existing value if left blank
        $submitted   = $request->input('credentials', []);
        $existing    = $server->credentials ?? [];
        $moduleId    = $data['type'];
        $moduleFields = $this->registry->has($moduleId)
            ? collect($this->registry->all()[$moduleId]['fields'])
            : collect();

        foreach ($moduleFields as $field) {
            $key = $field['name'];
            if (($field['secret'] ?? false) && empty($submitted[$key])) {
                $submitted[$key] = $existing[$key] ?? '';
            }
        }

        $data['credentials'] = $submitted;

        $server->update($data);

        ActivityLogger::log('server.updated', 'server', $server->id, $server->name, null);

        return redirect()->route('admin.servers.index')
            ->with('success', "Server \"{$server->name}\" updated.");
    }

    public function destroy(Request $request, Server $server)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $name = $server->name;
        $server->delete();

        ActivityLogger::log('server.deleted', 'server', $server->id, $name, null);

        return redirect()->route('admin.servers.index')
            ->with('success', "Server \"{$name}\" deleted.");
    }

    public function testConnection(Server $server)
    {
        if (! $this->registry->has($server->type)) {
            return back()->with('error', "Unknown server type \"{$server->type}\".");
        }

        $module = $this->registry->resolve($server->type);
        $result = $module->testConnection($server);

        if ($result->success) {
            return back()->with('success', "Connection to \"{$server->name}\" successful.");
        }

        return back()->with('error', "Connection failed: {$result->error}");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncDomainJob;
use App\Models\Client;
use App\Models\Domain;
use App\Models\DomainTld;
use App\Services\ActivityLogger;
use App\Services\RegistrarService;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $query = Domain::with('client')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tld')) {
            $query->where('tld', $request->tld);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where('domain_name', 'like', '%' . $request->search . '%');
        }

        $domains  = $query->paginate(25)->withQueryString();
        $tlds     = DomainTld::orderBy('tld')->pluck('tld');
        $clients  = Client::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'company_name']);
        $statuses = Domain::STATUSES;

        return view('admin.domains.index', compact('domains', 'tlds', 'clients', 'statuses'));
    }

    public function show(Domain $domain)
    {
        $domain->loadMissing(['client', 'contacts']);

        return view('admin.domains.show', compact('domain'));
    }

    public function updateNameservers(Request $request, Domain $domain)
    {
        $data = $request->validate([
            'ns1' => 'nullable|string|max:255',
            'ns2' => 'nullable|string|max:255',
            'ns3' => 'nullable|string|max:255',
            'ns4' => 'nullable|string|max:255',
        ]);

        $nameservers = array_values(array_filter($data));

        $result = RegistrarService::module()->updateNameservers($domain, $nameservers);

        if ($result->success) {
            $domain->update(array_merge($data, ['ns1' => $data['ns1'], 'ns2' => $data['ns2'], 'ns3' => $data['ns3'], 'ns4' => $data['ns4']]));
            ActivityLogger::log('domain.nameservers_updated', 'domain', $domain->id, $domain->domain_name, null);
            return back()->with('success', 'Nameservers updated.');
        }

        return back()->with('error', 'Failed to update nameservers: ' . $result->error);
    }

    public function togglePrivacy(Request $request, Domain $domain)
    {
        $enable = ! $domain->whois_privacy;
        $result = RegistrarService::module()->setPrivacy($domain, $enable);

        if ($result->success) {
            $domain->update(['whois_privacy' => $enable]);
            ActivityLogger::log('domain.privacy_toggled', 'domain', $domain->id, $domain->domain_name, $enable ? 'Enabled' : 'Disabled');
            return back()->with('success', 'WHOIS privacy ' . ($enable ? 'enabled' : 'disabled') . '.');
        }

        return back()->with('error', 'Failed: ' . $result->error);
    }

    public function toggleLock(Request $request, Domain $domain)
    {
        $lock   = ! $domain->is_locked;
        $result = RegistrarService::module()->setLock($domain, $lock);

        if ($result->success) {
            $domain->update(['is_locked' => $lock]);
            ActivityLogger::log('domain.lock_toggled', 'domain', $domain->id, $domain->domain_name, $lock ? 'Locked' : 'Unlocked');
            return back()->with('success', 'Domain ' . ($lock ? 'locked' : 'unlocked') . '.');
        }

        return back()->with('error', 'Failed: ' . $result->error);
    }

    public function getEppCode(Domain $domain)
    {
        $result = RegistrarService::module()->getEppCode($domain);

        if ($result->success) {
            ActivityLogger::log('domain.epp_revealed', 'domain', $domain->id, $domain->domain_name, null);
            return back()->with('epp_code', $result->data['epp_code'] ?? '');
        }

        return back()->with('error', 'Failed: ' . $result->error);
    }

    public function sync(Domain $domain)
    {
        SyncDomainJob::dispatch($domain->id);
        ActivityLogger::log('domain.sync_queued', 'domain', $domain->id, $domain->domain_name, null);

        return back()->with('success', 'Sync queued.');
    }

    public function testConnection()
    {
        $result = RegistrarService::module()->testConnection();

        if ($result->success) {
            return back()->with('success', 'Connection successful.');
        }

        return back()->with('error', 'Connection failed: ' . $result->error);
    }

    public function updateNotes(Request $request, Domain $domain)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $domain->update($data);

        return back()->with('success', 'Notes saved.');
    }
}

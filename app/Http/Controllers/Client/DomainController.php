<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Domain;
use App\Models\DomainContact;
use App\Models\DomainTld;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\ActivityLogger;
use App\Services\InvoiceService;
use App\Services\RegistrarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DomainController extends Controller
{
    private function client(): Client
    {
        return auth('client')->user();
    }

    // ------------------------------------------------------------------ //
    //  Search
    // ------------------------------------------------------------------ //

    public function search(Request $request)
    {
        $tlds    = DomainTld::active()->orderBy('sort_order')->get();
        $results = [];
        $sld     = null;

        if ($request->filled('domain')) {
            $raw = strtolower(trim($request->domain));
            // Strip any TLD the user may have typed
            $sld = explode('.', $raw)[0];

            $module     = RegistrarService::module();
            $tldList    = $tlds->pluck('tld')->toArray();
            $apiResults = $module->checkBulkAvailability($sld, $tldList);

            foreach ($tlds as $tld) {
                $check       = $apiResults[$tld->tld] ?? null;
                $results[]   = [
                    'tld'         => $tld,
                    'domain_name' => "{$sld}.{$tld->tld}",
                    'available'   => $check?->available ?? false,
                    'error'       => $check?->error ?? '',
                ];
            }
        }

        return view('client.domains.search', compact('tlds', 'results', 'sld'));
    }

    // ------------------------------------------------------------------ //
    //  My Domains
    // ------------------------------------------------------------------ //

    public function index()
    {
        $domains = Domain::where('client_id', $this->client()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('client.domains.index', compact('domains'));
    }

    // ------------------------------------------------------------------ //
    //  Register flow
    // ------------------------------------------------------------------ //

    public function register(Request $request)
    {
        $tldSlug = ltrim(strtolower($request->query('tld', '')), '.');
        $sld     = strtolower($request->query('domain', ''));
        $tld     = DomainTld::where('tld', $tldSlug)->where('is_active', true)->firstOrFail();
        $client  = $this->client();

        return view('client.domains.register', compact('tld', 'sld', 'client'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sld'                 => 'required|string|max:63',
            'tld'                 => 'required|string|max:30',
            'billing_cycle'       => 'required|in:1year,2year,3year,5year,10year',
            'whois_privacy'       => 'boolean',
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'company'             => 'nullable|string|max:200',
            'email'               => 'required|email|max:255',
            'phone'               => 'required|string|max:30',
            'address_1'           => 'required|string|max:255',
            'address_2'           => 'nullable|string|max:255',
            'city'                => 'required|string|max:100',
            'state'               => 'nullable|string|max:100',
            'postcode'            => 'required|string|max:20',
            'country_code'        => 'required|string|size:2',
        ]);

        $tld    = DomainTld::where('tld', $data['tld'])->where('is_active', true)->firstOrFail();
        $client = $this->client();

        $domainName  = strtolower($data['sld']) . '.' . $tld->tld;
        $years       = (int) str_replace('year', '', $data['billing_cycle']);
        $amount      = $tld->register_price * $years;
        $dueDays     = (int) \App\Models\Setting::get('invoice_due_days', 7);

        DB::transaction(function () use ($data, $tld, $client, $domainName, $years, $amount, $dueDays) {
            $domain = Domain::create([
                'client_id'       => $client->id,
                'domain_name'     => $domainName,
                'tld'             => $tld->tld,
                'status'          => 'pending',
                'registrar_module'=> 'resellerclub',
                'billing_cycle'   => $data['billing_cycle'],
                'amount'          => $amount,
                'currency_code'   => $tld->currency_code,
                'whois_privacy'   => $data['whois_privacy'] ?? false,
                'auto_renew'      => true,
                'is_locked'       => true,
            ]);

            // Create registrant contact (used as all four roles)
            DomainContact::create([
                'domain_id'    => $domain->id,
                'type'         => 'registrant',
                'first_name'   => $data['first_name'],
                'last_name'    => $data['last_name'],
                'company'      => $data['company'] ?? null,
                'email'        => $data['email'],
                'phone'        => $data['phone'],
                'address_1'    => $data['address_1'],
                'address_2'    => $data['address_2'] ?? null,
                'city'         => $data['city'],
                'state'        => $data['state'] ?? null,
                'postcode'     => $data['postcode'],
                'country_code' => $data['country_code'],
            ]);

            // Invoice
            $invoice = Invoice::create([
                'client_id'      => $client->id,
                'invoice_number' => (new InvoiceService())->generateInvoiceNumber(),
                'status'         => 'unpaid',
                'due_date'       => now()->addDays($dueDays)->toDateString(),
                'currency_code'  => $tld->currency_code,
                'subtotal'       => $amount,
                'tax'            => 0,
                'total'          => $amount,
                'credit_applied' => 0,
            ]);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'domain_id'   => $domain->id,
                'description' => "Domain Registration: {$domainName} ({$years} year" . ($years > 1 ? 's' : '') . ')',
                'amount'      => $amount,
                'tax_amount'  => 0,
                'quantity'    => 1,
            ]);

            $domain->update(['order_id' => null]);

            ActivityLogger::log('domain.registration_initiated', 'domain', $domain->id, $domainName, null);
        });

        return redirect()->route('client.invoices.index')
            ->with('success', "Domain registration initiated for {$domainName}. Please pay the invoice to complete registration.");
    }

    // ------------------------------------------------------------------ //
    //  Domain Detail
    // ------------------------------------------------------------------ //

    public function show(Domain $domain)
    {
        $this->authorizeClient($domain);
        $domain->loadMissing('contacts');

        return view('client.domains.show', compact('domain'));
    }

    public function updateNameservers(Request $request, Domain $domain)
    {
        $this->authorizeClient($domain);

        $data = $request->validate([
            'ns1' => 'nullable|string|max:255',
            'ns2' => 'nullable|string|max:255',
            'ns3' => 'nullable|string|max:255',
            'ns4' => 'nullable|string|max:255',
        ]);

        $nameservers = array_values(array_filter($data));
        $result      = RegistrarService::module()->updateNameservers($domain, $nameservers);

        if ($result->success) {
            $domain->update($data);
            return back()->with('success', 'Nameservers updated.');
        }

        return back()->with('error', 'Update failed: ' . $result->error);
    }

    public function togglePrivacy(Domain $domain)
    {
        $this->authorizeClient($domain);

        if (! $domain->whois_privacy && ! \App\Models\DomainTld::where('tld', $domain->tld)->value('whois_privacy_available')) {
            return back()->with('error', 'WHOIS privacy is not available for this TLD.');
        }

        $enable = ! $domain->whois_privacy;
        $result = RegistrarService::module()->setPrivacy($domain, $enable);

        if ($result->success) {
            $domain->update(['whois_privacy' => $enable]);
            return back()->with('success', 'WHOIS privacy ' . ($enable ? 'enabled' : 'disabled') . '.');
        }

        return back()->with('error', 'Failed: ' . $result->error);
    }

    public function toggleAutoRenew(Domain $domain)
    {
        $this->authorizeClient($domain);
        $domain->update(['auto_renew' => ! $domain->auto_renew]);

        return back()->with('success', 'Auto-renew ' . ($domain->auto_renew ? 'enabled' : 'disabled') . '.');
    }

    public function requestEppCode(Domain $domain)
    {
        $this->authorizeClient($domain);

        $result = RegistrarService::module()->getEppCode($domain);

        if ($result->success) {
            ActivityLogger::log('domain.epp_requested', 'domain', $domain->id, $domain->domain_name, null);
            return back()->with('epp_code', $result->data['epp_code'] ?? '');
        }

        return back()->with('error', 'Could not retrieve EPP code: ' . $result->error);
    }

    private function authorizeClient(Domain $domain): void
    {
        if ($domain->client_id !== $this->client()->id) {
            abort(403);
        }
    }
}

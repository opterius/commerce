<?php

namespace App\Registrar\Modules;

use App\Contracts\DomainRegistrarModule;
use App\Models\Domain;
use App\Models\DomainContact;
use App\Models\Setting;
use App\Registrar\DomainCheckResult;
use App\Registrar\DomainResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResellerClubModule implements DomainRegistrarModule
{
    private string $authUserId;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $sandbox          = (bool) Setting::get('resellerclub_sandbox', true);
        $this->authUserId = (string) Setting::get('resellerclub_auth_userid', '');
        $this->apiKey     = (string) Setting::get('resellerclub_api_key', '');
        $this->baseUrl    = $sandbox
            ? 'https://test.httpapi.com/api/'
            : 'https://httpapi.com/api/';
    }

    // ------------------------------------------------------------------ //
    //  Availability
    // ------------------------------------------------------------------ //

    public function checkAvailability(string $sld, string $tld): DomainCheckResult
    {
        $results = $this->checkBulkAvailability($sld, [$tld]);
        return $results[$tld] ?? DomainCheckResult::error("{$sld}.{$tld}", $tld, 'No result returned.');
    }

    public function checkBulkAvailability(string $sld, array $tlds): array
    {
        try {
            $response = Http::get($this->baseUrl . 'domains/available.json', array_merge(
                $this->authParams(),
                [
                    'domain-name' => $sld,
                    'tlds'        => implode(',', $tlds),
                ]
            ));

            if (! $response->successful()) {
                return $this->bulkError($sld, $tlds, 'HTTP error: ' . $response->status());
            }

            $body = $response->json();
            $out  = [];

            foreach ($tlds as $tld) {
                $fullDomain = "{$sld}.{$tld}";
                $info       = $body[$fullDomain] ?? null;

                if ($info === null) {
                    $out[$tld] = DomainCheckResult::error($fullDomain, $tld, 'No data in response.');
                    continue;
                }

                $status  = $info['status'] ?? '';
                $premium = isset($info['premium_pricing']);

                $out[$tld] = $status === 'available'
                    ? DomainCheckResult::available($fullDomain, $tld, $premium)
                    : DomainCheckResult::unavailable($fullDomain, $tld);
            }

            return $out;
        } catch (\Throwable $e) {
            Log::error('ResellerClub checkBulkAvailability failed', ['error' => $e->getMessage()]);
            return $this->bulkError($sld, $tlds, $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Registration
    // ------------------------------------------------------------------ //

    public function register(Domain $domain, array $contacts, int $years): DomainResult
    {
        try {
            // Ensure registrar contacts exist for all four roles
            $contactIds = $this->resolveContactIds($domain, $contacts);
            if (! $contactIds) {
                return DomainResult::failure('Failed to create registrar contact.');
            }

            $params = array_merge($this->authParams(), [
                'domain-name'          => $domain->domain_name,
                'years'                => $years,
                'ns'                   => $domain->nameservers() ?: $this->defaultNameservers(),
                'registrant-contact-id' => $contactIds['registrant'],
                'admin-contact-id'     => $contactIds['admin'],
                'tech-contact-id'      => $contactIds['tech'],
                'billing-contact-id'   => $contactIds['billing'],
                'invoice-option'       => 'NoInvoice',
                'purchase-privacy'     => $domain->whois_privacy ? true : false,
                'protect-privacy'      => $domain->whois_privacy ? true : false,
            ]);

            $response = Http::post($this->baseUrl . 'domains/register.json', $params);

            return $this->parseOrderResponse($response, 'register');
        } catch (\Throwable $e) {
            Log::error('ResellerClub register failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Renewal
    // ------------------------------------------------------------------ //

    public function renew(Domain $domain, int $years): DomainResult
    {
        try {
            $response = Http::post($this->baseUrl . 'domains/renew.json', array_merge(
                $this->authParams(),
                [
                    'order-id'       => $domain->registrar_order_id,
                    'years'          => $years,
                    'exp-date'       => $domain->expiry_date?->timestamp,
                    'invoice-option' => 'NoInvoice',
                ]
            ));

            return $this->parseOrderResponse($response, 'renew');
        } catch (\Throwable $e) {
            Log::error('ResellerClub renew failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Transfer
    // ------------------------------------------------------------------ //

    public function transfer(Domain $domain, string $eppCode, array $contacts): DomainResult
    {
        try {
            $contactIds = $this->resolveContactIds($domain, $contacts);
            if (! $contactIds) {
                return DomainResult::failure('Failed to create registrar contact.');
            }

            $params = array_merge($this->authParams(), [
                'domain-name'           => $domain->domain_name,
                'auth-code'             => $eppCode,
                'ns'                    => $domain->nameservers() ?: $this->defaultNameservers(),
                'registrant-contact-id' => $contactIds['registrant'],
                'admin-contact-id'      => $contactIds['admin'],
                'tech-contact-id'       => $contactIds['tech'],
                'billing-contact-id'    => $contactIds['billing'],
                'invoice-option'        => 'NoInvoice',
                'purchase-privacy'      => $domain->whois_privacy ? true : false,
            ]);

            $response = Http::post($this->baseUrl . 'domains/transfer.json', $params);

            return $this->parseOrderResponse($response, 'transfer');
        } catch (\Throwable $e) {
            Log::error('ResellerClub transfer failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Domain Info
    // ------------------------------------------------------------------ //

    public function getDomainInfo(Domain $domain): DomainResult
    {
        try {
            $response = Http::get($this->baseUrl . 'domains/details.json', array_merge(
                $this->authParams(),
                [
                    'domain-name' => $domain->domain_name,
                    'options'     => 'All',
                ]
            ));

            if (! $response->successful()) {
                return DomainResult::failure('HTTP error: ' . $response->status());
            }

            $body = $response->json();

            if (isset($body['status']) && $body['status'] === 'ERROR') {
                return DomainResult::failure($body['message'] ?? 'Unknown error');
            }

            $expiry = isset($body['endtime']) ? date('Y-m-d', $body['endtime']) : null;
            $ns     = $body['ns'] ?? [];

            return DomainResult::success('', [
                'registrar_order_id' => $body['entityid'] ?? null,
                'status'             => $this->mapRegistrarStatus($body['currentstatus'] ?? ''),
                'expiry_date'        => $expiry,
                'ns1'                => $ns[0] ?? null,
                'ns2'                => $ns[1] ?? null,
                'ns3'                => $ns[2] ?? null,
                'ns4'                => $ns[3] ?? null,
                'is_locked'          => ($body['domainstatus'] ?? '') === 'LOCKED',
                'whois_privacy'      => ($body['privacyprotectedallowed'] ?? '') === 'true',
            ]);
        } catch (\Throwable $e) {
            Log::error('ResellerClub getDomainInfo failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Nameservers
    // ------------------------------------------------------------------ //

    public function updateNameservers(Domain $domain, array $nameservers): DomainResult
    {
        try {
            $response = Http::post($this->baseUrl . 'domains/modify-ns.json', array_merge(
                $this->authParams(),
                [
                    'order-id' => $domain->registrar_order_id,
                    'ns'       => $nameservers,
                ]
            ));

            return $this->parseBoolResponse($response, 'updateNameservers');
        } catch (\Throwable $e) {
            Log::error('ResellerClub updateNameservers failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  EPP Code
    // ------------------------------------------------------------------ //

    public function getEppCode(Domain $domain): DomainResult
    {
        try {
            $response = Http::get($this->baseUrl . 'domains/actions/get-transfer-authcode.json', array_merge(
                $this->authParams(),
                ['order-id' => $domain->registrar_order_id]
            ));

            if (! $response->successful()) {
                return DomainResult::failure('HTTP error: ' . $response->status());
            }

            $body = $response->json();

            if (isset($body['message'])) {
                return DomainResult::failure($body['message']);
            }

            $code = is_string($body) ? $body : ($body['auth-code'] ?? null);

            return $code
                ? DomainResult::success('', ['epp_code' => $code])
                : DomainResult::failure('EPP code not returned by registrar.');
        } catch (\Throwable $e) {
            Log::error('ResellerClub getEppCode failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Lock
    // ------------------------------------------------------------------ //

    public function setLock(Domain $domain, bool $locked): DomainResult
    {
        try {
            $endpoint = $locked
                ? 'domains/enable-theft-protection.json'
                : 'domains/disable-theft-protection.json';

            $response = Http::post($this->baseUrl . $endpoint, array_merge(
                $this->authParams(),
                ['order-id' => $domain->registrar_order_id]
            ));

            return $this->parseBoolResponse($response, 'setLock');
        } catch (\Throwable $e) {
            Log::error('ResellerClub setLock failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Privacy
    // ------------------------------------------------------------------ //

    public function setPrivacy(Domain $domain, bool $enabled): DomainResult
    {
        try {
            $response = Http::post($this->baseUrl . 'domains/modify-privacy-protection.json', array_merge(
                $this->authParams(),
                [
                    'order-id'         => $domain->registrar_order_id,
                    'protect-privacy'  => $enabled,
                    'reason'           => $enabled ? 'Customer requested privacy protection.' : 'Customer disabled privacy protection.',
                ]
            ));

            return $this->parseBoolResponse($response, 'setPrivacy');
        } catch (\Throwable $e) {
            Log::error('ResellerClub setPrivacy failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Test Connection
    // ------------------------------------------------------------------ //

    public function testConnection(): DomainResult
    {
        try {
            if (empty($this->authUserId) || empty($this->apiKey)) {
                return DomainResult::failure('Auth credentials not configured.');
            }

            $response = Http::get($this->baseUrl . 'domains/available.json', array_merge(
                $this->authParams(),
                ['domain-name' => 'test', 'tlds' => 'com']
            ));

            if ($response->status() === 403) {
                return DomainResult::failure('Authentication failed — check your credentials.');
            }

            return DomainResult::success('Connection successful.');
        } catch (\Throwable $e) {
            return DomainResult::failure('Connection failed: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Contacts
    // ------------------------------------------------------------------ //

    private function resolveContactIds(Domain $domain, array $contacts): ?array
    {
        $registrant = $domain->contacts->firstWhere('type', 'registrant');

        if ($registrant && $registrant->registrar_contact_id) {
            $id = $registrant->registrar_contact_id;
            return ['registrant' => $id, 'admin' => $id, 'tech' => $id, 'billing' => $id];
        }

        // Use passed contacts array or fallback to stored registrant
        $contact = $contacts['registrant'] ?? $registrant;
        if (! $contact) {
            return null;
        }

        $id = $this->createRegistrarContact($contact);
        if (! $id) {
            return null;
        }

        // Cache the registrar_contact_id
        if ($registrant) {
            $registrant->update(['registrar_contact_id' => $id]);
        }

        return ['registrant' => $id, 'admin' => $id, 'tech' => $id, 'billing' => $id];
    }

    private function createRegistrarContact(DomainContact|array $contact): ?string
    {
        $data = $contact instanceof DomainContact ? $contact->toArray() : $contact;

        try {
            $response = Http::post($this->baseUrl . 'contacts/add.json', array_merge(
                $this->authParams(),
                [
                    'name'         => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                    'company'      => $data['company'] ?: 'N/A',
                    'email'        => $data['email'],
                    'phone-cc'     => $this->parsePhoneCountryCode($data['phone'] ?? ''),
                    'phone'        => $this->parsePhoneNumber($data['phone'] ?? ''),
                    'address-line-1' => $data['address_1'],
                    'city'         => $data['city'],
                    'state'        => $data['state'] ?? $data['city'],
                    'zipcode'      => $data['postcode'],
                    'country'      => $data['country_code'],
                    'type'         => 'Contact',
                ]
            ));

            $body = $response->json();

            if (is_numeric($body)) {
                return (string) $body;
            }

            if (isset($body['status']) && $body['status'] === 'ERROR') {
                Log::error('ResellerClub createContact error', ['message' => $body['message'] ?? '']);
                return null;
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('ResellerClub createContact exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function authParams(): array
    {
        return [
            'auth-userid' => $this->authUserId,
            'api-key'     => $this->apiKey,
        ];
    }

    private function defaultNameservers(): array
    {
        return ['ns1.resellerclub.com', 'ns2.resellerclub.com'];
    }

    private function parseOrderResponse($response, string $action): DomainResult
    {
        if (! $response->successful()) {
            return DomainResult::failure("HTTP error {$response->status()} on {$action}.");
        }

        $body = $response->json();

        if (isset($body['status']) && $body['status'] === 'ERROR') {
            return DomainResult::failure($body['message'] ?? "Unknown error on {$action}.");
        }

        return DomainResult::success('', [
            'registrar_order_id' => $body['entityid'] ?? null,
            'registrar_domain_id' => $body['entityid'] ?? null,
            'description'        => $body['description'] ?? '',
        ]);
    }

    private function parseBoolResponse($response, string $action): DomainResult
    {
        if (! $response->successful()) {
            return DomainResult::failure("HTTP error {$response->status()} on {$action}.");
        }

        $body = $response->json();

        if (isset($body['status']) && $body['status'] === 'ERROR') {
            return DomainResult::failure($body['message'] ?? "Unknown error on {$action}.");
        }

        return DomainResult::success();
    }

    private function mapRegistrarStatus(string $status): string
    {
        return match (strtolower($status)) {
            'active'      => 'active',
            'expired'     => 'expired',
            'transferring' => 'pending',
            'pending'     => 'pending',
            default       => 'active',
        };
    }

    private function bulkError(string $sld, array $tlds, string $message): array
    {
        $out = [];
        foreach ($tlds as $tld) {
            $out[$tld] = DomainCheckResult::error("{$sld}.{$tld}", $tld, $message);
        }
        return $out;
    }

    private function parsePhoneCountryCode(string $e164): string
    {
        // E.164: +12025551234 → country code "1"
        if (str_starts_with($e164, '+')) {
            preg_match('/^\+(\d{1,3})/', $e164, $m);
            return $m[1] ?? '1';
        }
        return '1';
    }

    private function parsePhoneNumber(string $e164): string
    {
        if (str_starts_with($e164, '+')) {
            preg_match('/^\+\d{1,3}(\d+)/', $e164, $m);
            return $m[1] ?? $e164;
        }
        return $e164;
    }
}

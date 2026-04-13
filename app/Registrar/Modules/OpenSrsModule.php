<?php

namespace App\Registrar\Modules;

use App\Contracts\DomainRegistrarModule;
use App\Models\Domain;
use App\Models\Setting;
use App\Registrar\DomainCheckResult;
use App\Registrar\DomainResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * OpenSRS (Tucows) registrar module.
 *
 * Protocol : Custom XML envelope (OPS_envelope) over HTTPS POST.
 * Auth     : X-Username header + X-Signature = md5(md5(body . key) . key)
 * Sandbox  : https://horizon.opensrs.net:55443/
 * Production: https://rr-n1-tor.opensrs.net:55443/
 * Docs     : https://opensrs.com/resources/documentation/opensrsapi/
 */
class OpenSrsModule implements DomainRegistrarModule
{
    private string $username;
    private string $privateKey;
    private string $baseUrl;

    public function __construct()
    {
        $sandbox          = (bool) Setting::get('opensrs_sandbox', true);
        $this->username   = (string) Setting::get('opensrs_username', '');
        $this->privateKey = (string) Setting::get('opensrs_private_key', '');
        $this->baseUrl    = $sandbox
            ? 'https://horizon.opensrs.net:55443/'
            : 'https://rr-n1-tor.opensrs.net:55443/';
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
        $out = [];

        foreach ($tlds as $tld) {
            $fullDomain = "{$sld}.{$tld}";
            try {
                $resp = $this->call('LOOKUP', 'DOMAIN', ['domain' => $fullDomain]);

                if ($resp === null) {
                    $out[$tld] = DomainCheckResult::error($fullDomain, $tld, 'API call failed.');
                    continue;
                }

                $status    = strtolower($resp['attributes']['status'] ?? 'unknown');
                $out[$tld] = $status === 'available'
                    ? DomainCheckResult::available($fullDomain, $tld)
                    : DomainCheckResult::unavailable($fullDomain, $tld);
            } catch (\Throwable $e) {
                Log::error('OpenSRS checkAvailability failed', ['domain' => $fullDomain, 'error' => $e->getMessage()]);
                $out[$tld] = DomainCheckResult::error($fullDomain, $tld, $e->getMessage());
            }
        }

        return $out;
    }

    // ------------------------------------------------------------------ //
    //  Registration
    // ------------------------------------------------------------------ //

    public function register(Domain $domain, array $contacts, int $years): DomainResult
    {
        try {
            $registrant = $domain->contacts->firstWhere('type', 'registrant') ?? ($contacts['registrant'] ?? null);

            if (! $registrant) {
                return DomainResult::failure('No registrant contact provided.');
            }

            $contactSet  = $this->buildContactSet($registrant);
            $nameservers = $domain->nameservers() ?: $this->defaultNameservers();

            $resp = $this->call('SW_REGISTER', 'DOMAIN', [
                'domain'          => $domain->domain_name,
                'period'          => $years,
                'auto_renew'      => 0,
                'reg_type'        => 'new',
                'contact_set'     => $contactSet,
                'nameserver_list' => $this->buildNameserverList($nameservers),
            ]);

            return $this->parseResult($resp, 'register');
        } catch (\Throwable $e) {
            Log::error('OpenSRS register failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Renewal
    // ------------------------------------------------------------------ //

    public function renew(Domain $domain, int $years): DomainResult
    {
        try {
            $expiryYear = $domain->expiry_date
                ? (int) $domain->expiry_date->format('Y')
                : (int) now()->format('Y');

            $resp = $this->call('RENEW', 'DOMAIN', [
                'domain'                => $domain->domain_name,
                'currentexpirationyear' => $expiryYear,
                'period'                => $years,
                'auto_renew'            => 0,
                'handle'                => 'process',
            ]);

            return $this->parseResult($resp, 'renew');
        } catch (\Throwable $e) {
            Log::error('OpenSRS renew failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Transfer
    // ------------------------------------------------------------------ //

    public function transfer(Domain $domain, string $eppCode, array $contacts): DomainResult
    {
        try {
            $registrant = $domain->contacts->firstWhere('type', 'registrant') ?? ($contacts['registrant'] ?? null);
            if (! $registrant) {
                return DomainResult::failure('No registrant contact provided.');
            }

            $nameservers = $domain->nameservers() ?: $this->defaultNameservers();

            $resp = $this->call('TRANSFER', 'DOMAIN', [
                'domain'          => $domain->domain_name,
                'auth_info'       => $eppCode,
                'contact_set'     => $this->buildContactSet($registrant),
                'nameserver_list' => $this->buildNameserverList($nameservers),
                'reg_type'        => 'transfer',
                'handle'          => 'process',
            ]);

            return $this->parseResult($resp, 'transfer');
        } catch (\Throwable $e) {
            Log::error('OpenSRS transfer failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Domain Info
    // ------------------------------------------------------------------ //

    public function getDomainInfo(Domain $domain): DomainResult
    {
        try {
            $resp = $this->call('GET', 'DOMAIN', [
                'domain' => $domain->domain_name,
                'type'   => 'all_info',
            ]);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($this->errorText($resp));
            }

            $attr   = $resp['attributes'] ?? [];
            $expiry = null;

            if (! empty($attr['expiredate'])) {
                $expiry = date('Y-m-d', strtotime($attr['expiredate']));
            }

            // Nameservers come back as a list of assoc arrays with 'name'
            $ns = [];
            if (isset($attr['nameserver_list']) && is_array($attr['nameserver_list'])) {
                foreach ($attr['nameserver_list'] as $entry) {
                    if (is_array($entry) && isset($entry['name'])) {
                        $ns[] = $entry['name'];
                    } elseif (is_string($entry)) {
                        $ns[] = $entry;
                    }
                }
            }

            $status   = strtolower($attr['status'] ?? 'active');
            $isLocked = str_contains($status, 'lock');

            return DomainResult::success('', [
                'status'      => $this->mapStatus($status),
                'expiry_date' => $expiry,
                'is_locked'   => $isLocked,
                'ns1'         => $ns[0] ?? null,
                'ns2'         => $ns[1] ?? null,
                'ns3'         => $ns[2] ?? null,
                'ns4'         => $ns[3] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('OpenSRS getDomainInfo failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Nameservers
    // ------------------------------------------------------------------ //

    public function updateNameservers(Domain $domain, array $nameservers): DomainResult
    {
        try {
            $resp = $this->call('ADVANCED_UPDATE_NAMESERVERS', 'DOMAIN', [
                'domain'          => $domain->domain_name,
                'op_type'         => 'assign',
                'nameserver_list' => $this->buildNameserverList($nameservers),
            ]);

            return $this->parseResult($resp, 'updateNameservers');
        } catch (\Throwable $e) {
            Log::error('OpenSRS updateNameservers failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  EPP Code
    // ------------------------------------------------------------------ //

    public function getEppCode(Domain $domain): DomainResult
    {
        try {
            $resp = $this->call('GET', 'DOMAIN', [
                'domain' => $domain->domain_name,
                'type'   => 'auth_info',
            ]);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($this->errorText($resp));
            }

            $code = $resp['attributes']['auth_info'] ?? '';

            return $code
                ? DomainResult::success('', ['epp_code' => $code])
                : DomainResult::failure('Auth code not returned by OpenSRS.');
        } catch (\Throwable $e) {
            Log::error('OpenSRS getEppCode failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Lock
    // ------------------------------------------------------------------ //

    public function setLock(Domain $domain, bool $locked): DomainResult
    {
        try {
            $resp = $this->call('MODIFY_DOMAIN', 'DOMAIN', [
                'domain'      => $domain->domain_name,
                'data'        => 'status',
                'lock_domain' => $locked ? '1' : '0',
            ]);

            return $this->parseResult($resp, 'setLock');
        } catch (\Throwable $e) {
            Log::error('OpenSRS setLock failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Privacy
    // ------------------------------------------------------------------ //

    public function setPrivacy(Domain $domain, bool $enabled): DomainResult
    {
        try {
            $resp = $this->call('MODIFY_DOMAIN', 'DOMAIN', [
                'domain'             => $domain->domain_name,
                'data'               => 'whois_privacy_state',
                'whois_privacy_state'=> $enabled ? 'enable' : 'disable',
                'affect_domains'     => '0',
            ]);

            return $this->parseResult($resp, 'setPrivacy');
        } catch (\Throwable $e) {
            Log::error('OpenSRS setPrivacy failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Test Connection
    // ------------------------------------------------------------------ //

    public function testConnection(): DomainResult
    {
        if (empty($this->username) || empty($this->privateKey)) {
            return DomainResult::failure('OpenSRS credentials not configured.');
        }

        try {
            // LOOKUP on a known-taken domain — if we get any valid response, connection works
            $resp = $this->call('LOOKUP', 'DOMAIN', ['domain' => 'example.com']);

            if ($resp === null) {
                return DomainResult::failure('No response from OpenSRS API.');
            }

            // A 401/400 means auth failed; any structured response means we connected
            $code = (int) ($resp['response_code'] ?? 0);
            if (in_array($code, [401, 415, 400])) {
                return DomainResult::failure('Authentication failed — check username and private key.');
            }

            return DomainResult::success('Connection successful.');
        } catch (\Throwable $e) {
            return DomainResult::failure('Connection failed: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  HTTP layer
    // ------------------------------------------------------------------ //

    /**
     * Build the OPS XML envelope, sign it, POST it, and return the parsed response array.
     */
    private function call(string $action, string $object, array $attributes): ?array
    {
        $xml = $this->buildEnvelope($action, $object, $attributes);
        $sig = md5(md5($xml . $this->privateKey) . $this->privateKey);

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml',
            'X-Username'   => $this->username,
            'X-Signature'  => $sig,
        ])->withBody($xml, 'text/xml')->post($this->baseUrl);

        if (! $response->successful()) {
            Log::error('OpenSRS HTTP error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 300)]);
            return null;
        }

        $prev    = libxml_use_internal_errors(true);
        $parsed  = simplexml_load_string($response->body());
        libxml_use_internal_errors($prev);

        if ($parsed === false) {
            Log::error('OpenSRS XML parse error', ['body' => substr($response->body(), 0, 500)]);
            return null;
        }

        // Navigate to the dt_assoc inside body > data_block
        $dataBlock = $parsed->body->data_block ?? null;
        if ($dataBlock === null) {
            return null;
        }

        // Find the dt_assoc child
        foreach ($dataBlock->children() as $child) {
            if ($child->getName() === 'dt_assoc') {
                return $this->parseNode($child);
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ //
    //  XML Builder
    // ------------------------------------------------------------------ //

    private function buildEnvelope(string $action, string $object, array $attributes): string
    {
        $attrXml = $this->encodeNode($attributes);

        return "<?xml version='1.0' encoding='UTF-8' standalone='no' ?>"
             . "<!DOCTYPE OPS_envelope SYSTEM 'ops.dtd'>"
             . '<OPS_envelope>'
             . '<header><version>0.9</version></header>'
             . '<body><data_block><dt_assoc>'
             . '<item key="protocol">XCP</item>'
             . "<item key=\"action\">{$action}</item>"
             . "<item key=\"object\">{$object}</item>"
             . '<item key="attributes"><dt_assoc>'
             . $attrXml
             . '</dt_assoc></item>'
             . '</dt_assoc></data_block></body>'
             . '</OPS_envelope>';
    }

    /**
     * Recursively encode a PHP array as OPS XML items.
     * Indexed arrays  → <dt_array>
     * Associative arrays → <dt_assoc>
     */
    private function encodeNode(array $data): string
    {
        $xml = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $inner = '';
                    foreach ($value as $i => $item) {
                        $inner .= is_array($item)
                            ? "<item key=\"{$i}\"><dt_assoc>" . $this->encodeNode($item) . '</dt_assoc></item>'
                            : "<item key=\"{$i}\">" . htmlspecialchars((string) $item, ENT_XML1) . '</item>';
                    }
                    $xml .= "<item key=\"{$key}\"><dt_array>{$inner}</dt_array></item>";
                } else {
                    $xml .= "<item key=\"{$key}\"><dt_assoc>" . $this->encodeNode($value) . '</dt_assoc></item>';
                }
            } else {
                $xml .= "<item key=\"{$key}\">" . htmlspecialchars((string) $value, ENT_XML1) . '</item>';
            }
        }
        return $xml;
    }

    // ------------------------------------------------------------------ //
    //  XML Parser
    // ------------------------------------------------------------------ //

    /**
     * Recursively parse an OPS dt_assoc or dt_array SimpleXMLElement into a PHP array.
     */
    private function parseNode(SimpleXMLElement $node): array
    {
        $result = [];
        foreach ($node->item as $item) {
            $key      = (string) $item['key'];
            $children = $item->children();

            if ($children->count() === 0) {
                $result[$key] = (string) $item;
                continue;
            }

            $child     = $children[0];
            $childName = $child->getName();

            $result[$key] = ($childName === 'dt_assoc' || $childName === 'dt_array')
                ? $this->parseNode($child)
                : (string) $child;
        }
        return $result;
    }

    // ------------------------------------------------------------------ //
    //  Contact and Nameserver builders
    // ------------------------------------------------------------------ //

    private function buildContactSet(mixed $contact): array
    {
        $d = is_array($contact) ? $contact : $contact->toArray();

        $phone = $this->formatPhone($d['phone'] ?? '');

        $person = [
            'first_name'  => $d['first_name'] ?? '',
            'last_name'   => $d['last_name'] ?? '',
            'org_name'    => $d['company'] ?? '',
            'address1'    => $d['address_1'] ?? '',
            'city'        => $d['city'] ?? '',
            'state'       => $d['state'] ?? '',
            'postal_code' => $d['postcode'] ?? '',
            'country'     => $d['country_code'] ?? '',
            'email'       => $d['email'] ?? '',
            'phone'       => $phone,
        ];

        if (! empty($d['address_2'])) {
            $person['address2'] = $d['address_2'];
        }

        return [
            'owner'   => $person,
            'admin'   => $person,
            'tech'    => $person,
            'billing' => $person,
        ];
    }

    private function buildNameserverList(array $nameservers): array
    {
        $list = [];
        foreach (array_values(array_filter($nameservers)) as $i => $ns) {
            $list[] = ['name' => $ns, 'sortorder' => $i + 1];
        }
        return $list;
    }

    // ------------------------------------------------------------------ //
    //  Response helpers
    // ------------------------------------------------------------------ //

    private function isSuccess(array $resp): bool
    {
        return ($resp['is_success'] ?? '0') === '1';
    }

    private function errorText(array $resp): string
    {
        return $resp['response_text'] ?? ('OpenSRS error code: ' . ($resp['response_code'] ?? 'unknown'));
    }

    private function parseResult(?array $resp, string $action): DomainResult
    {
        if ($resp === null) {
            return DomainResult::failure("API call failed for {$action}.");
        }

        if (! $this->isSuccess($resp)) {
            return DomainResult::failure($this->errorText($resp));
        }

        $data = [];

        // Capture order/transfer ID if present
        $attr = $resp['attributes'] ?? [];
        foreach (['id', 'transfer_id', 'domain_id', 'registration_expiration_date'] as $key) {
            if (! empty($attr[$key])) {
                $data[$key] = $attr[$key];
            }
        }

        return DomainResult::success('', $data);
    }

    private function mapStatus(string $status): string
    {
        return match (true) {
            str_contains($status, 'active')    => 'active',
            str_contains($status, 'expired')   => 'expired',
            str_contains($status, 'transfer')  => 'pending',
            str_contains($status, 'cancel')    => 'cancelled',
            default                            => 'active',
        };
    }

    /**
     * Convert E.164 (+12025551234) → OpenSRS/Enom phone format (+1.2025551234).
     */
    private function formatPhone(string $e164): string
    {
        if (! str_starts_with($e164, '+')) {
            return $e164;
        }

        $digits = ltrim($e164, '+');

        if (str_starts_with($digits, '1')) {
            return '+1.' . substr($digits, 1);
        }

        $cc2 = substr($digits, 0, 2);
        $twoDigitCodes = ['20','27','30','31','32','33','34','35','36','37','38','39',
                          '40','41','42','43','44','45','46','47','48','49',
                          '51','52','53','54','55','56','57','58','59',
                          '60','61','62','63','64','65','66','81','82','84','86',
                          '90','91','92','93','94','95','98'];

        if (in_array($cc2, $twoDigitCodes)) {
            return "+{$cc2}." . substr($digits, 2);
        }

        return '+' . substr($digits, 0, 3) . '.' . substr($digits, 3);
    }

    private function defaultNameservers(): array
    {
        return ['ns1.opensrs.net', 'ns2.opensrs.net'];
    }
}

<?php

namespace App\Registrar\Modules;

use App\Contracts\DomainRegistrarModule;
use App\Models\Domain;
use App\Models\Setting;
use App\Registrar\DomainCheckResult;
use App\Registrar\DomainResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CentralNic Reseller (formerly Hexonet / 1API) registrar module.
 *
 * Protocol : HTTPS POST, application/x-www-form-urlencoded.
 * Response : Plain-text key=value pairs between [RESPONSE] and EOF.
 * Auth     : s_login + s_pw flat params on every request.
 * OTE      : https://api-ote.rrpproxy.net/api/call.cgi
 * Production: https://api.rrpproxy.net/api/call.cgi
 * Docs     : https://centralnic-reseller.com/en/products/reseller-api/
 */
class CentralNicModule implements DomainRegistrarModule
{
    private string $login;
    private string $password;
    private string $baseUrl;

    public function __construct()
    {
        $sandbox        = (bool) Setting::get('centralnic_sandbox', true);
        $this->login    = (string) Setting::get('centralnic_login', '');
        $this->password = (string) Setting::get('centralnic_password', '');
        $this->baseUrl  = $sandbox
            ? 'https://api-ote.rrpproxy.net/api/call.cgi'
            : 'https://api.rrpproxy.net/api/call.cgi';
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

        try {
            $params = ['command' => 'CheckDomains'];
            foreach (array_values($tlds) as $i => $tld) {
                $params["domain{$i}"] = "{$sld}.{$tld}";
            }

            $resp = $this->call($params);

            if ($resp === null) {
                return $this->bulkError($sld, $tlds, 'API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return $this->bulkError($sld, $tlds, $resp['description'] ?? 'Unknown error.');
            }

            // Response includes PROPERTY[DOMAIN][N] and PROPERTY[DOMAINCHECK][N]
            $domains = $resp['properties']['DOMAIN'] ?? [];
            $checks  = $resp['properties']['DOMAINCHECK'] ?? [];

            foreach ($domains as $idx => $domain) {
                $domain    = strtolower($domain);
                $domainTld = substr($domain, strlen($sld) + 1);
                $check     = strtoupper($checks[$idx] ?? '');
                $available = $check === 'AVAILABLE';

                if (in_array($domainTld, $tlds)) {
                    $out[$domainTld] = $available
                        ? DomainCheckResult::available($domain, $domainTld)
                        : DomainCheckResult::unavailable($domain, $domainTld);
                }
            }

            foreach ($tlds as $tld) {
                if (! isset($out[$tld])) {
                    $out[$tld] = DomainCheckResult::error("{$sld}.{$tld}", $tld, 'Not in response.');
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::error('CentralNic checkBulkAvailability failed', ['error' => $e->getMessage()]);
            return $this->bulkError($sld, $tlds, $e->getMessage());
        }
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

            $c  = is_array($registrant) ? $registrant : $registrant->toArray();
            $ns = array_values(array_filter($domain->nameservers() ?: $this->defaultNameservers()));

            $params = array_merge(
                ['command' => 'AddDomain', 'domain' => $domain->domain_name, 'period' => "{$years}Y"],
                $this->inlineContact('ownercontact0', $c),
                $this->inlineContact('admincontact0', $c),
                $this->inlineContact('techcontact0', $c),
                $this->inlineContact('billingcontact0', $c),
                $this->nameserverParams($ns),
            );

            $resp = $this->call($params);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Registration failed.');
            }

            return DomainResult::success('', [
                'registrar_order_id' => $resp['properties']['DOMAINUNICODE'][0]
                    ?? $resp['properties']['DOMAIN'][0]
                    ?? $domain->domain_name,
            ]);
        } catch (\Throwable $e) {
            Log::error('CentralNic register failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Renewal
    // ------------------------------------------------------------------ //

    public function renew(Domain $domain, int $years): DomainResult
    {
        try {
            // CentralNic requires the current expiration year.
            $expirationYear = $domain->expiry_date
                ? $domain->expiry_date->format('Y')
                : date('Y');

            $resp = $this->call([
                'command'    => 'RenewDomain',
                'domain'     => $domain->domain_name,
                'period'     => "{$years}Y",
                'expiration' => $expirationYear,
            ]);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Renewal failed.');
            }

            return DomainResult::success('');
        } catch (\Throwable $e) {
            Log::error('CentralNic renew failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Transfer
    // ------------------------------------------------------------------ //

    public function transfer(Domain $domain, string $eppCode, array $contacts): DomainResult
    {
        try {
            $c = [];
            if (! empty($contacts['registrant'])) {
                $r = $contacts['registrant'];
                $c = is_array($r) ? $r : $r->toArray();
            }

            $params = array_merge(
                [
                    'command' => 'TransferDomain',
                    'domain'  => $domain->domain_name,
                    'auth'    => $eppCode,
                    'period'  => '1Y',
                ],
                empty($c) ? [] : $this->inlineContact('ownercontact0', $c),
            );

            $resp = $this->call($params);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Transfer failed.');
            }

            return DomainResult::success('');
        } catch (\Throwable $e) {
            Log::error('CentralNic transfer failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Domain Info
    // ------------------------------------------------------------------ //

    public function getDomainInfo(Domain $domain): DomainResult
    {
        try {
            $resp = $this->call([
                'command' => 'StatusDomain',
                'domain'  => $domain->domain_name,
            ]);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Status query failed.');
            }

            $props  = $resp['properties'];
            $expiry = null;

            if (! empty($props['EXPIRATIONDATE'][0])) {
                $expiry = date('Y-m-d', strtotime($props['EXPIRATIONDATE'][0]));
            }

            $ns = [];
            foreach (($props['NAMESERVER'] ?? []) as $nameserver) {
                $ns[] = strtolower($nameserver);
            }

            $rawStatus = strtolower($props['STATUS'][0] ?? 'active');

            return DomainResult::success('', [
                'status'      => $this->mapStatus($rawStatus),
                'expiry_date' => $expiry,
                'ns1'         => $ns[0] ?? null,
                'ns2'         => $ns[1] ?? null,
                'ns3'         => $ns[2] ?? null,
                'ns4'         => $ns[3] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('CentralNic getDomainInfo failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Nameservers
    // ------------------------------------------------------------------ //

    public function updateNameservers(Domain $domain, array $nameservers): DomainResult
    {
        try {
            $ns = array_values(array_filter($nameservers));

            $resp = $this->call(array_merge(
                [
                    'command'          => 'ModifyDomain',
                    'domain'           => $domain->domain_name,
                    'delallnameservers' => 1,
                ],
                $this->nameserverParams($ns),
            ));

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Nameserver update failed.');
            }

            return DomainResult::success('Nameservers updated.');
        } catch (\Throwable $e) {
            Log::error('CentralNic updateNameservers failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  EPP Code
    // ------------------------------------------------------------------ //

    public function getEppCode(Domain $domain): DomainResult
    {
        try {
            $resp = $this->call([
                'command' => 'StatusDomain',
                'domain'  => $domain->domain_name,
            ]);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Status query failed.');
            }

            $code = $resp['properties']['AUTH'][0] ?? '';

            return $code
                ? DomainResult::success('', ['epp_code' => $code])
                : DomainResult::failure('Auth code not returned. Ensure the domain is unlocked.');
        } catch (\Throwable $e) {
            Log::error('CentralNic getEppCode failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Lock
    // ------------------------------------------------------------------ //

    public function setLock(Domain $domain, bool $locked): DomainResult
    {
        try {
            $resp = $this->call([
                'command'      => 'ModifyDomain',
                'domain'       => $domain->domain_name,
                'transferlock' => $locked ? '1' : '0',
            ]);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Lock update failed.');
            }

            return DomainResult::success($locked ? 'Domain locked.' : 'Domain unlocked.');
        } catch (\Throwable $e) {
            Log::error('CentralNic setLock failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Privacy (WHOIS Trustee)
    // ------------------------------------------------------------------ //

    public function setPrivacy(Domain $domain, bool $enabled): DomainResult
    {
        try {
            $params = ['command' => 'ModifyDomain', 'domain' => $domain->domain_name];

            if ($enabled) {
                $params['x-accept-whoistrustee-tac'] = '1';
                $params['x-whois-privacy']            = '1';
            } else {
                $params['x-whois-privacy'] = '0';
            }

            $resp = $this->call($params);

            if ($resp === null) {
                return DomainResult::failure('API call failed.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure($resp['description'] ?? 'Privacy update failed.');
            }

            return DomainResult::success($enabled ? 'Privacy enabled.' : 'Privacy disabled.');
        } catch (\Throwable $e) {
            Log::error('CentralNic setPrivacy failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Test Connection
    // ------------------------------------------------------------------ //

    public function testConnection(): DomainResult
    {
        try {
            if (empty($this->login) || empty($this->password)) {
                return DomainResult::failure('CentralNic credentials not configured.');
            }

            $resp = $this->call(['command' => 'CheckDomains', 'domain0' => 'example.com']);

            if ($resp === null) {
                return DomainResult::failure('No response from CentralNic API.');
            }

            if (! $this->isSuccess($resp)) {
                return DomainResult::failure('Authentication failed: ' . ($resp['description'] ?? 'Unknown error.'));
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
     * POST to the CentralNic API and parse the plain-text response.
     *
     * @return array{code:string,description:string,properties:array<string,array<int,string>>}|null
     */
    private function call(array $params): ?array
    {
        $params = array_merge([
            's_login' => $this->login,
            's_pw'    => $this->password,
        ], $params);

        $response = Http::timeout(30)
            ->asForm()
            ->post($this->baseUrl, $params);

        if (! $response->successful()) {
            Log::error('CentralNic HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $this->parseBody($response->body());
    }

    // ------------------------------------------------------------------ //
    //  Response parser
    // ------------------------------------------------------------------ //

    /**
     * Parse the CentralNic plain-text response format:
     *
     *   [RESPONSE]
     *   CODE=200
     *   DESCRIPTION=Command completed successfully
     *   PROPERTY[DOMAIN][0]=example.com
     *   PROPERTY[STATUS][0]=ACTIVE
     *   EOF
     */
    private function parseBody(string $body): array
    {
        $result = ['code' => '', 'description' => '', 'properties' => []];

        foreach (explode("\n", $body) as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'CODE=')) {
                $result['code'] = substr($line, 5);
            } elseif (str_starts_with($line, 'DESCRIPTION=')) {
                $result['description'] = substr($line, 12);
            } elseif (preg_match('/^PROPERTY\[([^\]]+)\]\[(\d+)\]=(.*)$/', $line, $m)) {
                $result['properties'][$m[1]][(int) $m[2]] = $m[3];
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function isSuccess(array $resp): bool
    {
        // Codes 200 and 201 indicate success; 210/211 are availability-specific.
        return in_array($resp['code'], ['200', '201', '210', '211']);
    }

    /**
     * Build inline contact params for AddDomain / TransferDomain.
     * CentralNic uses hyphen-separated prefix: ownercontact0-firstname
     */
    private function inlineContact(string $prefix, array $c): array
    {
        return [
            "{$prefix}-firstname"    => $c['first_name'] ?? '',
            "{$prefix}-lastname"     => $c['last_name'] ?? '',
            "{$prefix}-organization" => $c['company'] ?? '',
            "{$prefix}-street"       => trim(($c['address_1'] ?? '') . ' ' . ($c['address_2'] ?? '')),
            "{$prefix}-city"         => $c['city'] ?? '',
            "{$prefix}-state"        => $c['state'] ?? '',
            "{$prefix}-zip"          => $c['postcode'] ?? '',
            "{$prefix}-country"      => $c['country_code'] ?? '',
            "{$prefix}-phone"        => $c['phone'] ?? '',
            "{$prefix}-email"        => $c['email'] ?? '',
        ];
    }

    /** Build nameserver0, nameserver1, ... params. */
    private function nameserverParams(array $ns): array
    {
        $params = [];
        foreach (array_values($ns) as $i => $nameserver) {
            $params["nameserver{$i}"] = $nameserver;
        }
        return $params;
    }

    private function mapStatus(string $status): string
    {
        return match (true) {
            str_contains($status, 'active')   => 'active',
            str_contains($status, 'expired')  => 'expired',
            str_contains($status, 'transfer') => 'pending',
            str_contains($status, 'pending')  => 'pending',
            default                           => 'active',
        };
    }

    private function defaultNameservers(): array
    {
        return ['ns1.centralnic.net', 'ns2.centralnic.net'];
    }

    private function bulkError(string $sld, array $tlds, string $message): array
    {
        $out = [];
        foreach ($tlds as $tld) {
            $out[$tld] = DomainCheckResult::error("{$sld}.{$tld}", $tld, $message);
        }
        return $out;
    }
}

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
 * Namecheap registrar module.
 *
 * API: HTTPS GET, XML responses.
 * Sandbox: https://api.sandbox.namecheap.com/xml.response
 * Production: https://api.namecheap.com/xml.response
 * Auth: ApiUser + ApiKey + UserName + ClientIp query params on every request.
 * ClientIp must be a whitelisted IP in the Namecheap account.
 * Docs: https://www.namecheap.com/support/api/methods/
 */
class NamecheapModule implements DomainRegistrarModule
{
    private string $apiUser;
    private string $apiKey;
    private string $clientIp;
    private string $baseUrl;

    public function __construct()
    {
        $sandbox         = (bool) Setting::get('namecheap_sandbox', true);
        $this->apiUser   = (string) Setting::get('namecheap_api_user', '');
        $this->apiKey    = (string) Setting::get('namecheap_api_key', '');
        $this->clientIp  = (string) Setting::get('namecheap_client_ip', '127.0.0.1');
        $this->baseUrl   = $sandbox
            ? 'https://api.sandbox.namecheap.com/xml.response'
            : 'https://api.namecheap.com/xml.response';
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
            // Namecheap accepts a comma-separated domain list for bulk check.
            $domainList = implode(',', array_map(fn($t) => "{$sld}.{$t}", $tlds));

            $xml = $this->call([
                'Command'    => 'namecheap.domains.check',
                'DomainList' => $domainList,
            ]);

            if ($xml === null) {
                return $this->bulkError($sld, $tlds, 'API call failed.');
            }

            if ($this->isError($xml)) {
                return $this->bulkError($sld, $tlds, $this->errorMessage($xml));
            }

            $results = $xml->CommandResponse->DomainCheckResult ?? null;

            if ($results) {
                foreach ($results as $result) {
                    $domain    = strtolower((string) ($result['Domain'] ?? ''));
                    $available = strtolower((string) ($result['Available'] ?? 'false')) === 'true';
                    $domainTld = substr($domain, strlen($sld) + 1);

                    if (in_array($domainTld, $tlds)) {
                        $out[$domainTld] = $available
                            ? DomainCheckResult::available($domain, $domainTld)
                            : DomainCheckResult::unavailable($domain, $domainTld);
                    }
                }
            }

            // Fill in any TLDs not returned.
            foreach ($tlds as $tld) {
                if (! isset($out[$tld])) {
                    $out[$tld] = DomainCheckResult::error("{$sld}.{$tld}", $tld, 'Not in response.');
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::error('Namecheap checkBulkAvailability failed', ['error' => $e->getMessage()]);
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

            $xml = $this->call([
                'Command'    => 'namecheap.domains.create',
                'DomainName' => $domain->domain_name,
                'Years'      => $years,

                // Registrant
                'RegistrantFirstName'        => $c['first_name'] ?? '',
                'RegistrantLastName'         => $c['last_name'] ?? '',
                'RegistrantOrganizationName' => $c['company'] ?? '',
                'RegistrantJobTitle'         => '',
                'RegistrantAddress1'         => $c['address_1'] ?? '',
                'RegistrantAddress2'         => $c['address_2'] ?? '',
                'RegistrantCity'             => $c['city'] ?? '',
                'RegistrantStateProvince'    => $c['state'] ?? '',
                'RegistrantPostalCode'       => $c['postcode'] ?? '',
                'RegistrantCountry'          => $c['country_code'] ?? '',
                'RegistrantPhone'            => $c['phone'] ?? '',
                'RegistrantEmailAddress'     => $c['email'] ?? '',

                // Admin (same as registrant)
                'AdminFirstName'             => $c['first_name'] ?? '',
                'AdminLastName'              => $c['last_name'] ?? '',
                'AdminOrganizationName'      => $c['company'] ?? '',
                'AdminAddress1'              => $c['address_1'] ?? '',
                'AdminCity'                  => $c['city'] ?? '',
                'AdminStateProvince'         => $c['state'] ?? '',
                'AdminPostalCode'            => $c['postcode'] ?? '',
                'AdminCountry'               => $c['country_code'] ?? '',
                'AdminPhone'                 => $c['phone'] ?? '',
                'AdminEmailAddress'          => $c['email'] ?? '',

                // Tech (same as registrant)
                'TechFirstName'              => $c['first_name'] ?? '',
                'TechLastName'               => $c['last_name'] ?? '',
                'TechOrganizationName'       => $c['company'] ?? '',
                'TechAddress1'               => $c['address_1'] ?? '',
                'TechCity'                   => $c['city'] ?? '',
                'TechStateProvince'          => $c['state'] ?? '',
                'TechPostalCode'             => $c['postcode'] ?? '',
                'TechCountry'                => $c['country_code'] ?? '',
                'TechPhone'                  => $c['phone'] ?? '',
                'TechEmailAddress'           => $c['email'] ?? '',

                // AuxBilling (same as registrant)
                'AuxBillingFirstName'        => $c['first_name'] ?? '',
                'AuxBillingLastName'         => $c['last_name'] ?? '',
                'AuxBillingOrganizationName' => $c['company'] ?? '',
                'AuxBillingAddress1'         => $c['address_1'] ?? '',
                'AuxBillingCity'             => $c['city'] ?? '',
                'AuxBillingStateProvince'    => $c['state'] ?? '',
                'AuxBillingPostalCode'       => $c['postcode'] ?? '',
                'AuxBillingCountry'          => $c['country_code'] ?? '',
                'AuxBillingPhone'            => $c['phone'] ?? '',
                'AuxBillingEmailAddress'     => $c['email'] ?? '',

                // Nameservers — comma-separated; max 12
                'Nameservers'        => implode(',', array_slice($ns, 0, 12)),
                'AddFreeWhoisguard'  => 'yes',
                'WGEnabled'          => 'no',
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            $result  = $xml->CommandResponse->DomainCreateResult ?? null;
            $orderId = $result ? (string) ($result['OrderID'] ?? '') : '';

            return DomainResult::success('', array_filter(['registrar_order_id' => $orderId]));
        } catch (\Throwable $e) {
            Log::error('Namecheap register failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Renewal
    // ------------------------------------------------------------------ //

    public function renew(Domain $domain, int $years): DomainResult
    {
        try {
            $xml = $this->call([
                'Command'    => 'namecheap.domains.renew',
                'DomainName' => $domain->domain_name,
                'Years'      => $years,
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            $result  = $xml->CommandResponse->DomainRenewResult ?? null;
            $orderId = $result ? (string) ($result['OrderID'] ?? '') : '';

            return DomainResult::success('', array_filter(['registrar_order_id' => $orderId]));
        } catch (\Throwable $e) {
            Log::error('Namecheap renew failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
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

            $xml = $this->call([
                'Command'                 => 'namecheap.domains.transfer.create',
                'DomainName'              => $domain->domain_name,
                'Years'                   => 1,
                'EPPCode'                 => $eppCode,
                'RegistrantFirstName'     => $c['first_name'] ?? '',
                'RegistrantLastName'      => $c['last_name'] ?? '',
                'RegistrantAddress1'      => $c['address_1'] ?? '',
                'RegistrantCity'          => $c['city'] ?? '',
                'RegistrantStateProvince' => $c['state'] ?? '',
                'RegistrantPostalCode'    => $c['postcode'] ?? '',
                'RegistrantCountry'       => $c['country_code'] ?? '',
                'RegistrantPhone'         => $c['phone'] ?? '',
                'RegistrantEmailAddress'  => $c['email'] ?? '',
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            $result  = $xml->CommandResponse->TransferCreateResult ?? null;
            $orderId = $result ? (string) ($result['OrderID'] ?? '') : '';

            return DomainResult::success('', array_filter(['registrar_order_id' => $orderId]));
        } catch (\Throwable $e) {
            Log::error('Namecheap transfer failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Domain Info
    // ------------------------------------------------------------------ //

    public function getDomainInfo(Domain $domain): DomainResult
    {
        try {
            $xml = $this->call([
                'Command'    => 'namecheap.domains.getInfo',
                'DomainName' => $domain->domain_name,
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            $info = $xml->CommandResponse->DomainGetInfoResult ?? null;

            if (! $info) {
                return DomainResult::failure('No domain info returned.');
            }

            // Expiry date
            $expiry = null;
            if (isset($info->DomainDetails->ExpiredDate)) {
                $raw    = (string) $info->DomainDetails->ExpiredDate;
                $expiry = $raw ? date('Y-m-d', strtotime($raw)) : null;
            }

            // Nameservers
            $ns = [];
            if (isset($info->DnsDetails->Nameserver)) {
                foreach ($info->DnsDetails->Nameserver as $nameserver) {
                    $ns[] = strtolower((string) $nameserver);
                }
            }

            $status = strtolower((string) ($info['Status'] ?? 'ok'));

            return DomainResult::success('', [
                'status'      => $this->mapStatus($status),
                'expiry_date' => $expiry,
                'ns1'         => $ns[0] ?? null,
                'ns2'         => $ns[1] ?? null,
                'ns3'         => $ns[2] ?? null,
                'ns4'         => $ns[3] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Namecheap getDomainInfo failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Nameservers
    // ------------------------------------------------------------------ //

    public function updateNameservers(Domain $domain, array $nameservers): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);
            $ns          = implode(',', array_values(array_filter($nameservers)));

            $xml = $this->call([
                'Command'     => 'namecheap.domains.dns.setCustom',
                'SLD'         => $sld,
                'TLD'         => $tld,
                'Nameservers' => $ns,
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            return DomainResult::success('Nameservers updated.');
        } catch (\Throwable $e) {
            Log::error('Namecheap updateNameservers failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  EPP Code
    // ------------------------------------------------------------------ //

    public function getEppCode(Domain $domain): DomainResult
    {
        try {
            $xml = $this->call([
                'Command'    => 'namecheap.domains.getInfo',
                'DomainName' => $domain->domain_name,
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            $info = $xml->CommandResponse->DomainGetInfoResult ?? null;
            $code = $info ? (string) ($info->DomainDetails->EPCCode ?? '') : '';

            return $code
                ? DomainResult::success('', ['epp_code' => $code])
                : DomainResult::failure('EPP code not returned. Ensure the domain is unlocked and eligible for transfer.');
        } catch (\Throwable $e) {
            Log::error('Namecheap getEppCode failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Lock
    // ------------------------------------------------------------------ //

    public function setLock(Domain $domain, bool $locked): DomainResult
    {
        try {
            $xml = $this->call([
                'Command'    => 'namecheap.domains.setRegistrarLock',
                'DomainName' => $domain->domain_name,
                'LockAction' => $locked ? 'LOCK' : 'UNLOCK',
            ]);

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            return DomainResult::success($locked ? 'Domain locked.' : 'Domain unlocked.');
        } catch (\Throwable $e) {
            Log::error('Namecheap setLock failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Privacy (WhoisGuard)
    // ------------------------------------------------------------------ //

    public function setPrivacy(Domain $domain, bool $enabled): DomainResult
    {
        try {
            // Retrieve the WhoisGuard ID from domain info first.
            $infoXml = $this->call([
                'Command'    => 'namecheap.domains.getInfo',
                'DomainName' => $domain->domain_name,
            ]);

            if ($infoXml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($infoXml)) {
                return DomainResult::failure($this->errorMessage($infoXml));
            }

            $info = $infoXml->CommandResponse->DomainGetInfoResult ?? null;
            $wgId = $info ? (string) ($info->Whoisguard->WhoisguardID ?? '') : '';

            if (! $wgId) {
                return DomainResult::failure('WhoisGuard not available for this domain.');
            }

            $command = $enabled
                ? 'namecheap.whoisguard.enable'
                : 'namecheap.whoisguard.disable';

            $wgXml = $this->call([
                'Command'         => $command,
                'WhoisguardId'    => $wgId,
                'ForwardedToMail' => '',
            ]);

            if ($wgXml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($wgXml)) {
                return DomainResult::failure($this->errorMessage($wgXml));
            }

            return DomainResult::success($enabled ? 'Privacy enabled.' : 'Privacy disabled.');
        } catch (\Throwable $e) {
            Log::error('Namecheap setPrivacy failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Test Connection
    // ------------------------------------------------------------------ //

    public function testConnection(): DomainResult
    {
        try {
            if (empty($this->apiUser) || empty($this->apiKey)) {
                return DomainResult::failure('Namecheap credentials not configured.');
            }

            $xml = $this->call([
                'Command'    => 'namecheap.domains.check',
                'DomainList' => 'example.com',
            ]);

            if ($xml === null) {
                return DomainResult::failure('No response from Namecheap API.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure('Authentication failed: ' . $this->errorMessage($xml));
            }

            return DomainResult::success('Connection successful.');
        } catch (\Throwable $e) {
            return DomainResult::failure('Connection failed: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  HTTP layer
    // ------------------------------------------------------------------ //

    private function call(array $params): ?SimpleXMLElement
    {
        $params = array_merge($this->authParams(), $params);

        $response = Http::timeout(30)->get($this->baseUrl, $params);

        if (! $response->successful()) {
            Log::error('Namecheap HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        $body = $response->body();

        $prev = libxml_use_internal_errors(true);
        $xml  = simplexml_load_string($body);
        libxml_use_internal_errors($prev);

        if ($xml === false) {
            Log::error('Namecheap XML parse error', ['body' => substr($body, 0, 500)]);
            return null;
        }

        return $xml;
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function authParams(): array
    {
        return [
            'ApiUser'  => $this->apiUser,
            'ApiKey'   => $this->apiKey,
            'UserName' => $this->apiUser,
            'ClientIp' => $this->clientIp,
        ];
    }

    private function isError(SimpleXMLElement $xml): bool
    {
        return strtoupper((string) ($xml['Status'] ?? '')) === 'ERROR';
    }

    private function errorMessage(SimpleXMLElement $xml): string
    {
        if (isset($xml->Errors->Error)) {
            return (string) $xml->Errors->Error;
        }
        return 'Unknown Namecheap error.';
    }

    private function splitDomain(string $fullDomain): array
    {
        $parts = explode('.', $fullDomain, 2);
        return [$parts[0], $parts[1] ?? ''];
    }

    private function mapStatus(string $status): string
    {
        return match (true) {
            str_contains($status, 'ok') || str_contains($status, 'active') => 'active',
            str_contains($status, 'expired')                               => 'expired',
            str_contains($status, 'transfer')                              => 'pending',
            default                                                        => 'active',
        };
    }

    private function defaultNameservers(): array
    {
        return ['dns1.registrar-servers.com', 'dns2.registrar-servers.com'];
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

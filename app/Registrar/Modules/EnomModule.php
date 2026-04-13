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
 * Enom (Tucows) registrar module.
 *
 * API: HTTPS GET/POST, XML responses.
 * Sandbox: https://resellertest.enom.com/interface.asp
 * Production: https://www.enom.com/interface.asp
 * Auth: uid + pw query params on every request.
 * Docs: https://www.enom.com/APICommandCatalog/
 */
class EnomModule implements DomainRegistrarModule
{
    private string $uid;
    private string $pw;
    private string $baseUrl;

    public function __construct()
    {
        $sandbox       = (bool) Setting::get('enom_sandbox', true);
        $this->uid     = (string) Setting::get('enom_uid', '');
        $this->pw      = (string) Setting::get('enom_pw', '');
        $this->baseUrl = $sandbox
            ? 'https://resellertest.enom.com/interface.asp'
            : 'https://www.enom.com/interface.asp';
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

        // Enom supports numbered SLD/TLD pairs for bulk check.
        $params = $this->baseParams('Check');
        $i      = 1;
        foreach ($tlds as $tld) {
            $params["SLD{$i}"] = $sld;
            $params["TLD{$i}"] = $tld;
            $i++;
        }

        try {
            $xml = $this->call($params);

            if ($xml === null) {
                return $this->bulkError($sld, $tlds, 'API call failed.');
            }

            // Single TLD returns top-level RRPCode; multiple TLDs return <Responses><Response>
            if (count($tlds) === 1) {
                $tld  = $tlds[0];
                $code = (string) ($xml->RRPCode ?? '');
                $out[$tld] = $code === '210'
                    ? DomainCheckResult::available("{$sld}.{$tld}", $tld)
                    : DomainCheckResult::unavailable("{$sld}.{$tld}", $tld);
            } else {
                // Index by TLD
                $tldMap = array_fill_keys($tlds, null);

                if (isset($xml->Responses->Response)) {
                    foreach ($xml->Responses->Response as $resp) {
                        $respTld  = strtolower((string) ($resp->TLD ?? ''));
                        $code     = (string) ($resp->RRPCode ?? '');
                        $available = $code === '210';

                        if (array_key_exists($respTld, $tldMap)) {
                            $tldMap[$respTld] = $available
                                ? DomainCheckResult::available("{$sld}.{$respTld}", $respTld)
                                : DomainCheckResult::unavailable("{$sld}.{$respTld}", $respTld);
                        }
                    }
                }

                foreach ($tlds as $tld) {
                    $out[$tld] = $tldMap[$tld] ?? DomainCheckResult::error("{$sld}.{$tld}", $tld, 'Not in response.');
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::error('Enom checkBulkAvailability failed', ['error' => $e->getMessage()]);
            return $this->bulkError($sld, $tlds, $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Registration
    // ------------------------------------------------------------------ //

    public function register(Domain $domain, array $contacts, int $years): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);
            $registrant  = $domain->contacts->firstWhere('type', 'registrant') ?? ($contacts['registrant'] ?? null);

            if (! $registrant) {
                return DomainResult::failure('No registrant contact provided.');
            }

            $contactData = is_array($registrant) ? $registrant : $registrant->toArray();
            $phone       = $this->formatPhone($contactData['phone'] ?? '');
            $ns          = array_values(array_filter($domain->nameservers() ?: $this->defaultNameservers()));

            $params = array_merge($this->baseParams('Purchase'), [
                'SLD'     => $sld,
                'TLD'     => $tld,
                'NumYears'=> $years,

                // Registrant
                'RegistrantFirstName'      => $contactData['first_name'] ?? '',
                'RegistrantLastName'       => $contactData['last_name'] ?? '',
                'RegistrantOrganizationName' => $contactData['company'] ?? '',
                'RegistrantAddress1'       => $contactData['address_1'] ?? '',
                'RegistrantAddress2'       => $contactData['address_2'] ?? '',
                'RegistrantCity'           => $contactData['city'] ?? '',
                'RegistrantStateProvince'  => $contactData['state'] ?? '',
                'RegistrantPostalCode'     => $contactData['postcode'] ?? '',
                'RegistrantCountry'        => $contactData['country_code'] ?? '',
                'RegistrantEmailAddress'   => $contactData['email'] ?? '',
                'RegistrantPhone'          => $phone,

                // Admin (same as registrant)
                'AdminFirstName'           => $contactData['first_name'] ?? '',
                'AdminLastName'            => $contactData['last_name'] ?? '',
                'AdminOrganizationName'    => $contactData['company'] ?? '',
                'AdminAddress1'            => $contactData['address_1'] ?? '',
                'AdminCity'                => $contactData['city'] ?? '',
                'AdminStateProvince'       => $contactData['state'] ?? '',
                'AdminPostalCode'          => $contactData['postcode'] ?? '',
                'AdminCountry'             => $contactData['country_code'] ?? '',
                'AdminEmailAddress'        => $contactData['email'] ?? '',
                'AdminPhone'               => $phone,

                // Tech (same as registrant)
                'TechFirstName'            => $contactData['first_name'] ?? '',
                'TechLastName'             => $contactData['last_name'] ?? '',
                'TechOrganizationName'     => $contactData['company'] ?? '',
                'TechAddress1'             => $contactData['address_1'] ?? '',
                'TechCity'                 => $contactData['city'] ?? '',
                'TechStateProvince'        => $contactData['state'] ?? '',
                'TechPostalCode'           => $contactData['postcode'] ?? '',
                'TechCountry'              => $contactData['country_code'] ?? '',
                'TechEmailAddress'         => $contactData['email'] ?? '',
                'TechPhone'                => $phone,

                // AuxBilling (same as registrant)
                'AuxBillingFirstName'      => $contactData['first_name'] ?? '',
                'AuxBillingLastName'       => $contactData['last_name'] ?? '',
                'AuxBillingOrganizationName' => $contactData['company'] ?? '',
                'AuxBillingAddress1'       => $contactData['address_1'] ?? '',
                'AuxBillingCity'           => $contactData['city'] ?? '',
                'AuxBillingStateProvince'  => $contactData['state'] ?? '',
                'AuxBillingPostalCode'     => $contactData['postcode'] ?? '',
                'AuxBillingCountry'        => $contactData['country_code'] ?? '',
                'AuxBillingEmailAddress'   => $contactData['email'] ?? '',
                'AuxBillingPhone'          => $phone,

                // Nameservers
                'NS1' => $ns[0] ?? '',
                'NS2' => $ns[1] ?? '',
                'NS3' => $ns[2] ?? '',
                'NS4' => $ns[3] ?? '',
            ]);

            $xml = $this->call($params);
            return $this->parseResponse($xml, 'register');
        } catch (\Throwable $e) {
            Log::error('Enom register failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Renewal
    // ------------------------------------------------------------------ //

    public function renew(Domain $domain, int $years): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);

            $xml = $this->call(array_merge($this->baseParams('Extend'), [
                'SLD'      => $sld,
                'TLD'      => $tld,
                'NumYears' => $years,
            ]));

            return $this->parseResponse($xml, 'renew');
        } catch (\Throwable $e) {
            Log::error('Enom renew failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Transfer
    // ------------------------------------------------------------------ //

    public function transfer(Domain $domain, string $eppCode, array $contacts): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);

            $xml = $this->call(array_merge($this->baseParams('TP_CreateOrder'), [
                'SLD'          => $sld,
                'TLD'          => $tld,
                'AuthInfo'     => $eppCode,
                'OrderType'    => 'dom',
                'UseContacts'  => 1,
            ]));

            return $this->parseResponse($xml, 'transfer');
        } catch (\Throwable $e) {
            Log::error('Enom transfer failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Domain Info
    // ------------------------------------------------------------------ //

    public function getDomainInfo(Domain $domain): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);

            $xml = $this->call(array_merge($this->baseParams('GetDomainInfo'), [
                'SLD' => $sld,
                'TLD' => $tld,
            ]));

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            // Parse expiry from GetDomainInfo -> status -> expiration
            $expiry = null;
            if (isset($xml->GetDomainInfo->status->expiration)) {
                $raw    = (string) $xml->GetDomainInfo->status->expiration;
                $expiry = $raw ? date('Y-m-d', strtotime($raw)) : null;
            }

            // Nameservers
            $ns = [];
            if (isset($xml->GetDomainInfo->services->entry)) {
                foreach ($xml->GetDomainInfo->services->entry as $entry) {
                    if (strtolower((string) ($entry->name ?? '')) === 'nameservers') {
                        foreach ($entry->configuration->dns as $dns) {
                            $ns[] = (string) $dns;
                        }
                        break;
                    }
                }
            }

            $status = strtolower((string) ($xml->GetDomainInfo->status->registrystatus ?? 'ACTIVE'));

            return DomainResult::success('', [
                'status'      => $this->mapStatus($status),
                'expiry_date' => $expiry,
                'ns1'         => $ns[0] ?? null,
                'ns2'         => $ns[1] ?? null,
                'ns3'         => $ns[2] ?? null,
                'ns4'         => $ns[3] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Enom getDomainInfo failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
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
            $ns          = array_values($nameservers);

            $xml = $this->call(array_merge($this->baseParams('ModifyNS'), [
                'SLD' => $sld,
                'TLD' => $tld,
                'NS1' => $ns[0] ?? '',
                'NS2' => $ns[1] ?? '',
                'NS3' => $ns[2] ?? '',
                'NS4' => $ns[3] ?? '',
            ]));

            return $this->parseResponse($xml, 'updateNameservers');
        } catch (\Throwable $e) {
            Log::error('Enom updateNameservers failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  EPP Code
    // ------------------------------------------------------------------ //

    public function getEppCode(Domain $domain): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);

            $xml = $this->call(array_merge($this->baseParams('GetAuthInfo'), [
                'SLD' => $sld,
                'TLD' => $tld,
            ]));

            if ($xml === null) {
                return DomainResult::failure('API call failed.');
            }

            if ($this->isError($xml)) {
                return DomainResult::failure($this->errorMessage($xml));
            }

            $code = (string) ($xml->authinfo ?? $xml->AuthInfo ?? '');

            return $code
                ? DomainResult::success('', ['epp_code' => $code])
                : DomainResult::failure('EPP code not returned by Enom.');
        } catch (\Throwable $e) {
            Log::error('Enom getEppCode failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Lock
    // ------------------------------------------------------------------ //

    public function setLock(Domain $domain, bool $locked): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);

            // UnlockRegistrar=0 means locked, 1 means unlocked
            $xml = $this->call(array_merge($this->baseParams('SetRegLock'), [
                'SLD'              => $sld,
                'TLD'              => $tld,
                'UnlockRegistrar'  => $locked ? '0' : '1',
            ]));

            return $this->parseResponse($xml, 'setLock');
        } catch (\Throwable $e) {
            Log::error('Enom setLock failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Privacy (WhoisGuard)
    // ------------------------------------------------------------------ //

    public function setPrivacy(Domain $domain, bool $enabled): DomainResult
    {
        try {
            [$sld, $tld] = $this->splitDomain($domain->domain_name);

            // First check if WhoisGuard is available for this domain
            $infoXml = $this->call(array_merge($this->baseParams('GetWhoisGuardInfo'), [
                'SLD' => $sld,
                'TLD' => $tld,
            ]));

            $wgId = null;
            if ($infoXml !== null && isset($infoXml->GetWhoisGuardInfo->WhoisGuardList->WhoisGuard)) {
                foreach ($infoXml->GetWhoisGuardInfo->WhoisGuardList->WhoisGuard as $wg) {
                    $wgId = (string) ($wg->WGID ?? '');
                    break;
                }
            }

            if (! $wgId && $enabled) {
                // Purchase WhoisGuard (free at Enom)
                $purchaseXml = $this->call(array_merge($this->baseParams('PurchaseServices'), [
                    'SLD'     => $sld,
                    'TLD'     => $tld,
                    'Service' => 'WPPS',
                ]));

                if ($purchaseXml !== null && isset($purchaseXml->GetWhoisGuardInfo->WhoisGuardList->WhoisGuard)) {
                    foreach ($purchaseXml->GetWhoisGuardInfo->WhoisGuardList->WhoisGuard as $wg) {
                        $wgId = (string) ($wg->WGID ?? '');
                        break;
                    }
                }
            }

            if (! $wgId) {
                return DomainResult::failure('WhoisGuard not available for this domain.');
            }

            $xml = $this->call(array_merge($this->baseParams('SetWhoisGuardEnabled'), [
                'WhoisGuardID' => $wgId,
                'IsEnabled'    => $enabled ? '1' : '0',
            ]));

            return $this->parseResponse($xml, 'setPrivacy');
        } catch (\Throwable $e) {
            Log::error('Enom setPrivacy failed', ['domain' => $domain->domain_name, 'error' => $e->getMessage()]);
            return DomainResult::failure($e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Test Connection
    // ------------------------------------------------------------------ //

    public function testConnection(): DomainResult
    {
        try {
            if (empty($this->uid) || empty($this->pw)) {
                return DomainResult::failure('Enom credentials not configured.');
            }

            $xml = $this->call($this->baseParams('Hello'));

            if ($xml === null) {
                return DomainResult::failure('No response from Enom API.');
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
        $params['responsetype'] = 'xml';

        $response = Http::timeout(30)->get($this->baseUrl, $params);

        if (! $response->successful()) {
            Log::error('Enom HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        $body = $response->body();

        // Suppress XML warnings from malformed responses
        $prev = libxml_use_internal_errors(true);
        $xml  = simplexml_load_string($body);
        libxml_use_internal_errors($prev);

        if ($xml === false) {
            Log::error('Enom XML parse error', ['body' => substr($body, 0, 500)]);
            return null;
        }

        return $xml;
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function baseParams(string $command): array
    {
        return [
            'uid'     => $this->uid,
            'pw'      => $this->pw,
            'command' => $command,
        ];
    }

    private function isError(SimpleXMLElement $xml): bool
    {
        $errCount = (int) ($xml->ErrCount ?? 0);
        $rrpCode  = (string) ($xml->RRPCode ?? '200');

        return $errCount > 0 || (! in_array($rrpCode, ['', '200', '201']));
    }

    private function errorMessage(SimpleXMLElement $xml): string
    {
        if (isset($xml->errors->Err1)) {
            return (string) $xml->errors->Err1;
        }
        if (isset($xml->RRPText)) {
            return (string) $xml->RRPText;
        }
        return 'Unknown Enom error.';
    }

    private function parseResponse(?SimpleXMLElement $xml, string $action): DomainResult
    {
        if ($xml === null) {
            return DomainResult::failure("API call failed for {$action}.");
        }

        if ($this->isError($xml)) {
            return DomainResult::failure($this->errorMessage($xml));
        }

        // Capture the order ID if present
        $orderId = (string) ($xml->OrderID ?? $xml->transferorderid ?? '');

        return DomainResult::success('', array_filter(['registrar_order_id' => $orderId]));
    }

    private function splitDomain(string $fullDomain): array
    {
        $parts = explode('.', $fullDomain, 2);
        return [$parts[0], $parts[1] ?? ''];
    }

    private function mapStatus(string $enom): string
    {
        return match (true) {
            str_contains($enom, 'active')    => 'active',
            str_contains($enom, 'expired')   => 'expired',
            str_contains($enom, 'transfer')  => 'pending',
            default                          => 'active',
        };
    }

    /**
     * Convert E.164 phone (+12025551234) to Enom format (+1.2025551234).
     * Inserts a dot after the country code using a best-effort prefix match.
     */
    private function formatPhone(string $e164): string
    {
        if (! str_starts_with($e164, '+')) {
            return $e164;
        }

        $digits = ltrim($e164, '+');

        // 1-digit country codes: 1 (NANP)
        if (str_starts_with($digits, '1')) {
            return '+1.' . substr($digits, 1);
        }

        // 2-digit country codes (sample): 20,27,30-39,40-49,51-59,60-69,70-79,81,82,84,86,90,91,92,93,94,95
        $cc2 = substr($digits, 0, 2);
        $twoDigit = ['20','27','30','31','32','33','34','35','36','37','38','39',
                     '40','41','42','43','44','45','46','47','48','49',
                     '51','52','53','54','55','56','57','58','59',
                     '60','61','62','63','64','65','66','81','82','84','86',
                     '90','91','92','93','94','95','98'];
        if (in_array($cc2, $twoDigit)) {
            return "+{$cc2}." . substr($digits, 2);
        }

        // Assume 3-digit country code for everything else
        $cc3 = substr($digits, 0, 3);
        return "+{$cc3}." . substr($digits, 3);
    }

    private function defaultNameservers(): array
    {
        return ['dns1.name-services.com', 'dns2.name-services.com'];
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

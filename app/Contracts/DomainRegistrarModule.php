<?php

namespace App\Contracts;

use App\Models\Domain;
use App\Registrar\DomainCheckResult;
use App\Registrar\DomainResult;

interface DomainRegistrarModule
{
    public function checkAvailability(string $sld, string $tld): DomainCheckResult;

    /** @return DomainCheckResult[] keyed by TLD */
    public function checkBulkAvailability(string $sld, array $tlds): array;

    public function register(Domain $domain, array $contacts, int $years): DomainResult;

    public function renew(Domain $domain, int $years): DomainResult;

    public function transfer(Domain $domain, string $eppCode, array $contacts): DomainResult;

    public function getDomainInfo(Domain $domain): DomainResult;

    public function updateNameservers(Domain $domain, array $nameservers): DomainResult;

    public function getEppCode(Domain $domain): DomainResult;

    public function setLock(Domain $domain, bool $locked): DomainResult;

    public function setPrivacy(Domain $domain, bool $enabled): DomainResult;

    public function testConnection(): DomainResult;
}

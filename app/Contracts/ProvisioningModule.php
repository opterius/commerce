<?php

namespace App\Contracts;

use App\Models\Service;
use App\Provisioning\ProvisioningResult;

interface ProvisioningModule
{
    public function createAccount(Service $service): ProvisioningResult;

    public function suspendAccount(Service $service): ProvisioningResult;

    public function unsuspendAccount(Service $service): ProvisioningResult;

    public function terminateAccount(Service $service): ProvisioningResult;

    public function getAccountInfo(Service $service): ProvisioningResult;

    public function testConnection(\App\Models\Server $server): ProvisioningResult;
}

<?php

namespace App\Contracts;

use App\Models\Server;
use App\Models\Service;
use App\Provisioning\ProvisioningResult;

interface ProvisioningModule
{
    // ── Module metadata (called statically on the class) ────────────────────

    /** Unique identifier used as the `type` value stored on the Server. */
    public static function moduleId(): string;

    /** Human-readable name shown on the provider card. */
    public static function moduleLabel(): string;

    /** One or two sentence description shown below the card when selected. */
    public static function moduleDescription(): string;

    /**
     * Credential fields this module needs.
     *
     * Each entry:
     *   name        – form field key inside credentials[]
     *   label       – shown as the input label
     *   type        – HTML input type (text, password, url, number)
     *   placeholder – optional placeholder text
     *   required    – bool
     *   secret      – bool; if true the value is never echoed back to the form
     *                 and an empty submission keeps the existing stored value
     */
    public static function moduleFields(): array;

    // ── Runtime provisioning actions ─────────────────────────────────────────

    public function createAccount(Service $service): ProvisioningResult;

    public function suspendAccount(Service $service): ProvisioningResult;

    public function unsuspendAccount(Service $service): ProvisioningResult;

    public function terminateAccount(Service $service): ProvisioningResult;

    public function getAccountInfo(Service $service): ProvisioningResult;

    public function testConnection(Server $server): ProvisioningResult;
}

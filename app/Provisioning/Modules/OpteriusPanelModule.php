<?php

namespace App\Provisioning\Modules;

use App\Contracts\ProvisioningModule;
use App\Models\Server;
use App\Models\Service;
use App\Provisioning\ProvisioningResult;
use Illuminate\Support\Facades\Http;

class OpteriusPanelModule implements ProvisioningModule
{
    public function createAccount(Service $service): ProvisioningResult
    {
        $server = $service->server;
        if (! $server) {
            return ProvisioningResult::failure('No server assigned to service.');
        }

        $payload = [
            'domain'   => $service->domain,
            'username' => $service->username,
            'package'  => $service->product?->provisioning_package,
            'email'    => $service->client?->email,
        ];

        $result = $this->request($server, 'POST', '/api/accounts', $payload);

        if ($result->success && isset($result->data['account_id'])) {
            $service->update(['panel_account_id' => $result->data['account_id']]);
        }

        return $result;
    }

    public function suspendAccount(Service $service): ProvisioningResult
    {
        $server = $service->server;
        if (! $server) {
            return ProvisioningResult::failure('No server assigned to service.');
        }

        return $this->request($server, 'POST', '/api/accounts/' . $service->panel_account_id . '/suspend', []);
    }

    public function unsuspendAccount(Service $service): ProvisioningResult
    {
        $server = $service->server;
        if (! $server) {
            return ProvisioningResult::failure('No server assigned to service.');
        }

        return $this->request($server, 'POST', '/api/accounts/' . $service->panel_account_id . '/unsuspend', []);
    }

    public function terminateAccount(Service $service): ProvisioningResult
    {
        $server = $service->server;
        if (! $server) {
            return ProvisioningResult::failure('No server assigned to service.');
        }

        return $this->request($server, 'DELETE', '/api/accounts/' . $service->panel_account_id, []);
    }

    public function getAccountInfo(Service $service): ProvisioningResult
    {
        $server = $service->server;
        if (! $server) {
            return ProvisioningResult::failure('No server assigned to service.');
        }

        return $this->request($server, 'GET', '/api/accounts/' . $service->panel_account_id, []);
    }

    public function testConnection(Server $server): ProvisioningResult
    {
        return $this->request($server, 'GET', '/api/ping', []);
    }

    private function request(Server $server, string $method, string $path, array $payload): ProvisioningResult
    {
        try {
            $timestamp = (string) time();
            $bodyJson  = empty($payload) ? '{}' : json_encode($payload);
            $signature = hash_hmac('sha256', $timestamp . $bodyJson, $server->api_token);

            $response = Http::timeout(15)
                ->withHeaders([
                    'X-Timestamp' => $timestamp,
                    'X-Signature' => $signature,
                    'Accept'      => 'application/json',
                ])
                ->$method(rtrim($server->api_url, '/') . $path, $payload ?: null);

            if ($response->successful()) {
                return ProvisioningResult::success('OK', $response->json() ?? []);
            }

            return ProvisioningResult::failure(
                'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
                $response->json() ?? []
            );
        } catch (\Throwable $e) {
            return ProvisioningResult::failure($e->getMessage());
        }
    }
}

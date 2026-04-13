<?php

namespace App\Gateways\Contracts;

class GatewayResult
{
    private function __construct(
        public readonly bool    $success,
        public readonly ?string $transactionId   = null,
        public readonly ?string $redirectUrl     = null,
        public readonly ?string $error           = null,
        public readonly array   $gatewayResponse = [],
    ) {}

    public static function success(string $transactionId, array $response = []): self
    {
        return new self(
            success:         true,
            transactionId:   $transactionId,
            gatewayResponse: $response,
        );
    }

    public static function redirect(string $url): self
    {
        return new self(success: false, redirectUrl: $url);
    }

    public static function failure(string $error, array $response = []): self
    {
        return new self(
            success:         false,
            error:           $error,
            gatewayResponse: $response,
        );
    }

    public function isRedirect(): bool
    {
        return $this->redirectUrl !== null;
    }
}

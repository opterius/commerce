<?php

namespace App\Registrar;

class DomainResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $message = '',
        public readonly array  $data    = [],
        public readonly string $error   = '',
    ) {}

    public static function success(string $message = '', array $data = []): static
    {
        return new static(true, $message, $data);
    }

    public static function failure(string $error, array $data = []): static
    {
        return new static(false, '', $data, $error);
    }
}

<?php

namespace App\Registrar;

class DomainCheckResult
{
    public function __construct(
        public readonly string $domain,
        public readonly string $tld,
        public readonly bool   $available,
        public readonly bool   $premium = false,
        public readonly ?int   $price   = null,
        public readonly string $error   = '',
    ) {}

    public static function available(string $domain, string $tld, bool $premium = false, ?int $price = null): static
    {
        return new static($domain, $tld, true, $premium, $price);
    }

    public static function unavailable(string $domain, string $tld): static
    {
        return new static($domain, $tld, false);
    }

    public static function error(string $domain, string $tld, string $message): static
    {
        return new static($domain, $tld, false, false, null, $message);
    }
}

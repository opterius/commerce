<?php

namespace App\Services;

class TotpService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random TOTP secret (16 bytes → ~26 base32 chars).
     */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(16));
    }

    /**
     * Standard base32 encode.
     */
    private function base32Encode(string $bytes): string
    {
        $encoded  = '';
        $byteLen  = strlen($bytes);
        $bitBuf   = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < $byteLen; $i++) {
            $bitBuf   = ($bitBuf << 8) | ord($bytes[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $encoded  .= self::BASE32_CHARS[($bitBuf >> $bitsLeft) & 0x1F];
            }
        }

        if ($bitsLeft > 0) {
            $encoded .= self::BASE32_CHARS[($bitBuf << (5 - $bitsLeft)) & 0x1F];
        }

        return $encoded;
    }

    /**
     * Standard base32 decode. Ignores padding.
     */
    private function base32Decode(string $base32): string
    {
        $base32   = strtoupper($base32);
        $decoded  = '';
        $bitBuf   = 0;
        $bitsLeft = 0;

        for ($i = 0, $len = strlen($base32); $i < $len; $i++) {
            $char = $base32[$i];

            if ($char === '=') {
                break;
            }

            $pos = strpos(self::BASE32_CHARS, $char);

            if ($pos === false) {
                continue;
            }

            $bitBuf   = ($bitBuf << 5) | $pos;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $decoded  .= chr(($bitBuf >> $bitsLeft) & 0xFF);
            }
        }

        return $decoded;
    }

    /**
     * Compute TOTP code for the given time step (defaults to current 30-second window).
     */
    public function getCode(string $secret, ?int $timeStep = null): string
    {
        if ($timeStep === null) {
            $timeStep = (int) floor(time() / 30);
        }

        $key     = $this->base32Decode($secret);
        $message = pack('N*', 0) . pack('N*', $timeStep);  // 8-byte big-endian

        $hash   = hash_hmac('sha1', $message, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $code = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
            (ord($hash[$offset + 3])  & 0xFF)
        ) % 1_000_000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code against current ± $window time steps.
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = trim($code);

        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        $currentStep = (int) floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->getCode($secret, $currentStep + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the otpauth URI for QR code generation.
     */
    public function getProvisioningUri(string $secret, string $accountName, string $issuer): string
    {
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => 6,
            'period'    => 30,
        ]);

        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Generate $count random 10-character alphanumeric backup codes.
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no ambiguous chars
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = '';
            for ($j = 0; $j < 10; $j++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Return sha256-hashed versions of the backup codes for storage.
     */
    public function hashBackupCodes(array $codes): array
    {
        return array_map(fn ($code) => hash('sha256', strtoupper(trim($code))), $codes);
    }

    /**
     * Verify and consume a backup code.
     * Returns updated hashed codes array (with the used code removed), or false if invalid.
     *
     * @param  array  $hashedCodes  Array of sha256-hashed backup codes from storage.
     * @param  string $inputCode    Raw code entered by user.
     * @return array|false
     */
    public function verifyAndConsumeBackupCode(array $hashedCodes, string $inputCode): array|false
    {
        $inputHash = hash('sha256', strtoupper(trim($inputCode)));

        foreach ($hashedCodes as $index => $stored) {
            if (hash_equals($stored, $inputHash)) {
                unset($hashedCodes[$index]);
                return array_values($hashedCodes);
            }
        }

        return false;
    }
}

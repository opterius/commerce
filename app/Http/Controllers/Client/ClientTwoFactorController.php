<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientTwoFactorController extends Controller
{
    public function __construct(private TotpService $totp) {}

    /**
     * Show the 2FA settings page.
     */
    public function show(Request $request)
    {
        $client = auth('client')->user();

        $provisioningUri = null;
        $sessionSecret   = $request->session()->get('client_two_factor_setup_secret');

        if ($sessionSecret) {
            $issuer = config('app.name', 'Opterius Commerce');
            $provisioningUri = $this->totp->getProvisioningUri($sessionSecret, $client->email, $issuer);
        }

        $backupCodes = $request->session()->pull('client_two_factor_backup_codes');

        return view('client.profile.two-factor', compact('client', 'provisioningUri', 'sessionSecret', 'backupCodes'));
    }

    /**
     * Begin 2FA setup.
     */
    public function enable(Request $request)
    {
        $client = auth('client')->user();

        if ($client->two_factor_confirmed_at) {
            return back()->with('error', '2FA is already enabled.');
        }

        $secret = $this->totp->generateSecret();
        $request->session()->put('client_two_factor_setup_secret', $secret);

        return redirect()->route('client.two-factor.show');
    }

    /**
     * Confirm the TOTP code and activate 2FA.
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $client = auth('client')->user();
        $secret = $request->session()->get('client_two_factor_setup_secret');

        if (! $secret) {
            return redirect()->route('client.two-factor.show')
                ->with('error', 'Session expired. Please start setup again.');
        }

        if (! $this->totp->verify($secret, $validated['code'])) {
            return back()->withErrors(['code' => 'Invalid code. Please check your authenticator app and try again.']);
        }

        $plainCodes  = $this->totp->generateBackupCodes();
        $hashedCodes = $this->totp->hashBackupCodes($plainCodes);

        $client->update([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($hashedCodes)),
            'two_factor_confirmed_at'   => now(),
        ]);

        $request->session()->forget('client_two_factor_setup_secret');
        $request->session()->put('client_two_factor_backup_codes', $plainCodes);

        return redirect()->route('client.two-factor.show')
            ->with('status', '2FA has been enabled. Save your backup codes somewhere safe.');
    }

    /**
     * Disable 2FA (requires password confirmation).
     */
    public function disable(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $client = auth('client')->user();

        if (! Hash::check($validated['password'], $client->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $client->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        return redirect()->route('client.two-factor.show')
            ->with('status', '2FA has been disabled.');
    }

    /**
     * Regenerate backup codes (requires password confirmation).
     */
    public function regenerateCodes(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $client = auth('client')->user();

        if (! Hash::check($validated['password'], $client->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        if (! $client->two_factor_confirmed_at) {
            return back()->with('error', '2FA is not enabled.');
        }

        $plainCodes  = $this->totp->generateBackupCodes();
        $hashedCodes = $this->totp->hashBackupCodes($plainCodes);

        $client->update([
            'two_factor_recovery_codes' => encrypt(json_encode($hashedCodes)),
        ]);

        $request->session()->put('client_two_factor_backup_codes', $plainCodes);

        return redirect()->route('client.two-factor.show')
            ->with('status', 'Backup codes regenerated. Save them now — they won\'t be shown again.');
    }
}

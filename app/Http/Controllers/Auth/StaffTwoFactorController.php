<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffTwoFactorController extends Controller
{
    public function __construct(private TotpService $totp) {}

    /**
     * Show the 2FA settings page.
     */
    public function show(Request $request)
    {
        $staff = auth('staff')->user();

        $provisioningUri = null;
        $sessionSecret   = $request->session()->get('two_factor_setup_secret');

        if ($sessionSecret) {
            $issuer = config('app.name', 'Opterius Commerce');
            $provisioningUri = $this->totp->getProvisioningUri($sessionSecret, $staff->email, $issuer);
        }

        $backupCodes = $request->session()->pull('two_factor_backup_codes');

        return view('admin.profile.two-factor', compact('staff', 'provisioningUri', 'sessionSecret', 'backupCodes'));
    }

    /**
     * Begin 2FA setup — generate secret, store in session, redirect to show (QR step).
     */
    public function enable(Request $request)
    {
        $staff = auth('staff')->user();

        if ($staff->two_factor_confirmed_at) {
            return back()->with('error', '2FA is already enabled.');
        }

        $secret = $this->totp->generateSecret();
        $request->session()->put('two_factor_setup_secret', $secret);

        return redirect()->route('staff.two-factor.show');
    }

    /**
     * Confirm the TOTP code and activate 2FA.
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $staff  = auth('staff')->user();
        $secret = $request->session()->get('two_factor_setup_secret');

        if (! $secret) {
            return redirect()->route('staff.two-factor.show')
                ->with('error', 'Session expired. Please start setup again.');
        }

        if (! $this->totp->verify($secret, $validated['code'])) {
            return back()->withErrors(['code' => 'Invalid code. Please check your authenticator app and try again.']);
        }

        $plainCodes  = $this->totp->generateBackupCodes();
        $hashedCodes = $this->totp->hashBackupCodes($plainCodes);

        $staff->update([
            'two_factor_secret'          => encrypt($secret),
            'two_factor_recovery_codes'  => encrypt(json_encode($hashedCodes)),
            'two_factor_confirmed_at'    => now(),
        ]);

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->put('two_factor_backup_codes', $plainCodes);

        return redirect()->route('staff.two-factor.show')
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

        $staff = auth('staff')->user();

        if (! Hash::check($validated['password'], $staff->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $staff->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        return redirect()->route('staff.two-factor.show')
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

        $staff = auth('staff')->user();

        if (! Hash::check($validated['password'], $staff->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        if (! $staff->two_factor_confirmed_at) {
            return back()->with('error', '2FA is not enabled.');
        }

        $plainCodes  = $this->totp->generateBackupCodes();
        $hashedCodes = $this->totp->hashBackupCodes($plainCodes);

        $staff->update([
            'two_factor_recovery_codes' => encrypt(json_encode($hashedCodes)),
        ]);

        $request->session()->put('two_factor_backup_codes', $plainCodes);

        return redirect()->route('staff.two-factor.show')
            ->with('status', 'Backup codes regenerated. Save them now — they won\'t be shown again.');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Services\TotpService;
use Illuminate\Http\Request;

class StaffTwoFactorChallengeController extends Controller
{
    public function __construct(private TotpService $totp) {}

    /**
     * Show the 2FA challenge form.
     */
    public function show(Request $request)
    {
        if (! $request->session()->has('two_factor_staff_id')) {
            abort(404);
        }

        return view('auth.staff-two-factor-challenge');
    }

    /**
     * Verify the submitted code and log the staff user in.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $staffId = $request->session()->get('two_factor_staff_id');

        if (! $staffId) {
            return redirect()->route('staff.login');
        }

        $staff = Staff::find($staffId);

        if (! $staff || ! $staff->two_factor_confirmed_at) {
            $request->session()->forget('two_factor_staff_id');
            return redirect()->route('staff.login');
        }

        $code   = trim($validated['code']);
        $secret = decrypt($staff->two_factor_secret);

        // Try TOTP first (6-digit)
        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($this->totp->verify($secret, $code)) {
                return $this->loginAndRedirect($request, $staff);
            }
        }

        // Try backup code
        $hashedCodes = json_decode(decrypt($staff->two_factor_recovery_codes), true) ?? [];
        $updated     = $this->totp->verifyAndConsumeBackupCode($hashedCodes, $code);

        if ($updated !== false) {
            $staff->update([
                'two_factor_recovery_codes' => encrypt(json_encode($updated)),
            ]);
            return $this->loginAndRedirect($request, $staff);
        }

        return back()->withErrors(['code' => 'Invalid code. Please try again.']);
    }

    private function loginAndRedirect(Request $request, Staff $staff)
    {
        auth('staff')->login($staff);

        $staff->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $request->session()->forget('two_factor_staff_id');
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\TotpService;
use Illuminate\Http\Request;

class ClientTwoFactorChallengeController extends Controller
{
    public function __construct(private TotpService $totp) {}

    /**
     * Show the 2FA challenge form.
     */
    public function show(Request $request)
    {
        if (! $request->session()->has('two_factor_client_id')) {
            abort(404);
        }

        return view('auth.client-two-factor-challenge');
    }

    /**
     * Verify the submitted code and log the client in.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $clientId = $request->session()->get('two_factor_client_id');

        if (! $clientId) {
            return redirect()->route('client.login');
        }

        $client = Client::find($clientId);

        if (! $client || ! $client->two_factor_confirmed_at) {
            $request->session()->forget('two_factor_client_id');
            return redirect()->route('client.login');
        }

        $code   = trim($validated['code']);
        $secret = decrypt($client->two_factor_secret);

        // Try TOTP first (6-digit)
        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($this->totp->verify($secret, $code)) {
                return $this->loginAndRedirect($request, $client);
            }
        }

        // Try backup code
        $hashedCodes = json_decode(decrypt($client->two_factor_recovery_codes), true) ?? [];
        $updated     = $this->totp->verifyAndConsumeBackupCode($hashedCodes, $code);

        if ($updated !== false) {
            $client->update([
                'two_factor_recovery_codes' => encrypt(json_encode($updated)),
            ]);
            return $this->loginAndRedirect($request, $client);
        }

        return back()->withErrors(['code' => 'Invalid code. Please try again.']);
    }

    private function loginAndRedirect(Request $request, Client $client)
    {
        auth('client')->login($client);

        $client->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $request->session()->forget('two_factor_client_id');
        $request->session()->regenerate();

        return redirect()->intended(route('client.dashboard'));
    }
}

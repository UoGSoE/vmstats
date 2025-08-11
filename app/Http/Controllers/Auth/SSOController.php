<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SSOController extends Controller
{
    public function login()
    {
        if (config('sso.enabled', true)) {
            return Socialite::driver('keycloak')
                ->with(['OAUTH2_PROXY_INSECURE_OIDC_ALLOW_UNVERIFIED_EMAIL' => true])
                ->redirect();
        }

        return view('auth.login');
    }

    /**
     * This is the fallback for local dev without needing to faff with SSO
     */
    public function doLocalLogin(Request $request)
    {
        if (config('sso.enabled', true)) {
            abort(403, 'SSO is enabled');
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (auth()->attempt($request->only('username', 'password'))) {
            return $this->getSuccessRedirect();
        }

        return redirect()->back()->withErrors(['username' => 'Invalid credentials']);
    }

    /**
     * This is the SSO callback
     */
    public function handleProviderCallback(): RedirectResponse
    {
        $ssoUser = Socialite::driver('keycloak')->user();

        if ($this->forbidsStudentsFromLoggingIn($ssoUser)) {
            Log::warning('Denying student login attempt', ['email' => $ssoUser->email]);
            abort(403, 'Students are not allowed to login');
        }

        $ssoDetails = $this->getSSODetails($ssoUser);

        $user = User::where('email', $ssoDetails['email'])->first();

        if ($this->onlyAdminsCanLogin($user)) {
            Log::warning('Denying login attempt by non-admin', ['email' => $ssoDetails['email']]);
            abort(403, 'Only admins can login');
        }

        if (!$user && $this->shouldCreateNewUsers()) {
            $user = $this->createUser($ssoDetails);
        }

        if (!$user) {
            Log::warning('Denying login attempt for unknown user', ['email' => $ssoDetails['email']]);
            abort(403, 'Authentication failed');
        }

        Auth::login($user, false);
        session()->regenerate();  // regenerate the session ID to prevent session fixation attacks

        return $this->getSuccessRedirect();
    }

    private function getSuccessRedirect(): RedirectResponse
    {
        return redirect()->intended(route('home'));
    }

    private function forbidsStudentsFromLoggingIn(\Laravel\Socialite\Contracts\User $ssoUser): bool
    {
        return $this->isStudent($ssoUser) && !config('sso.allow_students', true);
    }

    private function onlyAdminsCanLogin(?User $user): bool
    {
        return config('sso.admins_only', false) && (!$user || !$user->is_admin);
    }

    private function shouldCreateNewUsers(): bool
    {
        return config('sso.autocreate_new_users', false);
    }

    private function getSSODetails(\Laravel\Socialite\Contracts\User $ssoUser): array
    {
        return [
            'email' => strtolower(trim($ssoUser->email)),
            'username' => strtolower(trim($ssoUser->nickname)),
            'surname' => trim(data_get($ssoUser->user, 'family_name', '')),
            'forenames' => trim(data_get($ssoUser->user, 'given_name', '')),
            'is_staff' => $this->isStaff($ssoUser),
        ];
    }

    private function createUser(array $ssoDetails): User
    {
        return User::create([
            'password' => bcrypt(Str::random(64)),
            'username' => $ssoDetails['username'],
            'email' => $ssoDetails['email'],
            'surname' => $ssoDetails['surname'],
            'forenames' => $ssoDetails['forenames'],
            'is_staff' => $ssoDetails['is_staff'],
        ]);
    }

    private function isStudent(\Laravel\Socialite\Contracts\User $ssoUser): bool
    {
        return $this->looksLikeMatric($ssoUser->nickname);
    }

    private function isStaff(\Laravel\Socialite\Contracts\User $ssoUser): bool
    {
        return !$this->looksLikeMatric($ssoUser->nickname);
    }

    private function looksLikeMatric(string $username): bool
    {
        // Matric numbers are 7 digits (for now), followed by a letter
        return preg_match('/^[0-9]{7}[a-z]$/i', strtolower(trim($username))) === 1;
    }
}

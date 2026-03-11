<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        Log::info('=== LOGIN ATTEMPT START ===', [
            'tenant' => tenant()?->id,
            'db' => \DB::connection()->getDatabaseName(),
            'panel' => Filament::getCurrentOrDefaultPanel()?->getId(),
            'host' => request()->getHost(),
        ]);

        try {
            $this->rateLimit(5);
        } catch (\DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException $exception) {
            Log::warning('LOGIN: Rate limited');
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();
        Log::info('LOGIN: Form data', ['email' => $data['email'] ?? 'n/a']);

        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);

        Log::info('LOGIN: Guard info', [
            'guard_name' => $authGuard->getName(),
            'provider_class' => get_class($authProvider),
            'provider_model' => method_exists($authProvider, 'getModel') ? $authProvider->getModel() : 'n/a',
        ]);

        $user = $authProvider->retrieveByCredentials($credentials);

        Log::info('LOGIN: User retrieval', [
            'user_found' => $user ? 'YES' : 'NO',
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role ?? 'n/a',
            'db_after_retrieve' => \DB::connection()->getDatabaseName(),
        ]);

        if (! $user) {
            Log::warning('LOGIN: FAILED - User not found');
            $this->throwFailureValidationException();
        }

        $passwordValid = $authProvider->validateCredentials($user, $credentials);
        Log::info('LOGIN: Password validation', [
            'valid' => $passwordValid ? 'YES' : 'NO',
            'hash_check' => \Hash::check($credentials['password'], $user->password) ? 'YES' : 'NO',
        ]);

        if (! $passwordValid) {
            Log::warning('LOGIN: FAILED - Invalid password');
            $this->userUndertakingMultiFactorAuthentication = null;
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        // MFA check
        if (
            filled($this->userUndertakingMultiFactorAuthentication) &&
            (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
        ) {
            Log::info('LOGIN: MFA challenge in progress');
            if ($this->isMultiFactorChallengeRateLimited($user)) {
                return null;
            }
            $this->multiFactorChallengeForm->validate();
        } else {
            foreach (Filament::getMultiFactorAuthenticationProviders() as $mfaProvider) {
                if (! $mfaProvider->isEnabled($user)) {
                    continue;
                }
                Log::info('LOGIN: MFA required');
                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());
                if ($mfaProvider instanceof \Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook) {
                    $mfaProvider->beforeChallenge($user);
                }
                break;
            }

            if (filled($this->userUndertakingMultiFactorAuthentication)) {
                $this->multiFactorChallengeForm->fill();
                return null;
            }
        }

        $panel = Filament::getCurrentOrDefaultPanel();
        $canAccess = ($user instanceof FilamentUser) ? $user->canAccessPanel($panel) : true;

        Log::info('LOGIN: Panel access check', [
            'panel_id' => $panel?->getId(),
            'user_is_filament_user' => $user instanceof FilamentUser ? 'YES' : 'NO',
            'can_access_panel' => $canAccess ? 'YES' : 'NO',
        ]);

        $attemptResult = $authGuard->attemptWhen($credentials, function (Authenticatable $attemptUser) use ($panel) {
            $canAccess = true;
            if ($attemptUser instanceof FilamentUser) {
                $canAccess = $attemptUser->canAccessPanel($panel);
            }
            Log::info('LOGIN: attemptWhen callback', [
                'user_id' => $attemptUser->getAuthIdentifier(),
                'panel_id' => $panel?->getId(),
                'can_access' => $canAccess ? 'YES' : 'NO',
                'db_in_callback' => \DB::connection()->getDatabaseName(),
            ]);
            return $canAccess;
        }, $data['remember'] ?? false);

        Log::info('LOGIN: attemptWhen result', ['success' => $attemptResult ? 'YES' : 'NO']);

        if (! $attemptResult) {
            Log::warning('LOGIN: FAILED - attemptWhen returned false');
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        Log::info('=== LOGIN SUCCESS ===');
        session()->regenerate();

        return app(LoginResponse::class);
    }
}

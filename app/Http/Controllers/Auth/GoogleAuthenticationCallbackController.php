<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\AbTesting\AttachVisitorAssignmentsToUserAction;
use App\Application\User\AuthenticateWithSocialAccountAction;
use App\Application\User\Exceptions\SocialAuthenticationFailed;
use App\Domain\User\Dto\SocialAccountData;
use App\Domain\User\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetVisitorId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final class GoogleAuthenticationCallbackController extends Controller
{
    public function __construct(
        private readonly AuthenticateWithSocialAccountAction $authenticateWithSocialAccount,
        private readonly AttachVisitorAssignmentsToUserAction $attachAssignments,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless($this->isConfigured(), 404);

        try {
            $socialiteUser = Socialite::driver(SocialProvider::Google->value)->user();
            $user = $this->authenticateWithSocialAccount->execute($this->mapSocialiteUser($socialiteUser));
        } catch (Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign-in failed.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->attachAssignments->execute(
            visitorId: $request->cookie(SetVisitorId::COOKIE_NAME),
            userId: (int) $user->id,
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function mapSocialiteUser(SocialiteUser $user): SocialAccountData
    {
        $providerUserId = trim((string) $user->getId());

        if ($providerUserId === '') {
            throw new SocialAuthenticationFailed();
        }

        return new SocialAccountData(
            provider: SocialProvider::Google,
            providerUserId: $providerUserId,
            email: $user->getEmail(),
            name: $user->getName(),
            avatar: $user->getAvatar(),
        );
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}

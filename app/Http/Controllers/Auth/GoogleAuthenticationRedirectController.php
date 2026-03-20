<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\User\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

final class GoogleAuthenticationRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        abort_unless($this->isConfigured(), 404);

        /** @var RedirectResponse $response */
        $response = Socialite::driver(SocialProvider::Google->value)->redirect();

        return $response;
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}

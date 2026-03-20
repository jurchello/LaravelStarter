<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;
use App\Domain\AbTesting\Repositories\AbTestEventRepository;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AccessControl\Repositories\RoleRepository;
use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use App\Domain\Health\Services\DatabaseHealthChecker;
use App\Domain\Health\Services\QueueHealthChecker;
use App\Domain\Health\Services\RedisHealthChecker;
use App\Domain\I18n\TranslationLoader;
use App\Domain\Shared\Randomizer;
use App\Domain\Storage\Repositories\FileStorage;
use App\Domain\User\Repositories\SocialAccountRepository;
use App\Domain\User\Repositories\UserRepository;
use App\Models\User;
use App\Infrastructure\AbTesting\Persistence\EloquentAbTestAssignmentRepository;
use App\Infrastructure\AbTesting\Persistence\EloquentAbTestEventRepository;
use App\Infrastructure\AbTesting\Persistence\EloquentAbTestRepository;
use App\Infrastructure\AccessControl\Persistence\SpatieRoleRepository;
use App\Infrastructure\FeatureFlags\Pennant\PennantFeatureRuntime;
use App\Infrastructure\FeatureFlags\Persistence\EloquentFeatureFlagRepository;
use App\Infrastructure\Health\LaravelDatabaseHealthChecker;
use App\Infrastructure\Health\LaravelQueueHealthChecker;
use App\Infrastructure\Health\LaravelRedisHealthChecker;
use App\Infrastructure\I18n\PhpFileTranslationLoader;
use App\Infrastructure\Shared\PhpRandomizer;
use App\Infrastructure\Storage\LaravelFileStorage;
use App\Infrastructure\User\Persistence\EloquentSocialAccountRepository;
use App\Infrastructure\User\Persistence\EloquentUserRepository;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TranslationLoader::class, PhpFileTranslationLoader::class);
        $this->app->bind(AbTestRepository::class, EloquentAbTestRepository::class);
        $this->app->bind(AbTestAssignmentRepository::class, EloquentAbTestAssignmentRepository::class);
        $this->app->bind(AbTestEventRepository::class, EloquentAbTestEventRepository::class);
        $this->app->bind(RoleRepository::class, SpatieRoleRepository::class);
        $this->app->bind(FeatureFlagRepository::class, EloquentFeatureFlagRepository::class);
        $this->app->singleton(FeatureFlagRuntime::class, PennantFeatureRuntime::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(SocialAccountRepository::class, EloquentSocialAccountRepository::class);
        $this->app->bind(FileStorage::class, function ($app): LaravelFileStorage {
            return new LaravelFileStorage(
                filesystems: $app->make(\Illuminate\Contracts\Filesystem\Factory::class),
                disk: (string) config('storage.file_storage_disk'),
            );
        });
        $this->app->bind(Randomizer::class, PhpRandomizer::class);
        $this->app->bind(DatabaseHealthChecker::class, LaravelDatabaseHealthChecker::class);
        $this->app->bind(RedisHealthChecker::class, LaravelRedisHealthChecker::class);
        $this->app->bind(QueueHealthChecker::class, LaravelQueueHealthChecker::class);

        Scramble::ignoreDefaultRoutes();
    }

    public function boot(): void
    {
        Feature::resolveScopeUsing(function (): mixed {
            $request = $this->app->bound('request') ? $this->app->make('request') : null;

            if (! $request instanceof Request) {
                return null;
            }

            return $request->user() ?? $request->cookie(\App\Http\Middleware\SetVisitorId::COOKIE_NAME);
        });

        $this->app->make(FeatureFlagRuntime::class)->registerDefinitions();
        $this->registerRateLimiters();

        Gate::before(static fn (?User $user): ?bool => $user?->is_admin === true ? true : null);

        Gate::define('viewApiDocs', static function (?User $user): bool {
            if (! $user || ! $user->hasVerifiedEmail()) {
                return false;
            }

            return $user->can('docs.site.ui')
                || $user->can('docs.site.document')
                || $user->can('docs.admin.ui')
                || $user->can('docs.admin.document');
        });

        Scramble::registerApi('site', ['api_path' => 'api'])
            ->routes(static fn (Route $route): bool => str_starts_with($route->uri(), 'api/'))
            ->expose(
                ui: static fn (Router $router, $action) => $router->get('docs/site-api', $action)->name('docs.site.ui'),
                document: static fn (Router $router, $action) => $router->get('docs/site-api.json', $action)->name('docs.site.document'),
            );

        Scramble::registerApi('admin', ['api_path' => 'management/api'])
            ->routes(static fn (Route $route): bool => str_starts_with($route->uri(), 'management/api/'))
            ->expose(
                ui: static fn (Router $router, $action) => $router->get('docs/admin-api', $action)->name('docs.admin.ui'),
                document: static fn (Router $router, $action) => $router->get('docs/admin-api.json', $action)->name('docs.admin.document'),
            );
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('auth.login', static function (Request $request): Limit {
            $maxAttempts = max(1, (int) config('rate_limits.auth.login.max_attempts', 5));
            $decayMinutes = max(1, (int) config('rate_limits.auth.login.decay_minutes', 1));
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinutes($decayMinutes, $maxAttempts)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('auth.password-reset', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.auth.password_reset.decay_minutes', 1)),
            max(1, (int) config('rate_limits.auth.password_reset.max_attempts', 3)),
        )->by((string) $request->ip()));

        RateLimiter::for('auth.password-update', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.auth.password_update.decay_minutes', 1)),
            max(1, (int) config('rate_limits.auth.password_update.max_attempts', 5)),
        )->by((string) $request->ip()));

        RateLimiter::for('auth.verification-send', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.auth.verification_send.decay_minutes', 1)),
            max(1, (int) config('rate_limits.auth.verification_send.max_attempts', 3)),
        )->by((string) optional($request->user())->id ?: (string) $request->ip()));

        RateLimiter::for('auth.verification-verify', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.auth.verification_verify.decay_minutes', 1)),
            max(1, (int) config('rate_limits.auth.verification_verify.max_attempts', 10)),
        )->by((string) $request->route('id').'|'.$request->ip()));

        RateLimiter::for('auth.social', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.auth.social.decay_minutes', 1)),
            max(1, (int) config('rate_limits.auth.social.max_attempts', 20)),
        )->by((string) $request->ip()));

        RateLimiter::for('admin.search', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.admin.search.decay_minutes', 1)),
            max(1, (int) config('rate_limits.admin.search.max_attempts', 60)),
        )->by(((string) optional($request->user())->id) ?: (string) $request->ip()));

        RateLimiter::for('admin.mutation', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.admin.mutation.decay_minutes', 1)),
            max(1, (int) config('rate_limits.admin.mutation.max_attempts', 30)),
        )->by(((string) optional($request->user())->id) ?: (string) $request->ip()));

        RateLimiter::for('admin.impersonation', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.admin.impersonation.decay_minutes', 1)),
            max(1, (int) config('rate_limits.admin.impersonation.max_attempts', 10)),
        )->by(((string) optional($request->user())->id) ?: (string) $request->ip()));

        RateLimiter::for('site.ab-assign', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.site_api.ab_assign.decay_minutes', 1)),
            max(1, (int) config('rate_limits.site_api.ab_assign.max_attempts', 120)),
        )->by((string) $request->ip()));

        RateLimiter::for('site.ab-event', static fn (Request $request): Limit => Limit::perMinutes(
            max(1, (int) config('rate_limits.site_api.ab_event.decay_minutes', 1)),
            max(1, (int) config('rate_limits.site_api.ab_event.max_attempts', 120)),
        )->by((string) $request->ip()));
    }
}

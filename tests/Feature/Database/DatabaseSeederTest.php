<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_environment_creates_default_super_admin(): void
    {
        $this->app['env'] = 'local';

        app(DatabaseSeeder::class)->run();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        $this->assertSame(['Admin', 'Developer', 'Manager'], Role::query()->orderBy('name')->pluck('name')->all());
        $this->assertSame([], Role::findByName('Admin')->users()->pluck('users.id')->all());
    }

    public function test_production_environment_skips_default_super_admin_seed(): void
    {
        $this->app['env'] = 'production';

        app(DatabaseSeeder::class)->run();

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@example.com',
        ]);
        $this->assertSame(['Admin', 'Developer', 'Manager'], Role::query()->orderBy('name')->pluck('name')->all());
    }
}

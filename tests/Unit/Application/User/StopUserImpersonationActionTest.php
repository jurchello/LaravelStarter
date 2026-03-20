<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User;

use App\Application\User\Exceptions\CannotLeaveImpersonation;
use App\Application\User\StopUserImpersonationAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StopUserImpersonationActionTest extends TestCase
{
    use RefreshDatabase;

    private StopUserImpersonationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new StopUserImpersonationAction();
    }

    public function test_returns_verified_admin_to_restore_session(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $result = $this->action->execute($admin->id);

        $this->assertTrue($result->is($admin));
    }

    public function test_throws_when_impersonator_id_is_missing(): void
    {
        $this->expectException(CannotLeaveImpersonation::class);

        $this->action->execute(null);
    }
}

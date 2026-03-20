<?php

declare(strict_types=1);

namespace Tests\Unit\Application\User;

use App\Application\User\Exceptions\CannotImpersonateUser;
use App\Application\User\StartUserImpersonationAction;
use App\Domain\User\ValueObjects\ImpersonationSession;
use App\Models\User;
use Tests\TestCase;

final class StartUserImpersonationActionTest extends TestCase
{
    private StartUserImpersonationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new StartUserImpersonationAction();
    }

    public function test_creates_impersonation_session_for_verified_admin(): void
    {
        $admin = new User();
        $admin->forceFill([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $admin->id = 10;

        $target = new User();
        $target->forceFill([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'is_admin' => false,
        ]);
        $target->id = 20;

        $result = $this->action->execute($admin, $target);

        $this->assertInstanceOf(ImpersonationSession::class, $result);
        $this->assertSame(10, $result->impersonatorId);
        $this->assertSame('Admin User', $result->impersonatorName);
    }

    public function test_throws_for_same_user(): void
    {
        $admin = new User();
        $admin->forceFill([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $admin->id = 10;

        $this->expectException(CannotImpersonateUser::class);

        $this->action->execute($admin, $admin);
    }
}

<?php

declare(strict_types=1);

namespace App\Events\AdminPanel;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class FeatureFlagsChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly string $action,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.feature-flags');
    }

    public function broadcastAs(): string
    {
        return 'admin.feature-flags.changed';
    }

    /**
     * @return array{action: string}
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
        ];
    }
}

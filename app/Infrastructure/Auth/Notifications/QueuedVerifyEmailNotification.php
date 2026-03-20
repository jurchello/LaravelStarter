<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth\Notifications;

use App\Infrastructure\Shared\Enums\QueueName;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class QueuedVerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(QueueName::Email->resolve());
    }
}

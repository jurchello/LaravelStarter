<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Enums;

enum QueueName: string
{
    case Default = 'default';
    case Email = 'email';
    case Exports = 'exports';

    public function configKey(): string
    {
        return 'queue.queues.'.$this->value;
    }

    public function resolve(): string
    {
        return (string) config($this->configKey(), $this->value);
    }
}

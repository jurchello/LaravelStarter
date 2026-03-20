<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter;

final class JsonLogTap
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getLogger()->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter);
        }
    }
}

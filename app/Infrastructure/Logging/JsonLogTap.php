<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FormattableHandlerInterface;

final class JsonLogTap
{
    public function __invoke(IlluminateLogger $logger): void
    {
        foreach ($logger->getLogger()->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new JsonFormatter);
            }
        }
    }
}

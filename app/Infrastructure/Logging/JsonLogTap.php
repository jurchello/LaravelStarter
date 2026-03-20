<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Logger as MonologLogger;

final class JsonLogTap
{
    public function __invoke(IlluminateLogger $logger): void
    {
        /** @var MonologLogger $monolog */
        $monolog = $logger->getLogger();

        foreach ($monolog->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new JsonFormatter);
            }
        }
    }
}

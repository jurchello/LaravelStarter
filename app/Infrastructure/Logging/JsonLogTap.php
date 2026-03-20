<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Logger;

final class JsonLogTap
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new JsonFormatter);
            }
        }
    }
}

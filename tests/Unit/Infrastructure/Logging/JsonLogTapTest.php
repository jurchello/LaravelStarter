<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Logging;

use App\Infrastructure\Logging\JsonLogTap;
use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

final class JsonLogTapTest extends TestCase
{
    public function test_tap_applies_json_formatter_to_handlers(): void
    {
        $handler = new StreamHandler('php://memory');
        $logger = new Logger('test');
        $logger->pushHandler($handler);

        (new JsonLogTap)(new IlluminateLogger($logger));

        self::assertInstanceOf(JsonFormatter::class, $handler->getFormatter());
    }
}

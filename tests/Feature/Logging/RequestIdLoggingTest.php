<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class RequestIdLoggingTest extends TestCase
{
    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('logs/request-id-test.log');
        @unlink($this->logPath);

        config()->set('logging.default', 'single');
        config()->set('logging.channels.single.path', $this->logPath);

        Log::forgetChannel();
        Log::forgetChannel('single');

        Route::middleware('web')->get('/logging/request-id-probe', function (Request $request) {
            Log::info('Request id probe', [
                'probe' => true,
            ]);

            return response()->json([
                'request_id' => $request->attributes->get('request_id'),
            ]);
        });
    }

    protected function tearDown(): void
    {
        @unlink($this->logPath);

        parent::tearDown();
    }

    public function test_request_id_is_added_to_response_and_log_context(): void
    {
        $response = $this->getJson('/logging/request-id-probe');

        $response->assertOk();

        $requestId = $response->headers->get('X-Request-Id');

        self::assertIsString($requestId);
        self::assertNotSame('', $requestId);
        self::assertSame($requestId, $response->json('request_id'));

        $payload = $this->lastLogEntry();

        self::assertSame('Request id probe', $payload['message']);
        self::assertSame($requestId, $payload['context']['request_id']);
        self::assertTrue($payload['context']['probe']);
    }

    public function test_request_id_header_is_preserved_when_provided(): void
    {
        $response = $this->withHeaders([
            'X-Request-Id' => 'external-request-id',
        ])->getJson('/logging/request-id-probe');

        $response->assertOk();
        $response->assertHeader('X-Request-Id', 'external-request-id');
        $response->assertJsonPath('request_id', 'external-request-id');

        $payload = $this->lastLogEntry();

        self::assertSame('external-request-id', $payload['context']['request_id']);
    }

    /**
     * @return array<string, mixed>
     */
    private function lastLogEntry(): array
    {
        $contents = trim((string) file_get_contents($this->logPath));
        $lines = preg_split('/\R+/', $contents) ?: [];
        $lastLine = end($lines);

        self::assertIsString($lastLine);

        $decoded = json_decode($lastLine, true);

        self::assertIsArray($decoded);

        return $decoded;
    }
}

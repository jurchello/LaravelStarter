<?php

declare(strict_types=1);

namespace App\Http\Controllers\AbTesting;

use App\Application\AbTesting\AssignVariantAction;
use App\Application\AbTesting\TrackEventAction;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetVisitorId;
use App\Http\Requests\AbTesting\TrackEventRequest;
use App\Http\Resources\AbTesting\AssignedVariantResource;
use App\Http\Resources\AbTesting\TrackEventResultResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AbTestingController extends Controller
{
    public function __construct(
        private readonly AssignVariantAction $assignVariant,
        private readonly TrackEventAction $trackEvent,
    ) {}

    public function assign(Request $request, string $testSlug): JsonResponse
    {
        $visitorId = $request->cookie(SetVisitorId::COOKIE_NAME);

        if ($visitorId === null) {
            return (new AssignedVariantResource(['variant' => null]))->response();
        }

        $variant = $this->assignVariant->execute(
            testSlug: $testSlug,
            visitorId: $visitorId,
            userId: $request->user()?->id,
        );

        return (new AssignedVariantResource(['variant' => $variant]))->response();
    }

    public function event(TrackEventRequest $request): JsonResponse
    {
        $visitorId = $request->cookie(SetVisitorId::COOKIE_NAME);

        if ($visitorId === null) {
            return (new TrackEventResultResource(['ok' => false]))->response();
        }

        $this->trackEvent->execute(
            testSlug: $request->string('test')->value(),
            visitorId: $visitorId,
            event: $request->string('event')->value(),
            userId: $request->user()?->id,
        );

        return (new TrackEventResultResource(['ok' => true]))->response();
    }
}

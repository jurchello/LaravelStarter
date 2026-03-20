<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\User\StartUserImpersonationAction;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RedirectTargetResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AdminUserImpersonationApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly StartUserImpersonationAction $startImpersonation,
    ) {}

    public function __invoke(Request $request, User $user): JsonResponse
    {
        $session = $this->startImpersonation->execute($request->user(), $user);

        $request->session()->put($session->toArray());

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $this->respond([
            'redirect' => new RedirectTargetResource([
                'redirectTo' => route('dashboard', absolute: false),
            ]),
        ]);
    }
}

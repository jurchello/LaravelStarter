<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\User\Exceptions\CannotLeaveImpersonation;
use App\Application\User\StopUserImpersonationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LeaveImpersonationController extends Controller
{
    public function __construct(
        private readonly StopUserImpersonationAction $stopImpersonation,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        try {
            $impersonator = $this->stopImpersonation->execute($request->session()->get('impersonator_id'));
        } catch (CannotLeaveImpersonation) {
            abort(404);
        }

        Auth::guard('web')->login($impersonator);

        $request->session()->forget($this->stopImpersonation->clearImpersonationSessionKeys());
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}

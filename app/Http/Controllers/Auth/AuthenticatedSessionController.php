<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\AbTesting\AttachVisitorAssignmentsToUserAction;
use App\Domain\User\ValueObjects\ImpersonationSession;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetVisitorId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AttachVisitorAssignmentsToUserAction $attachAssignments,
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('auth.failed'),
                ]);
        }

        $request->session()->regenerate();
        $this->attachAssignments->execute(
            visitorId: $request->cookie(SetVisitorId::COOKIE_NAME),
            userId: (int) $request->user()->id,
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->forget(ImpersonationSession::keys());
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

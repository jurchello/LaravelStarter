<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\AbTesting\AttachVisitorAssignmentsToUserAction;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetVisitorId;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly AttachVisitorAssignmentsToUserAction $attachAssignments,
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()->create($attributes);

        event(new Registered($user));

        Auth::login($user);
        $this->attachAssignments->execute(
            visitorId: $request->cookie(SetVisitorId::COOKIE_NAME),
            userId: (int) $user->id,
        );

        return redirect(route('dashboard', absolute: false));
    }
}

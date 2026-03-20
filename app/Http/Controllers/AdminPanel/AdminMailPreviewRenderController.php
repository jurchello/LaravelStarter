<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Response;

final class AdminMailPreviewRenderController extends Controller
{
    public function __invoke(string $template): Response
    {
        $message = match ($template) {
            'verify-email' => (new VerifyEmail)->toMail($this->fakeUser()),
            'password-reset' => (new ResetPassword('preview-reset-token'))->toMail($this->fakeUser()),
            default => abort(404),
        };

        return response($this->renderMessage($message));
    }

    private function fakeUser(): User
    {
        $user = new User;
        $user->forceFill([
            'id' => 999999,
            'name' => 'Preview User',
            'email' => 'preview@example.com',
        ]);

        return $user;
    }

    private function renderMessage(Renderable $message): string
    {
        return (string) $message->render();
    }
}

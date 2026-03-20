<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\I18n\GetTranslationsAction;
use App\Http\Resources\I18n\TranslationsResource;
use Illuminate\Http\JsonResponse;

final class I18nController extends Controller
{
    public function __construct(
        private readonly GetTranslationsAction $action,
    ) {}

    public function index(): JsonResponse
    {
        return (new TranslationsResource(
            $this->action->execute(app()->getLocale())
        ))->response();
    }
}

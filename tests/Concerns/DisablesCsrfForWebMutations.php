<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

trait DisablesCsrfForWebMutations
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\I18n;

interface TranslationLoader
{
    /**
     * @return array{locale: string, dictionary: array<string, string>}
     */
    public function load(string $locale): array;
}
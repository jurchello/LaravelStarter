<?php

declare(strict_types=1);

namespace App\Application\I18n;

use App\Domain\I18n\TranslationLoader;

final readonly class GetTranslationsAction
{
    public function __construct(
        private readonly TranslationLoader $loader,
    ) {}

    /**
     * @return array{locale: string, dictionary: array<string, string>}
     */
    public function execute(string $locale): array
    {
        return $this->loader->load($locale);
    }
}

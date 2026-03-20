<?php

declare(strict_types=1);

namespace App\Infrastructure\I18n;

use App\Domain\I18n\TranslationLoader;
use Illuminate\Support\Facades\File;

final class PhpFileTranslationLoader implements TranslationLoader
{
    /**
     * @return array{locale: string, dictionary: array<string, string>}
     */
    public function load(string $locale): array
    {
        $langPath = lang_path($locale);
        $dictionary = [];

        if (File::isDirectory($langPath)) {
            foreach (File::files($langPath) as $file) {
                $group = $file->getFilenameWithoutExtension();
                $translations = require $file->getPathname();

                if (! is_array($translations)) {
                    continue;
                }

                foreach ($translations as $key => $value) {
                    if (is_string($value)) {
                        $dictionary["{$group}.{$key}"] = $value;
                    }
                }
            }
        }

        return ['locale' => $locale, 'dictionary' => $dictionary];
    }
}

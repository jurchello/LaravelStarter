<?php

declare(strict_types=1);

namespace Tests\Unit\I18n;

use App\Infrastructure\I18n\PhpFileTranslationLoader;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class PhpFileTranslationLoaderTest extends TestCase
{
    public function test_loads_translations_from_php_files(): void
    {
        $locale = 'en';
        $langPath = lang_path($locale);

        File::shouldReceive('isDirectory')->with($langPath)->andReturn(true);
        File::shouldReceive('files')->with($langPath)->andReturn([
            $this->fakeFile('messages.php', ['hello' => 'Hello']),
        ]);

        $loader = new PhpFileTranslationLoader();
        $result = $loader->load($locale);

        $this->assertSame('en', $result['locale']);
        $this->assertSame(['messages.hello' => 'Hello'], $result['dictionary']);
    }

    public function test_returns_empty_dictionary_when_lang_dir_missing(): void
    {
        File::shouldReceive('isDirectory')->andReturn(false);

        $loader = new PhpFileTranslationLoader();
        $result = $loader->load('en');

        $this->assertSame([], $result['dictionary']);
    }

    private function fakeFile(string $name, array $translations): object
    {
        $tmpPath = sys_get_temp_dir() . '/' . $name;
        file_put_contents($tmpPath, '<?php return ' . var_export($translations, true) . ';');

        return new class($name, $tmpPath) extends \SplFileInfo {
            public function __construct(
                private readonly string $filename,
                string $path,
            ) {
                parent::__construct($path);
            }

            public function getFilenameWithoutExtension(): string
            {
                return pathinfo($this->filename, PATHINFO_FILENAME);
            }
        };
    }
}
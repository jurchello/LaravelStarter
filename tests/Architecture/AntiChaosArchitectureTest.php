<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class AntiChaosArchitectureTest extends TestCase
{
    public function test_anti_chaos_document_exists(): void
    {
        $path = $this->projectPath('docs/architecture/anti-chaos.md');

        self::assertFileExists($path);
        self::assertStringContainsString('## Service placement', (string) file_get_contents($path));
        self::assertStringContainsString('## Use case contract', (string) file_get_contents($path));
        self::assertStringContainsString('## Entity identity rule', (string) file_get_contents($path));
    }

    public function test_admin_api_controllers_return_standard_envelope(): void
    {
        foreach ($this->phpFilesMatching('app/Http/Controllers/AdminPanel', '/ApiController\.php$/') as $file) {
            $contents = (string) file_get_contents($file);

            self::assertTrue(
                str_contains($contents, 'RespondsWithApiEnvelope')
                || str_contains($contents, 'ApiEnvelopeResource'),
                sprintf('Admin API controller %s must use the standard API envelope resource.', $file),
            );
        }
    }

    public function test_domain_has_no_forbidden_outer_layer_dependencies(): void
    {
        $forbidden = [
            'Illuminate\\',
            'App\\Application\\',
            'App\\Infrastructure\\',
            'App\\Http\\',
            'App\\Models\\',
        ];

        foreach ($this->phpFilesIn('app/Domain') as $file) {
            $contents = (string) file_get_contents($file);

            foreach ($forbidden as $namespace) {
                self::assertStringNotContainsString(
                    $namespace,
                    $contents,
                    sprintf('Domain file %s must not depend on %s', $file, $namespace),
                );
            }
        }
    }

    public function test_entities_have_explicit_identity(): void
    {
        foreach ($this->phpFilesMatching('app/Domain', '/\/Entities\/.+\.php$/') as $file) {
            $contents = (string) file_get_contents($file);

            self::assertMatchesRegularExpression(
                '/\$id\b/',
                $contents,
                sprintf('Entity file %s must declare explicit identity.', $file),
            );
        }
    }

    public function test_application_use_cases_are_actions_with_execute_method(): void
    {
        foreach ($this->phpFilesIn('app/Application') as $file) {
            if (str_contains($file, '/Exceptions/')) {
                continue;
            }

            self::assertStringEndsWith('Action.php', $file, sprintf('Application class %s must end with Action.php', $file));

            $class = $this->classFromPath($file);
            $reflection = new ReflectionClass($class);

            self::assertTrue($reflection->hasMethod('execute'), sprintf('Use case %s must define execute().', $class));
            self::assertTrue($reflection->getMethod('execute')->isPublic(), sprintf('Use case %s execute() must be public.', $class));
        }
    }

    public function test_php_service_classes_live_only_in_domain_service_directories(): void
    {
        foreach ($this->phpFilesMatching('app', '/Service\.php$/') as $file) {
            self::assertMatchesRegularExpression(
                '#/app/Domain/[^/]+/Services/[^/]+Service\.php$#',
                $file,
                sprintf('Service class %s must live under app/Domain/{Context}/Services.', $file),
            );
        }

        self::assertDirectoryDoesNotExist($this->projectPath('app/Services'));
    }

    public function test_dto_like_php_files_live_only_in_domain_dto_directories(): void
    {
        foreach ($this->phpFilesMatching('app', '/(Dto|Data)\.php$/') as $file) {
            self::assertMatchesRegularExpression(
                '#/app/Domain/[^/]+/Dto/[^/]+(Dto|Data)\.php$#',
                $file,
                sprintf('DTO-like class %s must live under app/Domain/{Context}/Dto.', $file),
            );
        }
    }

    public function test_blade_views_do_not_contain_inline_assets(): void
    {
        foreach ($this->filesMatching('resources/views', '/\.blade\.php$/') as $file) {
            $contents = (string) file_get_contents($file);

            self::assertDoesNotMatchRegularExpression('/<script\b/i', $contents, sprintf('Blade view %s must not contain <script>.', $file));
            self::assertDoesNotMatchRegularExpression('/<style\b/i', $contents, sprintf('Blade view %s must not contain <style>.', $file));
            self::assertDoesNotMatchRegularExpression('/style\s*=/i', $contents, sprintf('Blade view %s must not contain inline style attributes.', $file));
        }
    }

    public function test_admin_page_views_declare_data_admin_page(): void
    {
        foreach ($this->filesMatching('resources/views/admin-panel', '/\.blade\.php$/') as $file) {
            if (str_contains($file, '/layouts/') || str_contains($file, '/partials/')) {
                continue;
            }

            $contents = (string) file_get_contents($file);

            self::assertStringContainsString(
                'data-admin-page=',
                $contents,
                sprintf('Admin page view %s must declare data-admin-page.', $file),
            );
        }
    }

    public function test_admin_page_views_do_not_embed_json_payload_islands(): void
    {
        foreach ($this->filesMatching('resources/views/admin-panel', '/\.blade\.php$/') as $file) {
            $contents = (string) file_get_contents($file);

            self::assertStringNotContainsString('@json(', $contents, sprintf('Admin page view %s must not embed @json payload islands.', $file));
            self::assertStringNotContainsString('json_encode(', $contents, sprintf('Admin page view %s must not embed json_encode payload islands.', $file));
        }
    }

    public function test_admin_page_connectors_do_not_call_http_clients_directly(): void
    {
        foreach ($this->filesMatching('resources/js/pages/admin-panel', '/connect\.ts$/') as $file) {
            $contents = (string) file_get_contents($file);

            self::assertStringNotContainsString('webClient', $contents, sprintf('Admin page connector %s must not call webClient directly.', $file));
            self::assertStringNotContainsString('apiClient', $contents, sprintf('Admin page connector %s must not call apiClient directly.', $file));
            self::assertStringNotContainsString('axios', $contents, sprintf('Admin page connector %s must not call axios directly.', $file));
            self::assertStringNotContainsString('fetch(', $contents, sprintf('Admin page connector %s must not call fetch directly.', $file));
        }
    }

    public function test_json_api_controllers_use_resources_instead_of_response_json(): void
    {
        $patterns = [
            'app/Http/Controllers/AdminPanel' => '/ApiController\.php$/',
            'app/Http/Controllers/AbTesting' => '/\.php$/',
            'app/Http/Controllers/Health' => '/\.php$/',
        ];

        foreach ($patterns as $directory => $pattern) {
            foreach ($this->filesMatching($directory, $pattern) as $file) {
                $contents = (string) file_get_contents($file);

                self::assertStringNotContainsString(
                    'response()->json(',
                    $contents,
                    sprintf('JSON API controller %s must use resources instead of response()->json().', $file),
                );
            }
        }

        $i18nController = $this->projectPath('app/Http/Controllers/I18nController.php');

        self::assertStringNotContainsString(
            'response()->json(',
            (string) file_get_contents($i18nController),
            'I18nController must use resources instead of response()->json().',
        );
    }

    private function classFromPath(string $path): string
    {
        $relativePath = str_replace($this->projectPath('app/'), '', $path);

        return 'App\\'.str_replace(['/', '.php'], ['\\', ''], $relativePath);
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        return $this->filesMatching($directory, '/\.php$/');
    }

    /**
     * @return list<string>
     */
    private function phpFilesMatching(string $directory, string $pattern): array
    {
        return $this->filesMatching($directory, $pattern);
    }

    /**
     * @return list<string>
     */
    private function filesMatching(string $directory, string $pattern): array
    {
        $base = $this->projectPath($directory);

        if (! is_dir($base)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
        $files = [];

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $pathname = $file->getPathname();

            if (preg_match($pattern, $pathname) !== 1) {
                continue;
            }

            $files[] = $pathname;
        }

        sort($files);

        return $files;
    }

    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 2).'/'.ltrim($relativePath, '/');
    }
}

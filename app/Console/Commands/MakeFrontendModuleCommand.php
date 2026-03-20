<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class MakeFrontendModuleCommand extends Command
{
    protected $signature = 'make:frontend-module
                            {name : Module name in kebab-case (e.g. ideas, ai-report)}
                            {--dir=modules : Directory inside resources/js/ where the module will be created}
                            {--no-page : Skip generating a page connect.ts}';

    protected $description = 'Scaffold a frontend module: init.ts, module.ts, service.ts, state.ts, bootstrap.ts, README.md and optionally a page connect.ts';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $dir = rtrim((string) $this->option('dir'), '/');

        if (! preg_match('/^[a-z][a-z0-9-]*$/', $name)) {
            $this->error('Name must be kebab-case (e.g. ideas, ai-report).');

            return self::FAILURE;
        }

        $this->scaffoldModule($name, $dir);

        if (! $this->option('no-page')) {
            $this->scaffoldPage($name);
        }

        $this->newLine();
        $this->info("✓ Frontend module [{$name}] scaffolded in resources/js/{$dir}/{$name}/");
        $this->newLine();
        $this->warn('Next steps:');
        $this->line("  1. Implement logic in resources/js/{$dir}/{$name}/service.ts");
        $this->line("  2. Wire events in resources/js/{$dir}/{$name}/bootstrap.ts");
        $this->line("  3. Export public API from resources/js/{$dir}/{$name}/module.ts");

        if (! $this->option('no-page')) {
            $this->line("  4. Register the module in resources/js/pages/{$name}/connect.ts");
            $this->line("  5. Load connect.ts in your Blade view: @vite('resources/js/pages/{$name}/connect.ts')");
        }

        $this->line('  Remove any generated files you do not need.');

        return self::SUCCESS;
    }

    private function toPascalCase(string $kebab): string
    {
        return implode('', array_map('ucfirst', explode('-', $kebab)));
    }

    private function scaffoldModule(string $name, string $dir): void
    {
        $pascalName = $this->toPascalCase($name);

        foreach (['init', 'module', 'bootstrap'] as $file) {
            $this->makeFile(
                "resources/js/{$dir}/{$name}/{$file}.ts",
                "frontend.module.{$file}.stub",
                $name,
                $pascalName,
            );
        }

        foreach (['service', 'state'] as $file) {
            $this->makeFile(
                "resources/js/{$dir}/{$name}/{$file}.ts",
                "frontend.module.{$file}.stub",
                $name,
                $pascalName,
            );
        }

        $this->makeFile(
            "resources/js/{$dir}/{$name}/README.md",
            'frontend.module.readme.stub',
            $name,
            $pascalName,
        );
    }

    private function scaffoldPage(string $name): void
    {
        $this->makeFile(
            "resources/js/pages/{$name}/connect.ts",
            'frontend.page.connect.stub',
            $name,
        );
    }

    private function makeFile(string $relativePath, string $stub, string $name, string $pascalName = ''): void
    {
        $path = base_path($relativePath);

        if (file_exists($path)) {
            $this->line("  <fg=yellow>SKIP</> {$relativePath} (already exists)");

            return;
        }

        $stubPath = base_path("stubs/{$stub}");
        $content = file_get_contents($stubPath);
        $content = str_replace('{{ name }}', $name, $content);
        $content = str_replace('{{ Name }}', $pascalName ?: ucfirst($name), $content);

        $this->writeFile($path, $content);
        $this->line("  <fg=green>CREATE</> {$relativePath}");
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);
    }
}

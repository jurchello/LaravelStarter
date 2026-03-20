<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class MakeDomainCommand extends Command
{
    protected $signature = 'make:domain {name : The domain entity name in PascalCase (e.g. Idea)}';

    protected $description = 'Scaffold a full DDD entity: Domain, Application, Infrastructure, HTTP, Model, Migration, Tests';

    public function handle(): int
    {
        $name = (string) $this->argument('name');

        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            $this->error('Name must be PascalCase (e.g. Idea, AiReport).');

            return self::FAILURE;
        }

        $this->scaffoldDomain($name);
        $this->scaffoldInfrastructure($name);
        $this->scaffoldHttp($name);
        $this->scaffoldModel($name);
        $this->scaffoldMigration($name);
        $this->scaffoldTests($name);

        $this->newLine();
        $this->info("✓ Domain [{$name}] scaffolded successfully.");
        $this->newLine();
        $this->warn('Next steps:');
        $this->line('  1. Fill in DTO properties in App\\Domain\\'.$name.'\\Dto\\'.$name.'Dto');
        $this->line('  2. Add migration columns in the generated migration file');
        $this->line('  3. Create Actions manually in app/Application/'.$name.'/');
        $this->line('  4. Register binding in App\\Providers\\AppServiceProvider:');
        $this->line('       $this->app->bind(\\App\\Domain\\'.$name.'\\Repositories\\'.$name.'Repository::class,');
        $this->line('                         \\App\\Infrastructure\\'.$name.'\\Persistence\\Eloquent'.$name.'Repository::class);');
        $this->line('  5. Add routes in routes/site_api.php or routes/site_web.php');

        return self::SUCCESS;
    }

    private function scaffoldDomain(string $name): void
    {
        $this->makeFile(
            "app/Domain/{$name}/Entities/{$name}.php",
            'domain.entity.stub',
            $name,
        );

        $this->makeFile(
            "app/Domain/{$name}/Repositories/{$name}Repository.php",
            'domain.repository.stub',
            $name,
        );

        $this->makeFile(
            "app/Domain/{$name}/Dto/{$name}Dto.php",
            'domain.dto.stub',
            $name,
        );
    }

    private function scaffoldInfrastructure(string $name): void
    {
        $this->makeFile(
            "app/Infrastructure/{$name}/Persistence/Eloquent{$name}Repository.php",
            'infrastructure.repository.stub',
            $name,
        );
    }

    private function scaffoldHttp(string $name): void
    {
        $this->makeFile(
            "app/Http/Controllers/{$name}Controller.php",
            'http.controller.stub',
            $name,
        );

        $this->makeFile(
            "app/Http/Requests/{$name}Request.php",
            'http.request.stub',
            $name,
        );

        $this->makeFile(
            "app/Http/Resources/{$name}Resource.php",
            'http.resource.stub',
            $name,
        );
    }

    private function scaffoldModel(string $name): void
    {
        $path = base_path("app/Models/{$name}.php");

        if (file_exists($path)) {
            $this->line("  <fg=yellow>SKIP</> app/Models/{$name}.php (already exists)");

            return;
        }

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Models;

        use Illuminate\Database\Eloquent\Model;

        final class {$name} extends Model
        {
            protected \$guarded = [];
        }
        PHP;

        $this->writeFile($path, $content);
        $this->line("  <fg=green>CREATE</> app/Models/{$name}.php");
    }

    private function scaffoldMigration(string $name): void
    {
        $table = Str::snake(Str::plural($name));
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_{$table}_table.php";
        $path = base_path("database/migrations/{$filename}");

        $content = <<<PHP
        <?php

        declare(strict_types=1);

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('{$table}', function (Blueprint \$table) {
                    \$table->id();
                    // Add columns for the new aggregate here.
                    \$table->timestamps();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('{$table}');
            }
        };
        PHP;

        $this->writeFile($path, $content);
        $this->line("  <fg=green>CREATE</> database/migrations/{$filename}");
    }

    private function scaffoldTests(string $name): void
    {
        $this->makeFile(
            "tests/Unit/Domain/{$name}/{$name}Test.php",
            'test.unit.stub',
            $name,
        );

        $this->makeFile(
            "tests/Feature/{$name}/{$name}Test.php",
            'test.feature.stub',
            $name,
        );
    }

    private function makeFile(string $relativePath, string $stub, string $name): void
    {
        $path = base_path($relativePath);

        if (file_exists($path)) {
            $this->line("  <fg=yellow>SKIP</> {$relativePath} (already exists)");

            return;
        }

        $stubPath = base_path("stubs/{$stub}");
        $content = str_replace('{{ Name }}', $name, file_get_contents($stubPath));

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

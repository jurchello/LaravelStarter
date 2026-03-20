<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ab_tests', function (Blueprint $table): void {
            $table->string('distribution_mode', 20)
                ->default('manual')
                ->after('traffic_percent');
        });
    }

    public function down(): void
    {
        Schema::table('ab_tests', function (Blueprint $table): void {
            $table->dropColumn('distribution_mode');
        });
    }
};

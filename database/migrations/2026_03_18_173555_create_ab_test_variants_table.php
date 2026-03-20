<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ab_test_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ab_test_id')->constrained('ab_tests')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug'); // control|treatment or any custom slug
            $table->unsignedSmallInteger('weight')->default(1);
            $table->timestamps();

            $table->unique(['ab_test_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_test_variants');
    }
};

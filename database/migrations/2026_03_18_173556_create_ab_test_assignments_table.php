<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ab_test_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ab_test_id')->constrained('ab_tests')->cascadeOnDelete();
            $table->foreignId('ab_test_variant_id')->constrained('ab_test_variants')->cascadeOnDelete();
            $table->string('visitor_id')->comment('UUID from cookie');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');

            $table->unique(['ab_test_id', 'visitor_id']);
            $table->index('visitor_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_test_assignments');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ab_test_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ab_test_assignment_id')->constrained('ab_test_assignments')->cascadeOnDelete();
            $table->string('event');
            $table->timestamp('created_at');

            $table->index(['ab_test_assignment_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_test_events');
    }
};

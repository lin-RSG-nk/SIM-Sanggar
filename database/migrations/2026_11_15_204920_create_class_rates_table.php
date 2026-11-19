<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rates', function (Blueprint $table) {
            $table->id();

            $table->string('rate_code', 30)->unique();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->restrictOnDelete();

            $table->decimal('price', 12, 2);

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['class_id', 'effective_from']);
            $table->index(['class_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rates');
    }
};
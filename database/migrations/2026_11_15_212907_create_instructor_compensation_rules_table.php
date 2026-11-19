<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_compensation_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('instructor_id')
                ->constrained('instructors')
                ->restrictOnDelete();

            // NULL berarti aturan berlaku umum untuk instruktur
            $table->foreignId('class_id')
                ->nullable()
                ->constrained('classes')
                ->restrictOnDelete();

            // per_session atau percentage
            $table->string('calculation_type', 30);

            $table->decimal('fixed_amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            // pay_per_class, membership_allocation, combined
            $table->string('revenue_basis', 30)->nullable();

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['instructor_id', 'class_id', 'status'],
                'compensation_rules_lookup_index'
            );

            $table->index(
    		['effective_from', 'effective_until'],
    		'comp_rules_effective_dates_idx'
		);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_compensation_rules');
    }
};

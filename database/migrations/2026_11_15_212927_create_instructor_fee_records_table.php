<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_fee_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('instructor_id')
                ->constrained('instructors')
                ->restrictOnDelete();

            $table->foreignId('class_schedule_id')
                ->constrained('class_schedules')
                ->restrictOnDelete();

            $table->foreignId('compensation_rule_id')
                ->constrained('instructor_compensation_rules')
                ->restrictOnDelete();

            $table->date('session_date');

            // Snapshot metode honor saat perhitungan dilakukan
            $table->string('calculation_type', 30);

            $table->decimal('fixed_amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            // Snapshot sumber pendapatan
            $table->string('revenue_basis', 30)->nullable();

            $table->decimal('pay_per_class_revenue', 12, 2)->default(0);
            $table->decimal('membership_revenue', 12, 2)->default(0);
            $table->decimal('revenue_basis_amount', 12, 2)->default(0);

            $table->decimal('fee_amount', 12, 2)->default(0);

            // pending, approved, paid, void
            $table->string('status', 20)->default('pending');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Mencegah honor sesi yang sama tercatat dua kali
            $table->unique(
                ['instructor_id', 'class_schedule_id', 'session_date'],
                'instructor_fee_session_unique'
            );

            $table->index(['instructor_id', 'status']);
            $table->index('session_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_fee_records');
    }
};

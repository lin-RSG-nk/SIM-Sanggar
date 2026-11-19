<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_revenue_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_id')
                ->unique()
                ->constrained('attendances')
                ->restrictOnDelete();

            $table->foreignId('membership_id')
                ->constrained('memberships')
                ->restrictOnDelete();

            // Belum diisi sebelum metode alokasi ditentukan
            $table->decimal('allocated_amount', 12, 2)->nullable();

            // fixed_per_visit, package_prorata, manual, dan sebagainya
            $table->string('allocation_method', 40)->nullable();

            // pending, allocated, void
            $table->string('status', 20)->default('pending');

            $table->timestamp('allocated_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['membership_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_revenue_allocations');
    }
};

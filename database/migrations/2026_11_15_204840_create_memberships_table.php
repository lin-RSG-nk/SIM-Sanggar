<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();

            $table->string('membership_code', 40)->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('membership_package_id')
                ->constrained('membership_packages')
                ->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            // Menyimpan kuota saat membership dibeli
            $table->unsignedSmallInteger('total_quota')->nullable();
            $table->unsignedSmallInteger('remaining_quota')->nullable();

            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('class_schedule_id')
                ->constrained('class_schedules')
                ->restrictOnDelete();

            // Salah satu sumber pembayaran harus digunakan
            $table->foreignId('membership_id')
                ->nullable()
                ->constrained('memberships')
                ->restrictOnDelete();

            $table->foreignId('transaction_detail_id')
                ->nullable()
                ->constrained('transaction_details')
                ->restrictOnDelete();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('attendance_date');

            // present, late, excused
            $table->string('attendance_status', 20)->default('present');

            // manual, qr_code, member_id
            $table->string('check_in_method', 20)->default('manual');

            $table->timestamp('check_in_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Mencegah absensi ganda untuk jadwal dan tanggal sama
            $table->unique(
                ['member_id', 'class_schedule_id', 'attendance_date'],
                'attendance_member_schedule_date_unique'
            );

            $table->index('attendance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->restrictOnDelete();

            $table->foreignId('instructor_id')
                ->constrained('instructors')
                ->restrictOnDelete();

            // 1 = Senin, 2 = Selasa, ..., 7 = Minggu
            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->unsignedSmallInteger('capacity');
            $table->string('location', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['day_of_week', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};

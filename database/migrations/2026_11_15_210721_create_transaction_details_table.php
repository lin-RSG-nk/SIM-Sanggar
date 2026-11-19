<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->restrictOnDelete();

            // membership atau pay_per_class
            $table->string('item_type', 30);

            $table->foreignId('membership_package_id')
                ->nullable()
                ->constrained('membership_packages')
                ->restrictOnDelete();

            $table->foreignId('class_rate_id')
                ->nullable()
                ->constrained('class_rates')
                ->restrictOnDelete();

            $table->foreignId('class_schedule_id')
                ->nullable()
                ->constrained('class_schedules')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('quantity')->default(1);

            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};

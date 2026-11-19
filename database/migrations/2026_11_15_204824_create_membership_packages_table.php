<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_packages', function (Blueprint $table) {
            $table->id();

            $table->string('package_code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->decimal('price', 12, 2);
            $table->unsignedSmallInteger('validity_days');
            $table->unsignedSmallInteger('class_quota')->nullable();

            $table->string('status', 20)->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_packages');
    }
};
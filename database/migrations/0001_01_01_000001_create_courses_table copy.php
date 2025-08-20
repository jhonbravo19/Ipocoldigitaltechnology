<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_hours');
            $table->string('serial_prefix', 10)->unique();
            $table->unsignedInteger('serial_counter')->default(0);

            $table->string('manual_file_path', 500)->nullable();
            $table->string('card_back_file_path', 500)->nullable();
            $table->string('acta_template_file_path', 500)->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('serial_prefix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

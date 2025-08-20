<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_holder_id')->constrained('certificate_holders');
            $table->foreignId('course_id')->constrained('courses');
            $table->string('series_number', 100)->unique();
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('certificate_file_path', 500);
            $table->string('card_file_path', 500);
            $table->string('acta_file_path', 500)->nullable();
            $table->string('paquete_file_path', 500)->nullable();
            $table->enum('status', ['active', 'inactive', 'replaced'])->default('active');
            $table->foreignId('issued_by')->constrained('users');
            $table->string('status_reason')->nullable();
            $table->timestamps();

            $table->index('series_number');
            $table->index('issue_date');
            $table->index('expiry_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_holders', function (Blueprint $table) {
            $table->id();
            $table->string('first_names');
            $table->string('last_names');
            $table->enum('identification_type', ['CC', 'CE', 'PA']);
            $table->bigInteger('identification_number');
            $table->string('identification_place');
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->string('photo_path', 500)->nullable();

            $table->enum('has_drivers_license', ['SI', 'NO']);
            $table->enum('drivers_license_category', ['A1', 'A2', 'B1', 'B2','B3', 'C1', 'C2', 'C3'])->nullable();

            $table->timestamps();

            $table->unique(['identification_type', 'identification_number'], 'unique_identification');
            $table->index(['first_names', 'last_names']);
            $table->index('identification_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_holders');
    }
};

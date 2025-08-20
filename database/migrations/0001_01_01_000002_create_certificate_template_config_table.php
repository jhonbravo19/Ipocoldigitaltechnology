<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_template_config', function (Blueprint $table) {
            $table->id();
            $table->string('company_logo', 500)->nullable();
            $table->string('certificate_title');
            $table->text('intro_text')->nullable();
            $table->string('signature_1_image', 500)->nullable();
            $table->string('signature_1_name')->nullable();
            $table->string('signature_1_position')->nullable();
            $table->string('signature_2_image', 500)->nullable();
            $table->string('signature_2_name')->nullable();
            $table->string('signature_2_position')->nullable();
            $table->string('background_image', 500)->nullable();
            $table->string('carnet_background_image', 500)->nullable();
            $table->text('additional_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_template_config');
    }
};

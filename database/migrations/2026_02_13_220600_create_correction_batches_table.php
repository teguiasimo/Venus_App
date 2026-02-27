<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('correction_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('zip_path')->nullable();
            $table->boolean('is_processing')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_batches');
    }
};

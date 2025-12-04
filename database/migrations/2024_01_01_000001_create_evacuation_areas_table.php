<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evacuation_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('capacity');
            $table->integer('current_occupancy')->default(0);
            $table->enum('status', ['available', 'full', 'closed'])->default('available');
            $table->text('facilities')->nullable();
            $table->text('contact_number')->nullable();
            $table->enum('disaster_type', ['typhoon', 'earthquake', 'flood', 'all'])->default('all');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evacuation_areas');
    }
};

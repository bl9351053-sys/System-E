<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_predictions', function (Blueprint $table) {
    $table->id();
    $table->string('disaster_type');
    $table->text('description')->nullable(); 
    $table->enum('severity', ['low','medium','high','critical'])->nullable();
    $table->string('affected_areas')->nullable(); 
    $table->decimal('probability',5,2)->nullable(); 
    $table->decimal('latitude',10,8)->nullable(); 
    $table->decimal('longitude',11,8)->nullable(); 
    $table->string('location_name')->nullable(); 
    $table->integer('risk_level')->nullable(); 
    $table->integer('predicted_recovery_days')->nullable(); 
    $table->timestamp('predicted_date')->nullable(); 
    $table->timestamp('valid_until')->nullable(); 
    $table->timestamp('predicted_at')->nullable(); 
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); 
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_predictions');
    }
};

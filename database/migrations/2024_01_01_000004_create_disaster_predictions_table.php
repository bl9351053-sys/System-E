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
    $table->text('description')->nullable(); // admin
    $table->enum('severity', ['low','medium','high','critical'])->nullable();
    $table->string('affected_areas')->nullable(); // admin
    $table->decimal('probability',5,2)->nullable(); // admin
    $table->decimal('latitude',10,8)->nullable(); // resident
    $table->decimal('longitude',11,8)->nullable(); // resident
    $table->string('location_name')->nullable(); // resident
    $table->integer('risk_level')->nullable(); // resident
    $table->integer('predicted_recovery_days')->nullable(); // resident
    $table->timestamp('predicted_date')->nullable(); // admin
    $table->timestamp('valid_until')->nullable(); // admin
    $table->timestamp('predicted_at')->nullable(); // resident
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // admin
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_predictions');
    }
};

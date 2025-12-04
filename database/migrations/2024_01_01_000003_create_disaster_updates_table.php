<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_updates', function (Blueprint $table) {
    $table->id();
    $table->string('title')->nullable();          // admin
    $table->text('content')->nullable();          // admin
    $table->enum('type', ['emergency','warning','info','success'])->nullable(); // admin
    $table->string('disaster_type')->nullable();  // resident
    $table->text('description')->nullable();     // resident
    $table->enum('severity', ['low','moderate','high','critical'])->nullable();
    $table->string('source')->nullable();        // resident
    $table->decimal('latitude',10,8)->nullable();
    $table->decimal('longitude',11,8)->nullable();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // admin
    $table->timestamp('issued_at')->nullable(); // resident
    $table->timestamp('published_at')->nullable(); // admin
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_updates');
    }
};

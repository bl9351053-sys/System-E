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
    $table->string('title')->nullable();         
    $table->text('content')->nullable();          
    $table->enum('type', ['emergency','warning','info','success'])->nullable(); 
    $table->string('disaster_type')->nullable();  
    $table->text('description')->nullable();     
    $table->enum('severity', ['low','moderate','high','critical'])->nullable();
    $table->string('source')->nullable();        
    $table->decimal('latitude',10,8)->nullable();
    $table->decimal('longitude',11,8)->nullable();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); 
    $table->timestamp('issued_at')->nullable(); 
    $table->timestamp('published_at')->nullable(); 
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_updates');
    }
};

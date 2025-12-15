<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();

        
            $table->string('family_head_name');
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();

            $table->integer('total_members')->default(1);

            $table->foreignId('evacuation_area_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('checked_in_at')->nullable();   
            $table->timestamp('checked_out_at')->nullable(); 
            $table->timestamp('evacuated_at')->nullable();    

            $table->enum('status', ['evacuated', 'returned', 'pending', 'available', 'full'])->default('pending');

            $table->text('special_needs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};

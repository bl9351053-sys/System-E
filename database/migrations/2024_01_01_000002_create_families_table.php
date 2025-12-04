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

            // Family info
            $table->string('family_head_name'); // Formerly 'family_name' in resident system
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();

            // Members info
            $table->integer('total_members')->default(1); // Formerly 'number_of_members'

            // Evacuation info
            $table->foreignId('evacuation_area_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('checked_in_at')->nullable();   // Resident system
            $table->timestamp('checked_out_at')->nullable();  // Resident system
            $table->timestamp('evacuated_at')->nullable();    // Admin system

            // Status
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

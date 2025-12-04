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
        Schema::create('earthquakes', function (Blueprint $table) {
           
            $table->string('date');
            $table->integer('total_earthquakes');
            $table->float('avg_magnitude');
            $table->float('max_magnitude');
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earthquakes');
    }
};

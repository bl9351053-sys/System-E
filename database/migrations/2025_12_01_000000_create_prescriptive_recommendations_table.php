<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prescriptive_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable()->comment('e.g., allocation, shelter_recommendation');
            $table->json('payload')->nullable();
            $table->json('metrics')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prescriptive_recommendations');
    }
};

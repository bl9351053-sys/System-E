<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('evacuation_areas', 'disaster_type')) {
            Schema::table('evacuation_areas', function (Blueprint $table) {
                $table->enum('disaster_type', ['typhoon', 'earthquake', 'flood', 'all'])->default('all')->after('contact_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('evacuation_areas', 'disaster_type')) {
            Schema::table('evacuation_areas', function (Blueprint $table) {
                $table->dropColumn('disaster_type');
            });
        }
    }
};

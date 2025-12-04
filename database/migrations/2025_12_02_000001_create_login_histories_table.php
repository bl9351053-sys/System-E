<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: login history migration is handled by System-E_Admin because
        // authentication is centralized there and the shared DB is `userevac_db`.
    }

    public function down(): void
    {
        // No-op
    }
};

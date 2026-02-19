<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE requisitions
            MODIFY status
            ENUM('pending','reviewed','approved','funded','rejected')
            NOT NULL DEFAULT 'pending'
        ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE requisitions
                MODIFY status
                ENUM('pending','approved','rejected')
                NOT NULL DEFAULT 'pending'
            ");
        });
    }
};

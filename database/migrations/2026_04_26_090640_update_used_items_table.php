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
        Schema::table('used_items', function (Blueprint $table) {
            $table->foreignId('closing_stock_session_id')
                ->constrained('closing_stock_sessions','id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('used_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closing_stock_session_id');
        });
    }
};

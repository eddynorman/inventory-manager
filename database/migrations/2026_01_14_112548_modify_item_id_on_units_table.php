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
        Schema::table('units', function (Blueprint $table) {
            // Drop old foreign key first (important)
            $table->dropForeign(['item_id']);

            // Make item_id NOT NULL
            $table->unsignedBigInteger('item_id')->nullable(false)->change();

            // Re-add foreign key with cascade
            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->change();

            // Re-add foreign key without cascade (or restrict)
            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->nullOnDelete();
        });
    }
};

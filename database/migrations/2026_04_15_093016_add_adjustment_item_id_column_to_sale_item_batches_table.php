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
        Schema::table('sale_item_batches', function (Blueprint $table) {
            $table->foreignId('adjustment_item_id')->nullable()->constrained('stock_adjustment_items','id')->after('sale_item_kit_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_item_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjustment_item_id');
        });
    }
};

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
        Schema::create('asset_inventory_damaged_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('asset_inventory_items','id');
            $table->float('quantity');
            $table->decimal('average_unit_cost');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_inventory_damaged_items');
    }
};

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
        Schema::create('sale_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items','id');
            $table->foreignId('sale_item_kit_item_id')->nullable()->constrained('sale_item_kit_items','id');
            $table->foreignId('stock_batch_id')->constrained('stock_batches','id');
            $table->float('quantity');
            $table->decimal('unit_cost',10,2);
            $table->decimal('total');
            $table->string('type');
            $table->bigInteger('type_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_batches');
    }
};

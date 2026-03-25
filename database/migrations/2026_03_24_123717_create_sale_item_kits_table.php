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
        Schema::create('sale_item_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales','id');
            $table->foreignId('item_ki_id')->constrained('item_kits','id');
            $table->float('quantity');
            $table->decimal('cost_at_sale',10,2);
            $table->decimal('selling_price',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_kits');
    }
};

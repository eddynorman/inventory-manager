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
        Schema::table('supplier_order_items', function (Blueprint $table) {
            $table->float('received_quantity')->default(0)->change();
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->float('received_quantity')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_order_items', function (Blueprint $table) {
            //
        });
    }
};

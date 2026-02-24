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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price','total']);
            $table->integer('requested_quantity')->after('unit_id');
            $table->decimal('requested_unit_price',15,2)->after('quantity');
            $table->decimal('actual_unit_price',15,2)->after('requested_unit_price');
            $table->decimal('actual_total',15,2)->after('actual_unit_price');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_price');
            $table->decimal('total');
            $table->dropColumn(['requested_unit_price','actual_unit_price','actual_total','requested_quantity']);
        });
    }
};

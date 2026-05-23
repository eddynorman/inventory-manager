<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ASSET INVENTORY
        |--------------------------------------------------------------------------
        */

        Schema::table('asset_inventory_damaged_items', function (Blueprint $table) {
            $table->decimal('average_unit_cost', 18, 2)->change();
        });

        Schema::table('asset_inventory_items', function (Blueprint $table) {
            $table->decimal('average_unit_cost', 18, 2)->change();
            $table->decimal('initial_unit_cost', 18, 2)->change();
        });

        Schema::table('asset_inventory_purchases', function (Blueprint $table) {
            $table->decimal('total', 18, 2)->change();
        });

        Schema::table('asset_inventory_purchase_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | FINANCIALS
        |--------------------------------------------------------------------------
        */

        Schema::table('bankings', function (Blueprint $table) {
            $table->decimal('amount', 18, 2)->change();
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->decimal('balance', 18, 2)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 18, 2)->change();
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->decimal('cost', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | ITEMS / KITS
        |--------------------------------------------------------------------------
        */

        Schema::table('item_kits', function (Blueprint $table) {
            $table->decimal('selling_price', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | PURCHASES
        |--------------------------------------------------------------------------
        */

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('total_amount', 18, 2)->change();
        });

        Schema::table('receiving_items', function (Blueprint $table) {
            $table->decimal('total', 18, 2)->change();
            $table->decimal('unit_price', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | REQUISITIONS
        |--------------------------------------------------------------------------
        */

        Schema::table('requisitions', function (Blueprint $table) {
            $table->decimal('cost', 18, 2)->change();
            $table->decimal('fund_amount', 18, 2)->nullable()->change();
        });

        Schema::table('requisition_items', function (Blueprint $table) {
            $table->decimal('total', 18, 2)->change();
            $table->decimal('unit_price', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | SALES
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('balance', 18, 2)->change();
            $table->decimal('total_amount', 18, 2)->change();
            $table->decimal('total_paid', 18, 2)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('cost_at_sale', 18, 2)->change();
            $table->decimal('total', 18, 2)->change();
            $table->decimal('unit_price', 18, 2)->change();
        });

        Schema::table('sale_item_batches', function (Blueprint $table) {
            $table->decimal('total', 18, 2)->change();
            $table->decimal('unit_cost', 18, 2)->change();
        });

        Schema::table('sale_item_kits', function (Blueprint $table) {
            $table->decimal('cost_at_sale', 18, 2)->change();
            $table->decimal('selling_price', 18, 2)->change();
            $table->decimal('total', 18, 2)->change();
        });

        Schema::table('sale_item_kit_items', function (Blueprint $table) {
            $table->decimal('cost_at_sale', 18, 2)->change();
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->decimal('amount', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | STOCK
        |--------------------------------------------------------------------------
        */

        Schema::table('stock_batches', function (Blueprint $table) {
            $table->decimal('unit_cost', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | SUPPLIER ORDERS
        |--------------------------------------------------------------------------
        */

        Schema::table('supplier_order_items', function (Blueprint $table) {
            $table->decimal('requested_unit_price', 18, 2)->change();
        });

        Schema::table('transfer_to_receive_batches', function (Blueprint $table) {
            $table->decimal('unit_cost', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | UNITS
        |--------------------------------------------------------------------------
        */

        Schema::table('units', function (Blueprint $table) {
            $table->decimal('buying_price', 18, 2)->change();
            $table->decimal('selling_price', 18, 2)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | USED ITEMS
        |--------------------------------------------------------------------------
        */

        Schema::table('used_items', function (Blueprint $table) {
            $table->decimal('total_cost', 18, 2)->change();
        });
    }

    public function down(): void
    {
        //
    }
};

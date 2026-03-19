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
        Schema::table('requisition_items', function (Blueprint $table) {
            $table->float('quantity')->change();
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->float('quantity')->change();
            $table->float('received_quantity')->change();
            $table->float('requested_quantity')->change();
        });
        Schema::table('supplier_order_items', function (Blueprint $table) {
            $table->float('quantity')->change();
            $table->float('received_quantity')->change();
            $table->float('requested_quantity')->change();
        });
        Schema::table('receiving_items', function (Blueprint $table) {
            $table->float('quantity')->change();
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->float('quantity')->change();
        });
        Schema::table('issue_items', function (Blueprint $table) {
            $table->float('quantity')->change();
        });
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->float('quantity')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisition_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('received_quantity')->change();
            $table->integer('requested_quantity')->change();
        });
        Schema::table('supplier_order_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('received_quantity')->change();
            $table->integer('requested_quantity')->change();
        });
        Schema::table('receiving_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
        Schema::table('issue_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};

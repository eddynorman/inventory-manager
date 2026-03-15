<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            if (!Schema::hasColumn('receivings', 'supplier_order_id')) {
                $table->foreignId('supplier_order_id')->nullable()->constrained('supplier_orders','id')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            if (Schema::hasColumn('receivings', 'supplier_order_id')) {
                $table->dropConstrainedForeignId('supplier_order_id');
            }
        });
    }
};

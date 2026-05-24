<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_locations', function (Blueprint $table) {

            $table->index([
                'location_id',
                'item_id',
            ]);
        });

        Schema::table('items', function (Blueprint $table) {

            $table->index([
                'is_auto_tracked',
                'is_stock_item',
            ]);
        });

        Schema::table('closing_stock_sessions', function (Blueprint $table) {

            $table->index([
                'location_id',
                'date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('item_locations', function (Blueprint $table) {

            $table->dropIndex([
                'location_id',
                'item_id',
            ]);
        });

        Schema::table('items', function (Blueprint $table) {

            $table->dropIndex([
                'is_auto_tracked',
                'is_stock_item',
            ]);
        });

        Schema::table('closing_stock_sessions', function (Blueprint $table) {

            $table->dropIndex([
                'location_id',
                'date',
            ]);
        });
    }
};

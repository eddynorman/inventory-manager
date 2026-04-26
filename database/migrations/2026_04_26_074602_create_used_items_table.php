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
        Schema::create('used_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items','id');
            $table->foreignId('location_id')->constrained('locations','id');
            $table->foreignId('recorded_by')->constrained('users','id');
            $table->float('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('used_items');
    }
};

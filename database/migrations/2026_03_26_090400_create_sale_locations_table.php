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
        Schema::create('sale_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales','id')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations','id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_locations');
    }
};

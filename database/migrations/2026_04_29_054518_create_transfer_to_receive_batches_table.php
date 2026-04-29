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
        Schema::create('transfer_to_receive_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers','id');
            $table->foreignId('to_location')->constrained('locations','id');
            $table->boolean('is_received')->default(false);
            $table->foreignId('item_id')->constrained('items','id');
            $table->float('quantity');
            $table->decimal('unit_cost',10,2);
            $table->bigInteger('batch_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_to_receive_batches');
    }
};

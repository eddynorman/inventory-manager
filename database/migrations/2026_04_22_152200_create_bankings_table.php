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
        Schema::create('bankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account')->constrained('bank_accounts','id');
            $table->decimal('amount',10,2);
            $table->enum('type',['deposit','withdraw']);
            $table->string('receipt_path')->nullable();
            $table->text('description');
            $table->foreignId('recorded_by')->constrained('users','id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankings');
    }
};

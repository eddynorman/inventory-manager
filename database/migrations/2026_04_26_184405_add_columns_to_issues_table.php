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
        Schema::table('issues', function (Blueprint $table) {
            $table->enum('status',['pending','processed','rejected']);
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users','id');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users','id');
            $table->timestamp('rejected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('processed_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['status','processed_at','rejected_at','rejection_reason']);
        });
    }
};

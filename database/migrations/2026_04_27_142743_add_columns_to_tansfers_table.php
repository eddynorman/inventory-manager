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
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropColumn('transfer_date');
            $table->foreignId('issue_id')->after('id')->constrained('issues','id');
            $table->enum('status',['pending','received'])->default('pending');
            $table->foreignId('received_by')->nullable()->constrained('users','id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->date('transfer_date')->after('id');
            $table->dropConstrainedForeignId('issue_id');
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn('status');
        });
    }
};

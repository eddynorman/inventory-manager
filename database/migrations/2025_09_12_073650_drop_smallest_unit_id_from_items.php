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
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['smallest_unit_id']);
            $table->dropColumn('smallest_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('smallest_unit_id')->nullable()->after('id');
            $table->foreign('smallest_unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }
};

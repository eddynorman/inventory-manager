<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {

            // Add reviewed_on
            $table->timestamp('reviewed_on')
                  ->nullable()
                  ->after('reviewed_by');

        });

        // Convert date columns to timestamp (date + time)
        DB::statement("ALTER TABLE requisitions MODIFY date_requested TIMESTAMP NULL");
        DB::statement("ALTER TABLE requisitions MODIFY date_approved TIMESTAMP NULL");
        DB::statement("ALTER TABLE requisitions MODIFY funded_on TIMESTAMP NULL");
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {

            $table->dropColumn('reviewed_on');
        });

        DB::statement("ALTER TABLE requisitions MODIFY date_requested DATE NULL");
        DB::statement("ALTER TABLE requisitions MODIFY date_approved DATE NULL");
        DB::statement("ALTER TABLE requisitions MODIFY funded_on DATE NULL");
    }
};

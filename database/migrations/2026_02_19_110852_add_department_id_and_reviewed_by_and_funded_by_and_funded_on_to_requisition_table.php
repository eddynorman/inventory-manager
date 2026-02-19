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
        Schema::table('requisitions', function (Blueprint $table) {

            $table->foreignId('department_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('funded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('funded_on')
                ->nullable();

            $table->decimal('fund_amount', 15, 2)
                ->nullable()
                ->after('funded_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {

            $table->dropForeign(['department_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['funded_by']);

            $table->dropColumn([
                'department_id',
                'reviewed_by',
                'funded_by',
                'funded_on',
                'fund_amount',
            ]);
        });
    }

};

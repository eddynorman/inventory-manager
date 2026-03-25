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
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('sale_date');
            $table->renameColumn('user_id','created_by');
            $table->enum('status',['pending','completed','cancelled'])->after('created_by');
            $table->enum('type',['upfront','credit']);
            $table->enum('payment_status',['unpaid','partial','paid']);
            $table->decimal('total_amount',10,2)->default(0)->change();
            $table->decimal('total_paid',10,2)->default(0)->after('total_amount');
            $table->decimal('balance',10,2)->default(0)->after('total_paid');
            $table->timestamp('completed_at')->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('sale_date')->after('id');
            $table->renameColumn('created_by','user_id');
            $table->dropColumn(['completed_at','type','status','balance','total_paid']);
        });
    }
};

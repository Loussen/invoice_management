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
        Schema::table('companies', function (Blueprint $table) {
            $table->double('monthly_limit');
            $table->double('daily_limit');
            $table->double('max_limit');
            $table->dropColumn('payment_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('monthly_limit');
            $table->dropColumn('daily_limit');
            $table->dropColumn('max_limit');
        });
    }
};

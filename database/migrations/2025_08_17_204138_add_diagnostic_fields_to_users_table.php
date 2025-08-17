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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('diagnostics_used_this_month')->default(0)->after('email_notifications');
            $table->date('diagnostics_month_reset')->nullable()->after('diagnostics_used_this_month');
            $table->timestamp('last_diagnostic_at')->nullable()->after('diagnostics_month_reset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['diagnostics_used_this_month', 'diagnostics_month_reset', 'last_diagnostic_at']);
        });
    }
};

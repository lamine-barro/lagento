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
            $table->dropColumn([
                'profile_type',
                'company_name',
                'business_sector',
                'business_stage',
                'team_size',
                'monthly_revenue'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_type')->nullable()->after('phone');
            $table->string('company_name')->nullable()->after('verification_status');
            $table->string('business_sector')->nullable()->after('company_name');
            $table->string('business_stage')->nullable()->after('business_sector');
            $table->string('team_size')->nullable()->after('business_stage');
            $table->string('monthly_revenue')->nullable()->after('team_size');
        });
    }
};

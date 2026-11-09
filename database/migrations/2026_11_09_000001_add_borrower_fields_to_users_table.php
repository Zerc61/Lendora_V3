<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('cohort_year')->nullable()->after('lab_member_id');
            $table->string('study_concentration', 120)->nullable()->after('cohort_year');
            $table->string('dpa_name', 150)->nullable()->after('study_concentration');
            $table->string('dpa_nidn', 30)->nullable()->after('dpa_name');
            $table->unsignedSmallInteger('compliance_score')->default(100)->after('dpa_nidn');
            $table->text('two_factor_secret')->nullable()->after('compliance_score');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->boolean('email_otp_enabled')->default(false)->after('two_factor_confirmed_at');
            $table->boolean('whatsapp_opt_in')->default(true)->after('email_otp_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cohort_year',
                'study_concentration',
                'dpa_name',
                'dpa_nidn',
                'compliance_score',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'email_otp_enabled',
                'whatsapp_opt_in',
            ]);
        });
    }
};

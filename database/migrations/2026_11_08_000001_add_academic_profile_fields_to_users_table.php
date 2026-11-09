<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nim')) {
                $table->string('nim', 30)->nullable()->after('name');
            }

            if (! Schema::hasColumn('users', 'study_program')) {
                $table->string('study_program', 120)->nullable()->after('nim');
            }

            if (! Schema::hasColumn('users', 'semester')) {
                $table->unsignedTinyInteger('semester')->nullable()->after('study_program');
            }

            if (! Schema::hasColumn('users', 'lab_tier')) {
                $table->string('lab_tier', 20)->nullable()->after('semester');
            }

            if (! Schema::hasColumn('users', 'lab_member_id')) {
                $table->string('lab_member_id', 40)->nullable()->after('lab_tier');
            }

            if (! Schema::hasColumn('users', 'academic_verified_at')) {
                $table->timestamp('academic_verified_at')->nullable()->after('lab_member_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'semester',
                'lab_tier',
                'lab_member_id',
                'academic_verified_at',
            ]);
        });
    }
};

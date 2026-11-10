<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('borrower_id')
                ->constrained('courses')->nullOnDelete();
            $table->string('activity_type', 30)->nullable()->after('course_id');
            $table->foreignId('pickup_location_id')->nullable()->after('activity_type')
                ->constrained('pickup_locations')->nullOnDelete();
            $table->string('usage_location', 255)->nullable()->after('pickup_location_id');
            $table->string('supervisor_name', 150)->nullable()->after('usage_location');
            $table->string('supervisor_letter_path', 255)->nullable()->after('supervisor_name');
            $table->timestamp('sop_agreed_at')->nullable()->after('supervisor_letter_path');
            $table->timestamp('submitted_at')->nullable()->after('sop_agreed_at');
            $table->string('return_token', 64)->nullable()->unique()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropConstrainedForeignId('pickup_location_id');
            $table->dropColumn([
                'activity_type',
                'usage_location',
                'supervisor_name',
                'supervisor_letter_path',
                'sop_agreed_at',
                'submitted_at',
                'return_token',
            ]);
        });
    }
};

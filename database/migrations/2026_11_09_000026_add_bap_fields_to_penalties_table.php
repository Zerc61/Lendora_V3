<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->string('case_category', 60)->nullable()->after('damage_description');
            $table->text('chronology')->nullable()->after('case_category');
            $table->foreignId('maintenance_id')->nullable()->after('chronology')
                ->constrained('maintenances')->nullOnDelete();
            $table->unsignedTinyInteger('payment_terms_days')->default(7)->after('due_at');
            $table->json('photo_paths')->nullable()->after('payment_terms_days');
            $table->timestamp('escalated_at')->nullable()->after('photo_paths');
            $table->foreignId('escalated_by')->nullable()->after('escalated_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('maintenance_id');
            $table->dropConstrainedForeignId('escalated_by');
            $table->dropColumn([
                'case_category',
                'chronology',
                'payment_terms_days',
                'photo_paths',
                'escalated_at',
            ]);
        });
    }
};

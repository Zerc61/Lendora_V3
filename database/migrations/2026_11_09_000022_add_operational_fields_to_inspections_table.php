<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('code', 40)->nullable()->unique()->after('asset_id');
            $table->string('context', 30)->default('post_return')->after('inspector_id');
            $table->json('checklist')->nullable()->after('notes');
            $table->json('photo_paths')->nullable()->after('checklist');
            $table->unsignedTinyInteger('condition_score')->nullable()->after('photo_paths');
            $table->string('follow_up', 30)->nullable()->after('condition_score');
            $table->foreignId('maintenance_id')->nullable()->after('follow_up')
                ->constrained('maintenances')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('maintenance_id');
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'context',
                'checklist',
                'photo_paths',
                'condition_score',
                'follow_up',
            ]);
        });
    }
};

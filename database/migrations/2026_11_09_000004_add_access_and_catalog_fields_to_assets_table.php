<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('grade', 5)->nullable()->after('condition_status');
            $table->string('access_level', 30)->default('open')->after('grade');
            $table->unsignedTinyInteger('condition_score')->default(100)->after('access_level');
            $table->boolean('is_featured')->default(false)->after('condition_score');
            $table->string('manual_path', 255)->nullable()->after('is_featured');
            $table->decimal('rating_average', 3, 2)->nullable()->after('manual_path');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'grade',
                'access_level',
                'condition_score',
                'is_featured',
                'manual_path',
                'rating_average',
                'rating_count',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('code', 40)->nullable()->unique()->after('asset_id');
            $table->string('priority', 20)->default('normal')->after('status');
            $table->string('vendor', 120)->nullable()->after('assigned_to');
            $table->timestamp('due_at')->nullable()->after('started_at');
            $table->unsignedInteger('labor_cost')->default(0)->after('cost');
            $table->string('final_condition', 20)->nullable()->after('result_notes');
            $table->json('progress')->nullable()->after('final_condition');
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'priority',
                'vendor',
                'due_at',
                'labor_cost',
                'final_condition',
                'progress',
            ]);
        });
    }
};

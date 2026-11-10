<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->timestamp('issued_at')->nullable()->after('due_at');
            $table->foreignId('issued_by')->nullable()->after('issued_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issued_by');
            $table->dropColumn('issued_at');
        });
    }
};

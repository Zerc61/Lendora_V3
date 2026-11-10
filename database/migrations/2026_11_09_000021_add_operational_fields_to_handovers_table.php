<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handovers', function (Blueprint $table) {
            $table->string('code', 40)->nullable()->unique()->after('booking_id');
            $table->string('shift_label', 60)->nullable()->after('performed_by');
            $table->string('borrower_signature_path', 255)->nullable()->after('shift_label');
            $table->string('operator_signature_path', 255)->nullable()->after('borrower_signature_path');
            $table->json('photo_paths')->nullable()->after('operator_signature_path');
            $table->json('checklist')->nullable()->after('photo_paths');
            $table->timestamp('due_at')->nullable()->after('happened_at');
        });
    }

    public function down(): void
    {
        Schema::table('handovers', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'shift_label',
                'borrower_signature_path',
                'operator_signature_path',
                'photo_paths',
                'checklist',
                'due_at',
            ]);
        });
    }
};

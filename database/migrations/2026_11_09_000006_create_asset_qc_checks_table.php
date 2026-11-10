<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_qc_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at');
            $table->string('result', 20);
            $table->string('sensor_cleanliness', 120)->nullable();
            $table->string('shutter_accuracy', 120)->nullable();
            $table->string('firmware_version', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('certificate_path', 255)->nullable();
            $table->date('next_due_at')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_qc_checks');
    }
};

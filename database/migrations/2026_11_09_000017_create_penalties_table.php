<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('borrower_id')->constrained('users')->restrictOnDelete();
            $table->string('bap_number', 40)->unique();
            $table->timestamp('incident_at');
            $table->string('inspector_name', 150);
            $table->string('damage_description', 255);
            $table->unsignedInteger('repair_subtotal');
            $table->unsignedTinyInteger('subsidy_percent')->default(10);
            $table->unsignedInteger('subsidy_amount')->default(0);
            $table->unsignedInteger('admin_fee')->default(0);
            $table->unsignedInteger('total_amount');
            $table->string('status', 20)->default('waiting_payment');
            $table->timestamp('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->unsignedTinyInteger('recovery_step')->default(1);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['borrower_id', 'status']);
            $table->index('booking_id');
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};

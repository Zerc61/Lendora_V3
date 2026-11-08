<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('borrower_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('purpose', 255);
            $table->date('requested_start_date');
            $table->date('requested_end_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamp('pickup_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['requested_start_date', 'requested_end_date'], 'bookings_requested_range_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penalty_id')->constrained('penalties')->cascadeOnDelete();
            $table->foreignId('borrower_id')->constrained('users')->restrictOnDelete();
            $table->string('method', 30);
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('pending');
            $table->string('reference_number', 60)->nullable();
            $table->string('proof_path', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['penalty_id', 'status']);
            $table->index(['borrower_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

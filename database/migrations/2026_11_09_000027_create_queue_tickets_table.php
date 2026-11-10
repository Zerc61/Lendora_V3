<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('number', 10);
            $table->string('service_type', 20);
            $table->string('status', 20)->default('waiting');
            $table->string('counter', 10)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('nim', 30)->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->date('service_date');
            $table->timestamps();

            $table->unique(['organization_id', 'service_date', 'number']);
            $table->index(['organization_id', 'service_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};

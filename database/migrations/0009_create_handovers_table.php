<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete()->index();
            $table->string('type', 20);
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('happened_at');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('happened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handovers');
    }
};

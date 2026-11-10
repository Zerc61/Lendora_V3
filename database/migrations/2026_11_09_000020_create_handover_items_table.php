<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_id')->constrained('handovers')->cascadeOnDelete();
            $table->foreignId('booking_item_id')->constrained('booking_items')->restrictOnDelete();
            $table->string('condition', 20)->default('good');
            $table->json('accessories')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['handover_id', 'booking_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_items');
    }
};

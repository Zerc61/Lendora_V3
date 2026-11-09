<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('brand', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('available')->index();
            $table->string('condition_status', 20)->default('good');
            $table->date('purchase_date')->nullable();
            $table->unsignedInteger('purchase_price')->nullable();
            $table->string('location', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200);
            $table->string('category', 30);
            $table->string('excerpt', 255)->nullable();
            $table->text('body');
            $table->string('publisher_name', 150)->nullable();
            $table->timestamp('published_at');
            $table->boolean('is_pinned')->default(false);
            $table->string('attachment_path', 255)->nullable();
            $table->string('cta_label', 80)->nullable();
            $table->string('cta_url', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

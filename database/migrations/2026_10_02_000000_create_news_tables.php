<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->json('translations');
            $table->string('image_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_archive')->default(false);
            $table->timestamps();
        });
        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('caption')->nullable();
            $table->text('permalink');
            $table->text('image_url')->nullable();
            $table->timestamp('posted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_posts');
        Schema::dropIfExists('articles');
    }
};

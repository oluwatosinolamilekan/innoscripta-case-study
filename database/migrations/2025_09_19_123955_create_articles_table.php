<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('author')->nullable();
            $table->string('url');
            $table->string('url_to_image')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('published_at');
            $table->string('external_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            // Create index for faster searches
            $table->index(['published_at', 'source_id']);
            $table->index('category');
            
            // Only create fulltext index if not using SQLite
            if (config('database.default') !== 'sqlite') {
                $table->fulltext(['title', 'description']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

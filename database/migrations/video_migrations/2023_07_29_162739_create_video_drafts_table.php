<?php

use App\Enums\Audience;
use App\Enums\Visibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('video_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();

            $table->string('video_url')->nullable();

            $table->string('thumbnail_url')->nullable();
            $table->string('title')->default('Untitled Video');
            $table->text('description')->nullable();
            $table->text('tags')->nullable();
            $table->foreignId('category_id')->default(10)->constrained()->nullOnDelete();
            $table->string('language', 5)->nullable();
            $table->string('region', 3)->nullable();
            $table->enum('audience', array_map(fn($audience) => $audience->value, Audience::getAll()))->default(Audience::ALL->value);
            $table->enum('visibility', array_map(fn($visibility) => $visibility->value, Visibility::getAll()))->default(Visibility::PUBLIC->value);
            $table->timestamp('publish_time')->nullable();
            $table->boolean('use_publish_time')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_drafts');
    }
};

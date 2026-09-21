<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Free-form tags are replaced by the opportunity priority, so every tag
     * and its assignments (opportunities and the unused organization ones)
     * are dropped along with the tables.
     */
    public function up(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }

    /**
     * Reverse the migrations.
     *
     * Recreates the empty tables; the dropped tags are not restored.
     */
    public function down(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }
};

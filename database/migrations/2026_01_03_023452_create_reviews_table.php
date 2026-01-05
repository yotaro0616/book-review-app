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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // 誰が投稿したか
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // どの本に対するレビューか
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            // 5段階評価
            $table->integer('rating');
            // コメント
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーは書籍にレビューを投稿できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => '素晴らしい本でした！',
        ]);

        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
        $response->assertRedirect(route('books.show', $book));
    }

    public function test_評価が1から5の範囲外の場合は投稿できない_バリデーション()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 6, // 範囲外
            'comment' => 'テスト',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_他人のレビューは更新できない_認和()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => '勝手に更新',
        ]);

        $response->assertStatus(403);
    }

    public function test_レビュー更新のバリデーションと認可()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        // 正常更新
        $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 1,
            'comment' => '評価変えました',
        ])->assertRedirect(route('books.show', $review->book_id));

        // 認可失敗
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->put(route('reviews.update', $review), ['rating' => 5])->assertStatus(403);
    }

    public function test_レビュー削除の認可()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->delete(route('reviews.destroy', $review))->assertStatus(403);
        $this->actingAs($user)->delete(route('reviews.destroy', $review))->assertStatus(302);
    }

    public function test_レビュー投稿時のバリデーション詳細()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 評価が未入力
        $this->actingAs($user)->post(route('reviews.store', $book), ['comment' => 'テスト'])
            ->assertSessionHasErrors('rating');

        // 評価が整数ではない
        $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 'good'])
            ->assertSessionHasErrors('rating');

        // コメントが空（もしnullableなら成功、requiredなら失敗。要件に合わせて確認）
        $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 5, 'comment' => ''])
            ->assertRedirect(route('books.show', $book));
    }

    public function test_レビュー編集画面が表示される()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->get(route('reviews.edit', $review))->assertStatus(200);
    }
}

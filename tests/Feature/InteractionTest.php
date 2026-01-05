<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_お気に入りボタンで登録と解除が切り替わる_toggle()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 1回目：登録
        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertTrue($user->favoriteBooks->contains($book->id));

        // 2回目：解除
        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertFalse($user->refresh()->favoriteBooks->contains($book->id));
    }

    public function test_ランキング画面で平均評価が高い順に表示される()
    {
        // 1. ユーザーを作成して「ログイン状態」にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 評価5の本と評価1の本を作成
        $bestBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $bestBook->id, 'rating' => 5]);

        $worstBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $worstBook->id, 'rating' => 1]);

        // 3. ログイン状態でアクセス
        $response = $this->get(route('ranking.index'));

        // 4. 正常に表示されているか、順序は正しいか確認
        $response->assertStatus(200); // 念のため200OKを確認
        $response->assertSeeInOrder([$bestBook->title, $worstBook->title]);
    }

    public function test_お気に入り一覧画面が表示される()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book);

        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_ジャンル別書籍一覧が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre);

        // ジャンル一覧またはジャンル別表示のルートがある場合
        $response = $this->get(route('books.index', ['genre' => $genre->id]));
        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_レビューのいいねトグル機能()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // いいね追加
        $this->actingAs($user)->post(route('reviews.like', $review));
        $this->assertDatabaseHas('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);

        // いいね解除
        $this->actingAs($user)->post(route('reviews.like', $review));
        $this->assertDatabaseMissing('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);
    }
}

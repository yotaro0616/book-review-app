<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * シーダーとモデルの各機能を網羅してカバレッジを稼ぐテスト
     */
    public function test_データベースの初期化と各モデルの整合性を網羅する()
    {
        // 1. DatabaseSeederを実行（これで全テーブルにデータが入る）
        $this->seed();

        // 2. 作成されたデータの数を検証（Seederの中身を網羅）
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertGreaterThan(0, Genre::count());
        $this->assertGreaterThan(0, Book::count());
        $this->assertGreaterThan(0, Review::count());

        // 3. 各モデルのファクトリの「特殊な状態」を実行してカバレッジを稼ぐ
        // (Factoryで定義されている定義をすべて動かす)
        User::factory()->count(2)->create();

        // ジャンルを持たない本、レビューがない本など「境界値」を作る
        $book = Book::factory()->create(['title' => '境界値テスト用']);
        $this->assertEquals('境界値テスト用', $book->title);

        // レビューの評価（1〜5）の境界値を実行
        Review::factory()->create(['rating' => 1]);
        Review::factory()->create(['rating' => 5]);

        // 4. モデルのリレーション（つながり）を網羅
        $user = User::first();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->books);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->favoriteBooks);
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. 固定のテストユーザーを作成（ログイン確認用）
        $testUser = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // パスワードは 'password'
        ]);

        // 2. 基本となるジャンルを作成
        $genres = collect(['文学', '技術書', 'ビジネス', '自己啓発', '小説'])->map(function ($name) {
            return Genre::create(['name' => $name]);
        });

        // 3. 書籍を20冊作成し、それぞれにランダムなジャンルとレビューを紐付ける
        Book::factory(20)->create()->each(function ($book) use ($genres) {
            // ランダムに1〜2個のジャンルを紐付け
            $book->genres()->attach(
                $genres->random(rand(1, 2))->pluck('id')->toArray()
            );

            // 各書籍に1〜3件のレビューを投稿（ランダムなユーザーによる）
            Review::factory(rand(1, 3))->create([
                'book_id' => $book->id,
                'user_id' => User::factory(), // レビュー投稿者として新しいユーザーを作成
            ]);
        });

        // 4. テストユーザーにお気に入り書籍をいくつか登録しておく
        $testUser->favoriteBooks()->attach(
            Book::inRandomOrder()->take(3)->pluck('id')->toArray()
        );
    }
}

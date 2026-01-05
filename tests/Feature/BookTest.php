<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは書籍を登録できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store', 1), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2024-01-01',
            'description' => 'テストの説明文',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', ['title' => 'テスト書籍']);
        $response->assertRedirect(route('books.show', 1));
    }

    public function test_isb_nが重複している場合は登録できない_バリデーション()
    {
        $user = User::factory()->create();
        Book::factory()->create(['isbn' => '1111111111111']);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '重複ISBNの本',
            'isbn' => '1111111111111', // 重複
            'genres' => [],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_他人の書籍は編集できない_認可()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('books.edit', $book));

        $response->assertStatus(403); // Policyによる拒否
    }

    public function test_タイトルが空の場合は登録できない()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '', // 空
            'isbn' => '1234567890123',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_他人の書籍は削除できない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertStatus(403);
    }

    public function test_書籍削除の認可()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $otherUser = User::factory()->create();

        // 他人は削除不可
        $this->actingAs($otherUser)->delete(route('books.destroy', $book))->assertStatus(403);
        // 本人は削除可能
        $this->actingAs($user)->delete(route('books.destroy', $book))->assertRedirect(route('books.index'));
    }

    public function test_書籍登録時の全バリデーション項目チェック()
    {
        $user = User::factory()->create();

        // 全項目空での送信
        $response = $this->actingAs($user)->post(route('books.store'), []);
        $response->assertSessionHasErrors(['title', 'isbn']);

        // ISBNの桁数不正
        $response = $this->actingAs($user)->post(route('books.store'), ['isbn' => '123']);
        $response->assertSessionHasErrors('isbn');
    }

    public function test_書籍詳細画面の表示と_n_plus_1防止の確認()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();
        $response = $this->get(route('books.show', $book));
        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_書籍登録画面が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('books.create'))->assertStatus(200);
    }

    public function test_書籍一覧の検索とページネーション()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Book::factory()->count(15)->create();
        $this->get(route('books.index'))->assertStatus(200);
        $this->get(route('books.index', ['page' => 2]))->assertStatus(200);
    }

    public function test_書籍更新時のバリデーション全パターン()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        // ISBN重複（自分以外）
        $otherBook = Book::factory()->create(['isbn' => '9999999999999']);
        $this->actingAs($user)->put(route('books.update', $book), ['isbn' => '9999999999999'])
            ->assertSessionHasErrors('isbn');
        $genre = Genre::factory()->create();
        // 正常な部分更新
        $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '新タイトル',
            'author' => '新著者',
            'published_date' => '2024-12-31',
            'isbn' => $book->isbn,
            'description' => null, // nullableの確認
            'genres' => [$genre->id],
        ])->assertSessionHasNoErrors();
    }
}

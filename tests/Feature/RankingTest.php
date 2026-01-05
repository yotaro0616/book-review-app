<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ランキング画面が正常に表示され計算が正しい()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 評価の違う本を作成して計算ロジックを通す
        $book1 = Book::factory()->create();
        Review::factory()->create(['book_id' => $book1->id, 'rating' => 5]);

        $book2 = Book::factory()->create();
        Review::factory()->create(['book_id' => $book2->id, 'rating' => 1]);

        $response = $this->get(route('ranking.index'));
        $response->assertStatus(200);

        // withAvg, withCount が実行されていることを確認
        $response->assertSee($book1->title);
        $response->assertSee('5.0'); // 平均評価
    }
}

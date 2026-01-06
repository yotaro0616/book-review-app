<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookCollection;
use App\Http\Resources\V1\BookDetailResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得します。
     */
    public function index(Request $request): BookCollection
    {
        // 1ページあたりの件数をリクエストから取得（デフォルト20、最大100）
        $perPage = $request->integer('per_page', 20);
        if ($perPage > 100) {
            $perPage = 100;
        }

        // 書籍データを取得
        $books = Book::query()
            ->with(['genres']) // N+1問題を防ぐためにEagerロード
            ->withAvg('reviews', 'rating') // レビューの平均評価を計算
            ->withCount('reviews') // レビュー数を計算
            ->filter($request->only(['keyword', 'genre_id'])) // 検索スコープを適用
            ->paginate($perPage); // ページネーション

        // BookResourceを適用してJSONレスポンスを返す
        return new BookCollection($books);
    }

    /**
     * 書籍詳細を取得します。
     */
    public function show(Book $book): BookDetailResource
    {
        // 詳細情報に必要なリレーションをロード
        $book->load([
            'genres',
            'reviews.user',
        ]);

        // レビューの平均評価を計算
        $book->loadAvg('reviews', 'rating');

        // BookDetailResourceを適用してJSONレスポンスを返す
        return new BookDetailResource($book);
    }
}

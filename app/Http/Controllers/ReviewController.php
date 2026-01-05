<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * レビューの新規投稿
     */
    public function store(StoreReviewRequest $request, Book $book)
    {
        // $request->validated() で検証済みデータのみを取得
        $book->reviews()->create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビューの編集画面表示
     */
    public function edit(Review $review)
    {
        // 認可：自分のレビューかチェック
        $this->authorize('update', $review);

        // Bladeで $review->book->title を使っているため、bookをロードしておく
        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューの更新処理
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book_id)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューの削除
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $bookId = $review->book_id;
        $review->delete();

        return redirect()->route('books.show', $bookId)
            ->with('success', 'レビューを削除しました。');
    }
}

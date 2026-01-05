<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示
     */
    public function index()
    {
        // ページネーション（10件ずつ）と、紐付くジャンル情報を効率的に取得（Eager Loading）
        $books = Book::with('genres')->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍登録画面を表示
     */
    public function create()
    {
        // 登録画面のチェックボックス用に、全ジャンルを取得
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を保存
     */
    public function store(StoreBookRequest $request)
    {
        // 1. バリデーション済みデータを取得
        $validated = $request->validated();

        // 2. 書籍を保存（現在ログインしているユーザーのIDを紐付ける）
        // auth()->user()->books() を使うことで、user_id が自動的にセットされます
        $book = auth()->user()->books()->create($validated);

        // 3. ジャンルを中間テーブルに保存（attach を使用）
        $book->genres()->attach($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    public function show(Book $book)
    {
        // 起点Aをロード（本の詳細とレビュー情報を準備）
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);

        // 起点B：ログインしている場合のみ、ユーザーのお気に入り状況をロード
        if (auth()->check()) {
            auth()->user()->load('favoriteBooks');
        }

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集画面を表示
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        // フォームの選択肢として全ジャンルを取得
        $genres = Genre::all();

        // 編集画面では、現在選択されているジャンルを判定するために$bookに紐付くgenresをロードしておく
        $book->load('genres');

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        // 1. 認可チェック
        $this->authorize('update', $book);

        // 2. 書籍情報の更新
        $book->update($request->validated());

        // 3. ジャンル情報の更新（中間テーブルの同期）
        // sync() を使うことで、既存の紐付けを削除し、新しい配列で上書きします
        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    public function destroy(Book $book)
    {
        // 1. 認可チェック（一時的）
        $this->authorize('delete', $book);

        // 2. 削除実行
        // データベースの cascadeOnDelete 設定により、
        // 紐付くレビューや中間テーブルのデータも自動で削除されます
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}

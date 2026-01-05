<?php

namespace App\Http\Controllers;

use App\Models\Book;

class FavoriteController extends Controller
{
    public function index()
    {
        // 1. ログインユーザーのお気に入り書籍を取得
        // Blade側で $book->genres を使っているため、Eager Loading (with) を行います
        $books = auth()->user()->favoriteBooks()
            ->latest('favorites.created_at') // お気に入り登録が新しい順
            ->paginate(10);

        // 2. 変数名を $books にしてビューに渡す
        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        // ログインユーザーの favoriteBooks リレーションに対して toggle を実行
        // すでに中間テーブルに ID があれば削除、なければ追加してくれます
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back()->with('status', 'お気に入りを更新しました。');
    }
}

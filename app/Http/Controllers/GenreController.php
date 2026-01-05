<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示
     */
    public function index()
    {
        // ジャンルを、紐付く書籍数（withCount）と共に取得
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録画面を表示
     */
    public function create()
    {
        return view('genres.create');
    }

    /**
     * ジャンルを保存
     */
    public function store(StoreGenreRequest $request)
    {
        // バリデーション済みのデータを取得して保存
        Genre::create($request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを作成しました。');
    }

    /**
     * ジャンル詳細（そのジャンルの書籍一覧）を表示
     */
    public function show(Genre $genre)
    {
        // そのジャンルに紐付く書籍を、ジャンル情報と共にページネーションで取得
        $books = $genre->books()->with('genres')->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集画面を表示
     */
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    /**
     * ジャンルを削除
     */
    public function destroy(Genre $genre)
    {
        // 紐付く書籍がある場合は削除させない
        if ($genre->books()->exists()) { // count() > 0 より exists() の方が高速です
            // redirect() を追加して、リダイレクトオブジェクトに対してメソッドを呼ぶ
            return redirect()->route('genres.index')
                ->with('error', 'このジャンルには書籍が紐付いているため削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示
     */
    public function index()
    {
        // 1. クエリビルダーの初期化
        $query = Book::with('genres');

        // 2. キーワード検索条件を追加
        if ($keyword = request('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // 3. ジャンル絞り込み条件を追加
        if ($genreId = request('genre')) {
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // 4. 並び順ソート条件を追加
        $sort = request('sort', 'newest');
        match ($sort) {
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            'title' => $query->orderBy('title'),
            'rating' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            default => $query->latest(),
        };

        // 5. ページネーション
        $books = $query->paginate(10);

        // 6. ビューに渡すデータを準備
        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍一覧をCSVエクスポート
     */
    public function csvExport()
    {
        // 1. 検索条件に基づいてクエリを構築
        $query = Book::with('genres');

        // キーワード検索条件
        if ($keyword = request('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンル絞り込み条件
        if ($genreId = request('genre')) {
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // 2. StreamedResponse でファイルをストリーミング出力
        return response()->streamDownload(function () use ($query) {
            // ファイルポインタを開く（php://output は標準出力）
            $file = fopen('php://output', 'w');

            // BOM付きUTF-8を出力（Excelで日本語が正しく表示されるため）
            fwrite($file, "\xEF\xBB\xBF");

            // ヘッダー行を出力
            fputcsv($file, ['ID', 'タイトル', '著者', 'ISBN', '出版日', 'ジャンル', '登録日']);

            // データを1件ずつ取得して出力
            $query->cursor()->each(function ($book) use ($file) {

                // CSV行を出力
                fputcsv($file, [
                    $book->id,
                    $book->title,
                    $book->author,
                    $book->isbn,
                    $book->published_date->format('Y-m-d'),
                    $book->genres->pluck('name')->implode(', '),
                    $book->created_at->format('Y-m-d H:i:s'),
                ]);
            });

            // ファイルをクローズ
            fclose($file);
        }, 'books_'.now()->format('YmdHis').'.csv');
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
     * ISBN コードから Google Books API で書籍情報を検索
     */
    public function searchByIsbn($isbn)
    {
        // 1. ISBN コードのバリデーション（13桁の数字）
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json(['error' => '無効な ISBN コードです'], 400);
        }

        // 2. Google Books API にリクエスト
        try {
            $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
                'q' => 'isbn:'.$isbn,
                'key' => env('GOOGLE_BOOKS_API_KEY'),
            ]);

            // 3. レスポンスをチェック
            if ($response->failed()) {
                return response()->json(['error' => 'API リクエストに失敗しました'], 500);
            }

            $data = $response->json();

            // 4. 検索結果がない場合
            if (empty($data['items'])) {
                return response()->json(['error' => '書籍が見つかりません'], 404);
            }

            // 5. 最初の検索結果を取得
            $volumeInfo = $data['items'][0]['volumeInfo'];

            // 6. 必要な情報を抽出して返す
            return response()->json([
                'title' => $volumeInfo['title'] ?? '',
                'author' => $volumeInfo['authors'][0] ?? '',
                'published_date' => $volumeInfo['publishedDate'] ?? '',
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'エラーが発生しました: '.$e->getMessage()], 500);
        }
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

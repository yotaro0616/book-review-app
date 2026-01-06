<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use Illuminate\Support\Facades\Route;

/*
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return redirect()->route('books.index'); // トップに来たら書籍一覧へ
});

// 認証済みユーザーのみアクセス可能なルート群
Route::middleware(['auth'])->group(function () {

    // --- ジャンル管理 ---
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

    // --- 書籍管理 ---
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::get('/books/export', [BookController::class, 'csvExport'])->name('books.export');

    // ---  レビュー管理 ---
    Route::post('books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    // 編集・更新・削除：レビュー自身のID {review} で特定可能
    Route::get('reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // --- お気に入り・いいね ---
    Route::post('books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // お気に入り一覧画面
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // 特定のレビューに対して「いいね」をトグルする
    Route::post('reviews/{review}/like', [ReviewLikeController::class, 'toggle'])->name('reviews.like');
});

// --- 書籍閲覧 ---
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('ranking', [RankingController::class, 'index'])->name('ranking.index');

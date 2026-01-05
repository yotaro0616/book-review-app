<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        // 1. 平均評価を計算し、レビュー数とジャンルも同時に取得
        $rankedBooks = Book::withAvg('reviews as average_rating', 'rating')
            ->withCount('reviews as review_count')
            ->orderByDesc('average_rating') // エイリアス名で並び替え
            ->orderByDesc('review_count')    // エイリアス名で並び替え
            ->take(10)
            ->get();

        // 2. Bladeに渡す
        return view('ranking.index', compact('rankedBooks'));
    }
}

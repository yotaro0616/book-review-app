<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポートを表示します。
     */
    public function index(): View
    {
        $user = Auth::user();
        $reviews = $user->reviews()->with('book')->latest()->get();

        // 各統計データを計算
        $stats = [
            'total_books_read' => $this->calculateTotalBooksRead($reviews),
            'review_posting_days' => $this->calculateReviewPostingDays($reviews),
            'streaks' => $this->calculateStreaks($reviews),
            'monthly_stats' => $this->calculateMonthlyStats($reviews),
        ];

        // ビューにデータを渡す
        return view('reports.index', compact('stats'));
    }

    /**
     * 総読了冊数を計算します。
     */
    private function calculateTotalBooksRead(Collection $reviews): int
    {
        return $reviews->pluck('book_id')->unique()->count();
    }

    /**
     * レビュー投稿日数を計算します。
     */
    private function calculateReviewPostingDays(Collection $reviews): int
    {
        return $reviews->map(fn ($review) => $review->created_at->format('Y-m-d'))->unique()->count();
    }

    /**
     * 読書ストリーク（現在および最長）を計算します。
     */
    private function calculateStreaks(Collection $reviews): array
    {
        if ($reviews->isEmpty()) {
            return ['current' => 0, 'longest' => 0];
        }

        // レビュー投稿日を日付のコレクションとして取得し、重複を排除してソート
        $reviewDates = $reviews->map(fn ($review) => $review->created_at->startOfDay())->unique()->sort();

        // reduceを使用してストリークを計算
        $streaks = $reviewDates->slice(1)->reduce(function ($carry, $currentDate) {
            $previousDate = $carry['last_date'];
            if ($previousDate->diffInDays($currentDate) === 1) {
                $carry['current']++;
            } else {
                $carry['current'] = 1; // ストリークが途切れたらリセット
            }
            $carry['longest'] = max($carry['longest'], $carry['current']);
            $carry['last_date'] = $currentDate;

            return $carry;
        }, [
            'current' => 1,
            'longest' => 1,
            'last_date' => $reviewDates->first(),
        ]);

        // 現在のストリークが今日または昨日から続いているかを確認
        $isCurrentStreakActive = $reviewDates->last()->isToday() || $reviewDates->last()->isYesterday();

        return [
            'current' => $isCurrentStreakActive ? $streaks['current'] : 0,
            'longest' => $streaks['longest'],
        ];
    }

    /**
     * 月別統計を計算します。
     */
    private function calculateMonthlyStats(Collection $reviews): array
    {
        // レビューを月別にグループ化し、各月の統計を計算
        $monthlyData = $reviews->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map(fn ($group) => [
                'books_read' => $group->pluck('book_id')->unique()->count(),
                'reviews_posted' => $group->count(),
            ]);

        // 過去12ヶ月の範囲を生成し、データがない月は0で埋める
        return collect(range(0, 11))
            ->map(fn ($i) => Carbon::now()->subMonths($i)->format('Y-m'))
            ->reverse()
            ->map(fn ($month) => [
                'month' => $month,
                'books_read' => $monthlyData[$month]['books_read'] ?? 0,
                'reviews_posted' => $monthlyData[$month]['reviews_posted'] ?? 0,
            ])
            ->values()
            ->toArray();
    }
}

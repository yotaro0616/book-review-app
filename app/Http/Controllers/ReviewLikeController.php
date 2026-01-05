<?php

namespace App\Http\Controllers;

use App\Models\Review;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        // ログインユーザーの likedReviews リレーションに対して toggle を実行
        // 中間テーブル review_likes に対してデータの追加/削除を自動で行います
        auth()->user()->likedReviews()->toggle($review->id);

        return back(); // 元の画面（書籍詳細画面）に戻る
    }
}

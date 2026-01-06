<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    /**
     * この書籍を登録したユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この書籍に紐付くジャンル一覧を取得
     */
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    /**
     * この書籍に対するレビュー一覧を取得
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * この書籍をお気に入りに登録しているユーザー一覧を取得
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    protected $casts = [
        'published_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 検索条件を適用するローカルスコープ
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        // $filters["keyword"] が存在する場合にのみ、このクロージャ内のクエリを実行
        $query->when($filters['keyword'] ?? null, function ($query, $keyword) {
            // タイトルまたは著者名にキーワードが含まれるものを検索
            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
            // $filters["genre_id"] が存在する場合にのみ、このクロージャ内のクエリを実行
        })->when($filters['genre_id'] ?? null, function ($query, $genreId) {
            // genresリレーションが存在し、そのIDが一致する書籍を検索
            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            });
        });

        return $query;
    }
}

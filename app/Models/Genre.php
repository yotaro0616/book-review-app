<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * このジャンルに紐付く書籍一覧を取得
     */
    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}

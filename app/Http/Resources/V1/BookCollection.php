<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookCollection extends ResourceCollection
{
    /**
     * 設計書通りに「data」と「meta」だけを定義する
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection, // BookResource で整形された各書籍の配列
            'meta' => [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
            ],
        ];
    }

    /**
     * Laravelが自動で付与する「links」や「デフォルトのmeta」を無効化する
     */
    public function paginationInformation($request, $paginated, $default)
    {
        // toArray で自前で meta を定義しているため、
        // Laravel標準の重複した情報を返さないように空の配列を返します。
        return [];
    }
}

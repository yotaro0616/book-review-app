<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {

        // 404エラー（NotFoundHttpException）をカスタムする
        $this->renderable(function (NotFoundHttpException $e, $request) {
            // リクエストが API (/api/*) からのものである場合
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => '書籍が見つかりませんでした。', // 設計書通りのメッセージ
                ], 404);
            }
        });
    }
}

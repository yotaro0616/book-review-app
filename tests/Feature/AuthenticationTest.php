<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーはログイン画面を表示できる()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_正しい資格情報でログインできる()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/books'); // 要件に合わせたリダイレクト先
    }

    public function test_ユーザーはログアウトできる()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login'); // ログアウト後のリダイレクト先
    }

    public function test_誤った資格情報ではログインできない()
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        $this->assertGuest();
    }

    public function test_未ログインユーザーは書籍登録画面にアクセスできない()
    {
        $response = $this->get(route('books.create'));
        $response->assertRedirect('/login');
    }
}

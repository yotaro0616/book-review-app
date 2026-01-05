<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // 新しくユーザーを作ってそのIDを割り当てる
            'title' => $this->faker->sentence(3), // 3単語程度のタイトル
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(), // 重複しない13桁のISBN
            'published_date' => $this->faker->date(),
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->imageUrl(400, 600, 'books'), // ダミー画像のURL
        ];
    }
}

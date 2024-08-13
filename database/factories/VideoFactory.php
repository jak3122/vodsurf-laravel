<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'videoId' => fake()->asciify("***********"),
            'streamer' => fake()->name(),
            'duration' => fake()->randomNumber(5),
            'publishedAt' => fake()->date(),
            'viewCount' => fake()->randomNumber(5),
            'channelId' => fake()->md5,
            'channelTitle' => fake()->name(),
            'videoTitle' => fake()->sentence(),
        ];
    }
}

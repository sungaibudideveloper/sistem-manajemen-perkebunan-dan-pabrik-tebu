<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;


class CategoryFactory extends Factory
{
    
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(rand(1,2), false),
            'slug' => Str::slug(fake()->sentence(rand(1,2), false))
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => implode(' ', $this->faker->words()),
            'notes' => rand(1, 10) > 5 ? implode("\n\n", $this->faker->paragraphs(rand(1, 3))) : null,
        ];
    }
}

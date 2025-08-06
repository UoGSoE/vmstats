<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guest>
 */
class GuestFactory extends Factory
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
            'server_id' => Server::factory(),
            'notes' => rand(1, 10) > 5 ? implode("\n\n", $this->faker->paragraphs(rand(1, 3))) : null,
        ];
    }
}

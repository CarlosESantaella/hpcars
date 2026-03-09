<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_name' => fake()->optional(0.4)->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->regexify('6[0-9]{8}'),
            'address' => fake()->address(),
            'dni' => strtoupper(fake()->unique()->regexify('[0-9]{8}[A-Z]')),
            'license_number' => strtoupper(fake()->regexify('[A-Z]-[0-9]{8}')),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->paragraph(),
        ]);
    }
}

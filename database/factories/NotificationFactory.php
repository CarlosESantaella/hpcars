<?php

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(NotificationType::cases());

        return [
            'type' => $type,
            'vehicle_id' => Vehicle::factory(),
            'title' => $type === NotificationType::Itv ? 'ITV próxima' : 'Mantenimiento próximo',
            'message' => fake()->sentence(),
            'is_read' => false,
            'due_date' => now()->addDay(),
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    public function itv(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NotificationType::Itv,
            'title' => 'ITV próxima',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NotificationType::Maintenance,
            'title' => 'Mantenimiento próximo',
        ]);
    }
}

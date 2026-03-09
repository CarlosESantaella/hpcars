<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = [
            'Seat' => ['Ibiza', 'León', 'Arona', 'Ateca'],
            'Ford' => ['Focus', 'Fiesta', 'Puma', 'Kuga'],
            'Volkswagen' => ['Polo', 'Golf', 'T-Cross', 'Tiguan'],
            'Renault' => ['Clio', 'Megane', 'Captur', 'Kadjar'],
            'Peugeot' => ['208', '308', '2008', '3008'],
            'Opel' => ['Corsa', 'Astra', 'Crossland', 'Mokka'],
            'Toyota' => ['Yaris', 'Corolla', 'C-HR', 'RAV4'],
            'Citroën' => ['C3', 'C4', 'C3 Aircross', 'C5 Aircross'],
        ];

        $brand = fake()->randomElement(array_keys($brands));
        $model = fake()->randomElement($brands[$brand]);
        $colors = ['Blanco', 'Negro', 'Gris', 'Plata', 'Rojo', 'Azul'];

        return [
            'plate' => strtoupper(fake()->unique()->regexify('[0-9]{4}[A-Z]{3}')),
            'brand' => $brand,
            'model' => $model,
            'year' => fake()->numberBetween(2019, 2025),
            'color' => fake()->randomElement($colors),
            'status' => VehicleStatus::Free,
            'current_mileage' => fake()->numberBetween(5000, 80000),
            'itv_date' => fake()->dateTimeBetween('now', '+2 years'),
            'next_maintenance_date' => fake()->dateTimeBetween('now', '+6 months'),
        ];
    }

    public function rented(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Rented,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Maintenance,
        ]);
    }

    public function itvExpiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'itv_date' => now()->addDays(fake()->numberBetween(1, 14)),
        ]);
    }

    public function maintenanceDueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => now()->addDays(fake()->numberBetween(1, 7)),
        ]);
    }
}

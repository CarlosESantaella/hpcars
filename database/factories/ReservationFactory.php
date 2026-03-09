<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 14).' days');
        $startMileage = fake()->numberBetween(10000, 80000);

        return [
            'client_id' => Client::factory(),
            'vehicle_id' => Vehicle::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => ReservationStatus::Pending,
            'start_mileage' => $startMileage,
            'end_mileage' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = fake()->dateTimeBetween('+1 day', '+2 weeks');
            $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(3, 14).' days');

            return [
                'status' => ReservationStatus::Pending,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        });
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = fake()->dateTimeBetween('-1 week', 'now');
            $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(3, 14).' days');

            return [
                'status' => ReservationStatus::Active,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = fake()->dateTimeBetween('-2 months', '-3 weeks');
            $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(3, 14).' days');
            $startMileage = fake()->numberBetween(10000, 80000);

            return [
                'status' => ReservationStatus::Completed,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_mileage' => $startMileage,
                'end_mileage' => $startMileage + fake()->numberBetween(100, 2000),
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Cancelled,
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(fake()->numberBetween(1, 7))->toDateString(),
        ]);
    }
}

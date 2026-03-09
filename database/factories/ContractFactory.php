<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'pdf_path' => null,
            'generated_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'conditions' => $this->defaultConditions(),
        ];
    }

    public function generated(): static
    {
        return $this->state(fn (array $attributes) => [
            'pdf_path' => 'contracts/'.fake()->uuid().'.pdf',
            'generated_at' => now(),
        ]);
    }

    private function defaultConditions(): string
    {
        return <<<'CONDITIONS'
1. El arrendatario se compromete a utilizar el vehículo de forma responsable y conforme a las normas de circulación vigentes.
2. El vehículo deberá ser devuelto en las mismas condiciones en que fue entregado, salvo el desgaste normal por uso.
3. El arrendatario será responsable de cualquier multa o sanción derivada del uso del vehículo durante el período de alquiler.
4. En caso de accidente, el arrendatario deberá notificar inmediatamente a la empresa arrendadora.
5. Queda prohibido fumar en el interior del vehículo.
6. El combustible no está incluido en el precio del alquiler. El vehículo se entrega con el depósito lleno y deberá devolverse en las mismas condiciones.
CONDITIONS;
    }
}

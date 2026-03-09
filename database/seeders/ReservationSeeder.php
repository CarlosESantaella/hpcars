<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $vehicles = Vehicle::all();

        if ($clients->isEmpty() || $vehicles->isEmpty()) {
            return;
        }

        // Reservas completadas con contrato
        foreach ($vehicles->take(5) as $vehicle) {
            $reservation = Reservation::factory()
                ->completed()
                ->for($clients->random())
                ->for($vehicle)
                ->create();

            Contract::factory()
                ->generated()
                ->for($reservation)
                ->create();
        }

        // Reservas activas
        Reservation::factory()
            ->count(3)
            ->active()
            ->sequence(fn ($sequence) => [
                'client_id' => $clients->random()->id,
                'vehicle_id' => $vehicles->random()->id,
            ])
            ->create();

        // Reservas pendientes
        Reservation::factory()
            ->count(4)
            ->pending()
            ->sequence(fn ($sequence) => [
                'client_id' => $clients->random()->id,
                'vehicle_id' => $vehicles->random()->id,
            ])
            ->create();

        // Reservas para hoy (pruebas del dashboard)
        Reservation::factory()
            ->count(2)
            ->today()
            ->active()
            ->sequence(fn ($sequence) => [
                'client_id' => $clients->random()->id,
                'vehicle_id' => $vehicles->random()->id,
            ])
            ->create();
    }
}

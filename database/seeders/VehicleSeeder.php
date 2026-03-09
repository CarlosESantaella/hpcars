<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 12 vehículos libres
        Vehicle::factory()->count(12)->create();

        // 2 vehículos alquilados
        Vehicle::factory()->count(2)->rented()->create();

        // 1 vehículo en mantenimiento
        Vehicle::factory()->maintenance()->create();

        // Alertas: ITV próxima a vencer
        Vehicle::factory()->itvExpiringSoon()->create();

        // Alertas: Mantenimiento próximo
        Vehicle::factory()->maintenanceDueSoon()->create();
    }
}

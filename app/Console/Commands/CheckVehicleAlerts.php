<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class CheckVehicleAlerts extends Command
{
    protected $signature = 'app:check-vehicle-alerts';

    protected $description = 'Verifica vehículos con ITV o mantenimiento próximo y crea notificaciones';

    public function handle(): int
    {
        $created = 0;

        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->endOfDay();

        // Vehículos con ITV a 1 día o menos (hoy o mañana, no pasados)
        $itvVehicles = Vehicle::whereNotNull('itv_date')
            ->where('itv_date', '>=', $today)
            ->where('itv_date', '<=', $tomorrow)
            ->get();

        foreach ($itvVehicles as $vehicle) {
            $exists = Notification::where('type', NotificationType::Itv)
                ->where('vehicle_id', $vehicle->id)
                ->whereDate('due_date', $vehicle->itv_date->toDateString())
                ->exists();

            if (! $exists) {
                $days = (int) now()->startOfDay()->diffInDays($vehicle->itv_date, false);
                $dayLabel = $days === 0 ? 'hoy' : "en {$days} día(s)";

                Notification::create([
                    'type' => NotificationType::Itv,
                    'vehicle_id' => $vehicle->id,
                    'due_date' => $vehicle->itv_date->toDateString(),
                    'title' => 'ITV próxima a vencer',
                    'message' => "La ITV del vehículo {$vehicle->plate} ({$vehicle->fullName()}) vence {$dayLabel}.",
                ]);

                $created++;
            }
        }

        // Vehículos con mantenimiento a 1 día o menos (hoy o mañana, no pasados)
        $maintenanceVehicles = Vehicle::whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '>=', $today)
            ->where('next_maintenance_date', '<=', $tomorrow)
            ->get();

        foreach ($maintenanceVehicles as $vehicle) {
            $exists = Notification::where('type', NotificationType::Maintenance)
                ->where('vehicle_id', $vehicle->id)
                ->whereDate('due_date', $vehicle->next_maintenance_date->toDateString())
                ->exists();

            if (! $exists) {
                $days = (int) now()->startOfDay()->diffInDays($vehicle->next_maintenance_date, false);
                $dayLabel = $days === 0 ? 'hoy' : "en {$days} día(s)";

                Notification::create([
                    'type' => NotificationType::Maintenance,
                    'vehicle_id' => $vehicle->id,
                    'due_date' => $vehicle->next_maintenance_date->toDateString(),
                    'title' => 'Mantenimiento programado próximo',
                    'message' => "El mantenimiento del vehículo {$vehicle->plate} ({$vehicle->fullName()}) es {$dayLabel}.",
                ]);

                $created++;
            }
        }

        $this->info("Se crearon {$created} notificación(es) nuevas.");

        return self::SUCCESS;
    }
}

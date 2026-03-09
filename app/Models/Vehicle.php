<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'plate',
        'brand',
        'model',
        'year',
        'color',
        'status',
        'current_mileage',
        'itv_date',
        'next_maintenance_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => VehicleStatus::class,
            'year' => 'integer',
            'current_mileage' => 'integer',
            'itv_date' => 'date',
            'next_maintenance_date' => 'date',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function fullName(): string
    {
        return "{$this->brand} {$this->model}";
    }

    public function isAvailable(): bool
    {
        return $this->status === VehicleStatus::Free;
    }

    public function scopeFree($query)
    {
        return $query->where('status', VehicleStatus::Free);
    }

    public function scopeAvailableBetween($query, $startDate, $endDate)
    {
        return $query->where('status', VehicleStatus::Free)
            ->whereDoesntHave('reservations', function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', ['pending', 'active'])
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                    });
            });
    }
}

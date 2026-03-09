<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'vehicle_id',
        'start_date',
        'end_date',
        'status',
        'start_mileage',
        'end_mileage',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'start_mileage' => 'integer',
            'end_mileage' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ReservationStatus::Active);
    }

    public function scopePending($query)
    {
        return $query->where('status', ReservationStatus::Pending);
    }

    public function scopeToday($query)
    {
        $today = now()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->where('start_date', $today)
                ->orWhere('end_date', $today);
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'dni',
        'dni_image_path',
        'license_number',
        'license_image_path',
        'notes',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reservationsCount(): int
    {
        return $this->reservations()->count();
    }

    public function dniImageUrl(): ?string
    {
        return $this->dni_image_path
            ? Storage::disk('public')->url($this->dni_image_path)
            : null;
    }

    public function licenseImageUrl(): ?string
    {
        return $this->license_image_path
            ? Storage::disk('public')->url($this->license_image_path)
            : null;
    }

    public function deleteDocumentImages(): void
    {
        if ($this->dni_image_path) {
            Storage::disk('public')->delete($this->dni_image_path);
        }

        if ($this->license_image_path) {
            Storage::disk('public')->delete($this->license_image_path);
        }
    }
}

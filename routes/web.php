<?php

use App\Http\Controllers\ContractDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // En modo demo, ir directamente al dashboard
    if (config('app.demo_mode')) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Vehículos
    Route::livewire('vehicles', 'pages::vehicles.index')->name('vehicles.index');
    Route::livewire('vehicles/create', 'pages::vehicles.create')->name('vehicles.create');
    Route::livewire('vehicles/{vehicle}', 'pages::vehicles.show')->name('vehicles.show');
    Route::livewire('vehicles/{vehicle}/edit', 'pages::vehicles.edit')->name('vehicles.edit');

    // Clientes
    Route::livewire('clients', 'pages::clients.index')->name('clients.index');
    Route::livewire('clients/create', 'pages::clients.create')->name('clients.create');
    Route::livewire('clients/{client}', 'pages::clients.show')->name('clients.show');
    Route::livewire('clients/{client}/edit', 'pages::clients.edit')->name('clients.edit');
    // Reservas
    Route::livewire('reservations', 'pages::reservations.index')->name('reservations.index');
    Route::livewire('reservations/create', 'pages::reservations.create')->name('reservations.create');
    Route::livewire('reservations/{reservation}', 'pages::reservations.show')->name('reservations.show');
    Route::livewire('reservations/{reservation}/edit', 'pages::reservations.edit')->name('reservations.edit');

    // Notificaciones
    Route::livewire('notifications', 'pages::notifications.index')->name('notifications.index');

    // Contratos
    Route::livewire('contracts', 'pages::contracts.index')->name('contracts.index');
    Route::livewire('contracts/{contract}', 'pages::contracts.show')->name('contracts.show');
    Route::get('contracts/{contract}/download', [ContractDownloadController::class, 'download'])->name('contracts.download');
});

// Temporal: crear symlink de storage en servidor compartido (eliminar después de usar)
Route::get('storage-link', function () {
    Illuminate\Support\Facades\Artisan::call('storage:link');

    return 'Storage link creado correctamente. Elimina esta ruta.';
});

require __DIR__.'/settings.php';

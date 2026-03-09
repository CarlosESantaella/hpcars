<?php

// Protección básica - elimina este archivo después de usarlo
$token = 'hpcars2024';

if (!isset($_GET['token']) || $_GET['token'] !== $token) {
    http_response_code(403);
    die('Acceso denegado');
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('storage:link');

echo '<h2>Comando ejecutado correctamente</h2>';
echo '<pre>' . $kernel->output() . '</pre>';
echo '<p style="color: red;"><strong>IMPORTANTE:</strong> Elimina este archivo después de usarlo.</p>';

<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Mengarahkan ke direktori aplikasi kds-pm di luar public_html
$home = getenv('HOME') ?: ('/home/' . (getenv('USER') ?: 'u' . posix_getuid()));
$base = $home . '/kds-pm';

if (! file_exists($base . '/bootstrap/app.php')) {
    // Alternatif jika path berbeda
    $base = dirname(__DIR__, 3) . '/kds-pm';
}

if (file_exists($maintenance = $base . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $base . '/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $base . '/bootstrap/app.php';

// Memastikan public path diarahkan ke lokasi dokumen root subdomain
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());


<?php

declare(strict_types=1);

/**
 * Front controller — the ONLY publicly reachable PHP entry point.
 *
 * The original project exposed ~20 individual .php files directly
 * (admin.php, status.php, status1.php, pdf.php, registration.php...), each
 * duplicating session/DB bootstrap and each independently guessable/
 * reachable with no central place to enforce auth or CSRF. Every request
 * now goes through here, so authentication, CSRF verification, and error
 * handling are enforced in exactly one place (see routes/web.php).
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');

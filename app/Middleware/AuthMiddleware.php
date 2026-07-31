<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\MiddlewareInterface;
use App\Core\Session;

/** Blocks access unless the visitor is logged in. */
final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . url('/login'));
            exit;
        }
    }
}

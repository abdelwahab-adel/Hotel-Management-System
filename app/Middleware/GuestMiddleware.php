<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;

/** Blocks logged-in users from re-visiting login/register pages. */
final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Auth::check()) {
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}

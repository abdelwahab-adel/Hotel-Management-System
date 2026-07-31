<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;

/**
 * Base class for role-gated middleware. The original project had no
 * authorization layer at all — anyone who guessed /admin.php's URL and the
 * hardcoded credentials had full access; there was no concept of scoped
 * permissions (e.g. a receptionist able to manage bookings but not settings).
 */
abstract class RoleMiddleware implements MiddlewareInterface
{
    /** @return string[] */
    abstract protected function allowedRoles(): array;

    public function handle(): void
    {
        if (!Auth::check()) {
            header('Location: ' . url('/login'));
            exit;
        }

        if (!Auth::hasRole($this->allowedRoles())) {
            http_response_code(403);
            echo view('errors/403', []);
            exit;
        }
    }
}

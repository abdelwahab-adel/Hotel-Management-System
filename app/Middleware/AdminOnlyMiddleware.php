<?php

declare(strict_types=1);

namespace App\Middleware;

/** Super Admin + Admin: full back-office access. */
final class AdminOnlyMiddleware extends RoleMiddleware
{
    protected function allowedRoles(): array
    {
        return ['super_admin', 'admin'];
    }
}

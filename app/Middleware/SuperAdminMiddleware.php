<?php

declare(strict_types=1);

namespace App\Middleware;

/** Super Admin only: system settings, staff account management. */
final class SuperAdminMiddleware extends RoleMiddleware
{
    protected function allowedRoles(): array
    {
        return ['super_admin'];
    }
}

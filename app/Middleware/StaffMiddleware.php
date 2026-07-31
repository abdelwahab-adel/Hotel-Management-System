<?php

declare(strict_types=1);

namespace App\Middleware;

/** Super Admin + Admin + Receptionist: operational back-office access
 *  (bookings/check-in/out), but not Settings or user management. */
final class StaffMiddleware extends RoleMiddleware
{
    protected function allowedRoles(): array
    {
        return ['super_admin', 'admin', 'receptionist'];
    }
}

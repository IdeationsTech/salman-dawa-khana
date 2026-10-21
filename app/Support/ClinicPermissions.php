<?php

namespace App\Support;

use App\Models\User;

class ClinicPermissions
{
    public static function allows(?User $user, string $route): bool
    {
        $role = $user?->role?->name;
        if (! $user?->is_active || ! in_array($role, ['Owner', 'Doctor', 'Receptionist', 'Accountant'], true)) {
            return false;
        }
        if ($role === 'Owner') {
            return true;
        }
        if (str_starts_with($route, 'prescriptions.templates.')) {
            return $role === 'Doctor';
        }
        if (str_starts_with($route, 'prescriptions.')) {
            return in_array($route, ['prescriptions.index', 'prescriptions.show', 'prescriptions.patients.search', 'prescriptions.patients.visits'], true)
                ? in_array($role, ['Doctor', 'Receptionist'], true) : $role === 'Doctor';
        }
        if (str_starts_with($route, 'users.') || $route === 'audit.index') {
            return false;
        }
        if (str_ends_with($route, '.destroy')) {
            return false;
        }
        if (in_array($route, ['payments.edit', 'payments.update'], true)) {
            return $role === 'Accountant';
        }
        if (str_starts_with($route, 'expenses.') || $route === 'reports.index') {
            return in_array($role, ['Doctor', 'Accountant'], true);
        }
        if (str_starts_with($route, 'orders.refunds.')) {
            return $role === 'Doctor';
        }
        if (in_array($route, ['visits.edit', 'visits.update'], true)) {
            return $role === 'Doctor';
        }
        if (in_array($route, ['patients.create', 'patients.store', 'patients.edit', 'patients.update', 'visits.create', 'visits.store'], true)) {
            return in_array($role, ['Doctor', 'Receptionist'], true);
        }

        return true;
    }
}

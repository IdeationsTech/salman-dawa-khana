<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $roles = [
                'Owner',
                'Doctor',
                'Receptionist',
                'Accountant',
            ];

            foreach ($roles as $roleName) {
                DB::table('roles')->updateOrInsert(
                    ['name' => $roleName],
                    ['name' => $roleName]
                );
            }

            $permissions = [
                ['code' => 'patient.view', 'label' => 'View Patients'],
                ['code' => 'patient.create', 'label' => 'Create Patients'],
                ['code' => 'patient.edit', 'label' => 'Edit Patients'],
                ['code' => 'visit.create', 'label' => 'Create Visits'],
                ['code' => 'prescription.create', 'label' => 'Create Prescriptions'],
                ['code' => 'prescription.print', 'label' => 'Print Prescriptions'],
                ['code' => 'bill.create', 'label' => 'Create Bills'],
                ['code' => 'payment.receive', 'label' => 'Receive Payments'],
                ['code' => 'expense.view', 'label' => 'View Expenses'],
                ['code' => 'expense.manage', 'label' => 'Manage Expenses'],
                ['code' => 'report.view', 'label' => 'View Reports'],
                ['code' => 'user.manage', 'label' => 'Manage Users'],
            ];

            foreach ($permissions as $permission) {
                DB::table('permissions')->updateOrInsert(
                    ['code' => $permission['code']],
                    ['label' => $permission['label']]
                );
            }

            $ownerRoleId = DB::table('roles')
                ->where('name', 'Owner')
                ->value('role_id');

            $allPermissionIds = DB::table('permissions')
                ->pluck('permission_id');

            foreach ($allPermissionIds as $permissionId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $ownerRoleId,
                    'permission_id' => $permissionId,
                ]);
            }
        });
    }
}
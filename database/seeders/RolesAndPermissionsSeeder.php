<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الصلاحيات
        $permissions = [
            'manage users',
            // 'manage orders',
            // 'manage products',
            'manage vouchers',
            'manage subscriptions',
            'manage categories',
            'manage faqs',
            'manage terms',
            'manage policies',
            'manage about',
            'manage contacts',
            'manage feedbacks',
            'manage withdrawals',
            'send notifications'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // إنشاء الأدوار وربط الصلاحيات بها
        $superAdmin = Role::firstOrCreate(['name' => 'superAdmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $permissions = Permission::all();

        // السوبر أدمن لديه كل الصلاحيات
        $superAdmin->syncPermissions($permissions);

        // الأدمن لديه بعض الصلاحيات فقط
        $admin->syncPermissions([
            'manage users',
            // 'manage orders',
            // 'manage products',
            'manage vouchers',
            'manage subscriptions',
            'manage categories',
        ]);
    }
}

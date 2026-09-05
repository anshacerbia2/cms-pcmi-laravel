<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $topMenus = [
            'Manage Users' => ['prefix' => 'users', 'icon' => 'ti ti-user'],
            'BOQs' => ['prefix' => 'boqs', 'icon' => 'ti ti-layout-list'],
            'Categories' => ['prefix' => 'categories', 'icon' => 'ti ti-category'],
            'Products' => ['prefix' => 'products', 'icon' => 'ti ti-box'],
            'Suppliers' => ['prefix' => 'suppliers', 'icon' => 'ti ti-truck'],
            'Roles' => ['prefix' => 'roles', 'icon' => 'ti ti-lock'],
            'Menus' => ['prefix' => 'menus', 'icon' => 'ti ti-menu'],
            'Permissions' => ['prefix' => 'permissions', 'icon' => 'ti ti-shield'],
            'Customers' => ['prefix' => 'customers', 'icon' => 'ti ti-users'],
            'Projects' => ['prefix' => 'projects', 'icon' => 'ti ti-briefcase'],
            'Employees' => ['prefix' => 'employees', 'icon' => 'ti ti-users-group'],
            'Proposals' => ['prefix' => 'proposals', 'icon' => 'ti ti-file-text'],
            'Invoices' => ['prefix' => 'invoices', 'icon' => 'ti ti-file-invoice'],
            'PDF Templates' => ['prefix' => 'pdf-templates', 'icon' => 'ti ti-file-settings'],
            'Banks' => ['prefix' => 'banks', 'icon' => 'ti ti-building-bank'],
            'PCMI Banks' => ['prefix' => 'pcmibanks', 'icon' => 'ti ti-building-bank'],
            'Billing Options' => ['prefix' => 'billing-options', 'icon' => 'ti ti-settings-automation'],
            'Receive Vouchers' => ['prefix' => 'rvs', 'icon' => 'ti ti-receipt'],
            'Payment Vouchers' => ['prefix' => 'pvs', 'icon' => 'ti ti-cash-banknote'],
        ];

        $allMenuIds = [];
        $userMenuIds = [];

        foreach ($topMenus as $menuName => $data) {
            // Buat menu top-level saja
            $menu = Menu::firstOrCreate(
                ['name' => $menuName],
                [
                    'icon' => $data['icon'],
                ]
            );

            // Ambil permission prefix.index
            $permission = Permission::where('route', "{$data['prefix']}.index")->first();
            if ($permission) {
                $menu->permission()->associate($permission);
                $menu->save();
            }

            $allMenuIds[] = $menu->id;

            if (in_array($data['prefix'], ['categories', 'products'])) {
                $userMenuIds[] = $menu->id;
            }
        }

        // Assign semua menu ke role Admin
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->menus()->sync($allMenuIds);
        }

        // Assign hanya categories & products ke role User
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $userRole->menus()->sync($userMenuIds);
        }
    }
}

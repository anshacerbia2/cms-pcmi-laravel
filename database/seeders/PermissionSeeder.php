<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            'users' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['changePassword', 'PATCH'], ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'boqs' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE'], ['bulkDelete', 'DELETE'],
                ['replicate', 'PATCH'], ['unbindProposal', 'PATCH']
            ],
            'categories' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'products' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'suppliers' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'roles' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'menus' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'permissions' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'customers' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'projects' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'proposals' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['boqs', 'GET'], ['update', 'PUT'], ['delete', 'DELETE'],
                ['pdf', 'GET'], ['getPricingModel', 'GET'], ['savePricingModel', 'PUT'], ['getAvailableBoqs', 'GET']
            ],
            'invoices' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE'], ['pdf', 'GET']
            ],
            'employees' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'pdf-templates' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE'], ['preview', 'POST']
            ],
            'banks' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'pcmibanks' => [
                ['index', 'GET'], ['create', 'POST'], ['readAll', 'GET'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'billing-options' => [
                ['index', 'GET'], ['create', 'POST'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'rvs' => [
                ['index', 'GET'], ['create', 'POST'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
            'pvs' => [
                ['index', 'GET'], ['create', 'POST'], ['read', 'GET'],
                ['update', 'PUT'], ['delete', 'DELETE']
            ],
        ];

        $allPermissions = [];
        $categoryProductPermissions = [];

        foreach ($routes as $prefix => $actions) {
            foreach ($actions as [$action, $method]) {
                $route = "{$prefix}.{$action}";
                $path = $this->buildPath($prefix, $action);

                $permission = Permission::firstOrCreate(
                    ['route' => $route],
                    ['route' => $route, 'method' => $method, 'path' => $path, 'description' => ucfirst($action)]
                );

                $allPermissions[] = $permission->id;

                // Kumpulin permission categories & products untuk role user
                if (in_array($prefix, ['categories', 'products'])) {
                    $categoryProductPermissions[] = $permission->id;
                }
            }
        }

        // Assign semua permission ke role Admin
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync($allPermissions);
        }

        // Assign hanya permission categories & products ke role User
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $userRole->permissions()->sync($categoryProductPermissions);
        }
    }

    private function buildPath(string $prefix, string $action): string
    {
        return match($action) {
            'index', 'create' => "/{$prefix}",
            'readAll' => "/{$prefix}/all",
            'read', 'update', 'delete' => "/{$prefix}/{id}",
            'bulkDelete' => "/{$prefix}",
            'replicate' => "/{$prefix}/replicate/{proposal_id?}",
            'changePassword' => "/{$prefix}/change-password/{user_id}",
            'unbindProposal' => "/{$prefix}/unbind-proposal/{boq_id?}",
            'boqs' => "/proposals/{proposal_id}/boqs",
            'preview' => "/{$prefix}/preview",
            'getPricingModel', 'savePricingModel' => "/proposals/{proposal_id}/pricing-model",
            'getAvailableBoqs' => "/proposals/{proposal_id}/available-boqs",
            'pdf' => "/{$prefix}/{id}/pdf",
            default => "/{$prefix}/{$action}"
        };
    }
}

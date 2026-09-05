<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;

echo "Total Users: " . User::count() . "\n";
User::all()->each(function($user) {
    echo "- User: {$user->name}, Email: {$user->email}, Role: " . ($user->role?->name ?: 'NONE') . " (Slug: " . ($user->role?->slug ?: 'NONE') . ")\n";
});

$admin_role = Role::where('slug', 'admin')->first();
if ($admin_role) {
    echo "\nAdmin Role found (ID: {$admin_role->id}).\n";
    $rvs_perms = Permission::where('route', 'like', 'rvs.%')->get();
    foreach ($rvs_perms as $p) {
        $linked = $admin_role->permissions()->where('permissions.id', $p->id)->exists();
        echo "- Permission {$p->route}: " . ($linked ? 'LINKED' : 'NOT LINKED') . "\n";
    }
}

// Check Menu Service
use App\Services\MenuService;
try {
    $menuService = app(MenuService::class);
    echo "\nMenuService is available.\n";
} catch (\Exception $e) {
    echo "\nMenuService Error: " . $e->getMessage() . "\n";
}

<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'Admin']);
        $users = User::factory(15)->create();
        $roleAdmin = Role::where('name', 'Admin')->first();
        foreach ($users as $user) {
            $user->assignRole($roleAdmin);
        }


        //role permission
        $roleSuperAdmin = Role::create(['name' => 'Super Admin']);

        Permission::create(['name' => 'dashboard-page']);
        Permission::create(['name' => 'options']);
        Permission::create(['name' => 'master-data']);
        Permission::create(['name' => 'user-page']);
        Permission::create(['name' => 'user-create']);
        Permission::create(['name' => 'user-edit']);
        Permission::create(['name' => 'user-delete']);

        $roleSuperAdmin->givePermissionTo(Permission::all());
        $roleAdmin->givePermissionTo(Permission::where('name', 'dashboard-page')->first());

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);

        $superAdmin->assignRole($roleSuperAdmin);
    }
}

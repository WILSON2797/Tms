<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Permissions
        $permissions = [
            // Master Data
            ['name' => 'View Master Data', 'slug' => 'view-master'],
            ['name' => 'Create Master Data', 'slug' => 'create-master'],
            ['name' => 'Edit Master Data', 'slug' => 'edit-master'],
            ['name' => 'Delete Master Data', 'slug' => 'delete-master'],
            
            // Shipment Order
            ['name' => 'View Shipment Order', 'slug' => 'view-shipment'],
            ['name' => 'Create Shipment Order', 'slug' => 'create-shipment'],
            ['name' => 'Edit Shipment Order', 'slug' => 'edit-shipment'],
            ['name' => 'Delete Shipment Order', 'slug' => 'delete-shipment'],
            
            // Trip Planning
            ['name' => 'View Trip', 'slug' => 'view-trip'],
            ['name' => 'Create Trip', 'slug' => 'create-trip'],
            ['name' => 'Edit Trip', 'slug' => 'edit-trip'],
            ['name' => 'Delete Trip', 'slug' => 'delete-trip'],
            
            // POD
            ['name' => 'View POD', 'slug' => 'view-pod'],
            ['name' => 'Upload POD', 'slug' => 'upload-pod'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = \App\Models\Permission::create($p);
        }

        // 2. Define Roles
        $superAdminRole = \App\Models\Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $operationalRole = \App\Models\Role::create(['name' => 'Operational', 'slug' => 'operational']);
        $plannerRole = \App\Models\Role::create(['name' => 'Transport Planner', 'slug' => 'transport-planner']);
        $csRole = \App\Models\Role::create(['name' => 'Customer Service', 'slug' => 'customer-service']);
        $driverRole = \App\Models\Role::create(['name' => 'Driver', 'slug' => 'driver']);

        // 3. Assign Permissions to Roles
        // Super Admin gets all implicitly via model helper, but let's sync in database too
        $superAdminRole->permissions()->sync(array_column($permissionModels, 'id'));

        // Operational permissions
        $operationalRole->permissions()->sync([
            $permissionModels['view-shipment']->id,
            $permissionModels['create-shipment']->id,
            $permissionModels['edit-shipment']->id,
        ]);

        // Transport Planner permissions
        $plannerRole->permissions()->sync([
            $permissionModels['view-shipment']->id,
            $permissionModels['view-trip']->id,
            $permissionModels['create-trip']->id,
            $permissionModels['edit-trip']->id,
        ]);

        // Customer Service permissions
        $csRole->permissions()->sync([
            $permissionModels['view-shipment']->id,
            $permissionModels['view-trip']->id,
            $permissionModels['view-pod']->id,
            $permissionModels['upload-pod']->id,
        ]);

        // Driver permissions
        $driverRole->permissions()->sync([
            $permissionModels['view-trip']->id,
            $permissionModels['upload-pod']->id,
        ]);

        // 4. Create Default Users
        \App\Models\User::create([
            'name' => 'Super Admin User',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
        ]);

        \App\Models\User::create([
            'name' => 'Operational User',
            'username' => 'ops',
            'password' => bcrypt('password'),
            'role_id' => $operationalRole->id,
        ]);

        \App\Models\User::create([
            'name' => 'Transport Planner User',
            'username' => 'planner',
            'password' => bcrypt('password'),
            'role_id' => $plannerRole->id,
        ]);

        \App\Models\User::create([
            'name' => 'Customer Service User',
            'username' => 'cs',
            'password' => bcrypt('password'),
            'role_id' => $csRole->id,
        ]);

        // Seed Drivers as Users
        \App\Models\User::create([
            'name' => 'Ahmad Subarjo',
            'username' => 'driver1',
            'password' => bcrypt('password'),
            'role_id' => $driverRole->id,
        ]);

        \App\Models\User::create([
            'name' => 'Joko Susilo',
            'username' => 'driver2',
            'password' => bcrypt('password'),
            'role_id' => $driverRole->id,
        ]);

        \App\Models\User::create([
            'name' => 'Rian Hidayat',
            'username' => 'driver3',
            'password' => bcrypt('password'),
            'role_id' => $driverRole->id,
        ]);
    }
}

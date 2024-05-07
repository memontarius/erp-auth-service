<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->truncate();

        $roles = \App\Enums\Role::cases();

        foreach ($roles as $role) {
            $role = new Role([
                'name' => $role->name,
                'type' => $role->value
            ]);
            $role->save();
        }
    }
}

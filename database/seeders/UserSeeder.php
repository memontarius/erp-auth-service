<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->truncate();

        $user = User::factory()->create([
            'name' => 'Иван',
            'lastName' => 'Иванов',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin')
        ]);

        DB::table('users_companies')->insert([
            'user_id' => $user->id,
            'company_id' => Company::all()->first()->id
        ]);

        DB::table('users_companies_roles')->insert([
            'user_id' => $user->id,
            'company_id' => Company::all()->first()->id,
            'role_id' => Role::where('type', \App\Enums\Role::CompanyOwner)->first()->id
        ]);
    }
}

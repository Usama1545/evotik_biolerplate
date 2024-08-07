<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        /**
         * @var User
         */
        $user = User::create([
            'name' => 'maen@househ',
            'email' => 'maen.househ@evotik.com',
            'password' => Hash::make('evotik@2255'),
        ]);

        // $user->assignRole('admin');
        // $user->permissions()->sync(
        //     Role::query()->where('name', 'developer')->with('permissions')->first()?->permissions?->pluck('id')
        // );

        $user = User::create([
            'name' => 'user@seo',
            'email' => 'user@evotik.com',
            'password' => Hash::make('evotik@2255'),
        ]);

        // $user->assignRole('user');
        // $user->permissions()->sync(
        //     Role::query()->where('name', 'user')->with('permissions')->first()?->permissions?->pluck('id')
        // );
    }
}

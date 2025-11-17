<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::firstOrNew(['email' => 'admin@example.com']);
        $u->name = 'Admin';
        $u->password = Hash::make('password');
        $u->role = 'admin';
        $u->save();
    }
}

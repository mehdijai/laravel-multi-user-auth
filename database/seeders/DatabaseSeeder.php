<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'id' => 1 ,
            'name' => 'teacher'
        ]);
        
        Role::create([
            'id' => 2 ,
            'name' => 'student'
        ]);
    }
}

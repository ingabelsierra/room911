<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Abel',           
            'last_name' => 'Sierra',           
            'email' => 'ingabelsierra@gmail.com',                  
            'identification_number' => '92549586',                             
            'password' => bcrypt('sierra'), 
            'is_active' => 1,           
        ]);
    }
}

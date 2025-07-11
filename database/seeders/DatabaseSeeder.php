<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        try {
            
            User::firstOrCreate(
                ['email' => 'admin@municipality.gov'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('admin123'),
                    'is_admin' => true,
                ]
            );

            
            User::firstOrCreate(
                ['email' => 'charles@municipality.gov'],
                [
                    'name' => 'Charles Biasora',
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                ]
            );

    
            $this->call([
                ThreatScenarioSeeder::class,
            ]);
            
            Log::info('Database seeding completed successfully');
        } catch (\Exception $e) {
            Log::error('Error during database seeding: ' . $e->getMessage());
            throw $e; 
        }
    }
}
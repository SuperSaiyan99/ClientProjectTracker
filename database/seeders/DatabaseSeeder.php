<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@kodakollectiv.com'],
            [
                'name' => 'koda kollectiv Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $jsonPath = database_path('seeders/data/test_data.json');
        
        if (! File::exists($jsonPath)) {
            $jsonPath = public_path('test_data.json');
        }

        $projects = json_decode(File::get($jsonPath), true);

        foreach ($projects as $item) {
            Project::updateOrCreate(
                ['id' => $item['id']],
                [
                    'user_id' => $user->id,
                    'client_name' => $item['clientName'],
                    'project_name' => $item['projectName'],
                    'description' => $item['description'] ?? null,
                    'status' => $item['status'],
                    'priority' => $item['priority'],
                    'start_date' => $item['startDate'],
                    'due_date' => $item['dueDate'],
                ]
            );
        }
    }
}

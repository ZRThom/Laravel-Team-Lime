<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sports = [
            ['name' => 'Football', 'slug' => 'football'],
            ['name' => 'Basketball', 'slug' => 'basketball'],
            ['name' => 'Tennis', 'slug' => 'tennis'],
            ['name' => 'Padel', 'slug' => 'padel'],
            ['name' => 'Running', 'slug' => 'running'],
            ['name' => 'Natation', 'slug' => 'natation'],
            ['name' => 'Escalade', 'slug' => 'escalade'],
            ['name' => 'Badminton', 'slug' => 'badminton'],
            ['name' => 'Volleyball', 'slug' => 'volleyball'],
            ['name' => 'Handball', 'slug' => 'handball'],
        ];

        // Insert or update each sport idempotently
        foreach ($sports as $sportData) {
            // Retrieve existing sport by slug or instantiate a new one
            $sport = Sport::firstOrNew(['slug' => $sportData['slug']]);
            $sport->name = $sportData['name'];
            $sport->active = true;
            $sport->save();
        }
    }
}

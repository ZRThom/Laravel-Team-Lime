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
        /**
        * Slug is a unique id for each sport , slug = index of the sports in the array , slug = unique type in database
        * " sports here =  Base list of commom sports / popular sports"
        */
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
        // rename ("as")the sport slug to be unique for the list of sports in this array for the database
        foreach ($sports as $sportData) {
            // Retrieve existing sport by slug or instantiate a new one
            $sport = Sport::firstOrNew(['slug' => $sportData['slug']]);
            //Use FirstOrNew ( native laravel function) and not create because : we create a new sport if it doesn't exist,
            // or update the existing one if it does.
            $sport->name = $sportData['name'];
            $sport->active = true;
            $sport->save();
        }
    }
}

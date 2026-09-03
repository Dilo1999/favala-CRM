<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SuperAdminSeeder::class);

        foreach (['BlogPostSeeder', 'PageSeoSeeder'] as $legacySeeder) {
            $class = "Database\\Seeders\\{$legacySeeder}";
            if (class_exists($class)) {
                $this->call($class);
            }
        }

        $this->call(SettingOptionSeeder::class);
        $this->call(AtollIslandSeeder::class);
        $this->call(CrmDemoSeeder::class);
    }
}

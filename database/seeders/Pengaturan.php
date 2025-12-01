<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Pengaturan extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    Setting::updateOrCreate(
        ['key' => 'biaya_admin'],
        ['value' => '12000'] // Nominal default
    );
}
}

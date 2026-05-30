<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Renta',       'color' => '#EF4444'],
            ['name' => 'Productos',   'color' => '#F59E0B'],
            ['name' => 'Servicios',   'color' => '#3B82F6'],
            ['name' => 'Nómina',      'color' => '#8B5CF6'],
            ['name' => 'Equipamiento','color' => '#10B981'],
            ['name' => 'Otros',       'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name']],
                ['color' => $category['color']]
            );
        }
    }
}
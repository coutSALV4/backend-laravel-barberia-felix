<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Corte clásico',     'description' => 'Corte de cabello estilo clásico.',         'price' => 120.00, 'duration_min' => 30],
            ['name' => 'Corte moderno',      'description' => 'Corte con técnicas actuales.',              'price' => 150.00, 'duration_min' => 40],
            ['name' => 'Arreglo de barba',   'description' => 'Perfilado y arreglo de barba.',             'price' => 80.00,  'duration_min' => 20],
            ['name' => 'Corte + barba',      'description' => 'Corte de cabello más arreglo de barba.',    'price' => 180.00, 'duration_min' => 50],
            ['name' => 'Rasurado clásico',   'description' => 'Rasurado con navaja estilo barbería.',      'price' => 100.00, 'duration_min' => 25],
            ['name' => 'Tinte',              'description' => 'Aplicación de color en cabello.',           'price' => 250.00, 'duration_min' => 60],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                array_merge($service, ['active' => true])
            );
        }
    }
}
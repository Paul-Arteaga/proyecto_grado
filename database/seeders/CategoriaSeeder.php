<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
            public function run(): void
        {
            \App\Models\Categoria::factory()->createMany([
                ['nombre'=>'Sedán','slug'=>'sedan','descripcion'=>'Autos cómodos para ciudad y carretera'],
                ['nombre'=>'SUV','slug'=>'suv','descripcion'=>'Espacio y altura para viajes'],
                ['nombre'=>'Crossover','slug'=>'crossover','descripcion'=>'Equilibrio urbano/aventura'],
            ]);
        }

}

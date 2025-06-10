<?php
// database/seeders/CategoriaSeeder.php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            ['nombre' => 'Electrónicos'],
            ['nombre' => 'Oficina'],
            ['nombre' => 'Herramientas'],
            ['nombre' => 'Limpieza'],
            ['nombre' => 'Seguridad'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
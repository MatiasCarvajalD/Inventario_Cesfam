<?php

namespace Database\Seeders;

// database/seeders/ProductoSeeder.php

use App\Enums\ProductoEstado;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        $productos = [
            [
                'nombre' => 'Laptop Gamer',
                'descripcion' => 'Laptop de alto rendimiento para juegos',
                'cantidad' => 15,
                'estado' => ProductoEstado::DISPONIBLE,
                'categoria_id' => 1,
                'marca_id' => 1,
            ],
            [
                'nombre' => 'Monitor 24"',
                'descripcion' => 'Monitor Full HD para oficina',
                'cantidad' => 8,
                'estado' => ProductoEstado::DISPONIBLE,
                'categoria_id' => 1,
                'marca_id' => 3,
            ],
            [
                'nombre' => 'Taladro inalámbrico',
                'cantidad' => 5,
                'estado' => ProductoEstado::EN_USO,
                'categoria_id' => 3,
                'marca_id' => 5,
            ],
            [
                'nombre' => 'Juego de limpieza',
                'descripcion' => 'Kit de limpieza para oficina',
                'cantidad' => 20,
                'estado' => ProductoEstado::DISPONIBLE,
                'categoria_id' => 4,
                'marca_id' => 4,
                'metadata' => json_encode(['componentes' => ['Escoba', 'Recogedor', 'Trapeador']])
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
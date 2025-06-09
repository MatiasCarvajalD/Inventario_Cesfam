// database/seeders/MovimientoSeeder.php
<?php

namespace Database\Seeders;

use App\Enums\TipoMovimiento;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class MovimientoSeeder extends Seeder
{
    public function run()
    {
        $productos = Producto::all();

        foreach ($productos as $producto) {
            // Entrada inicial
            Movimiento::create([
                'producto_id' => $producto->id,
                'tipo' => TipoMovimiento::ENTRADA,
                'cantidad' => $producto->cantidad + 5,
                'motivo' => 'Stock inicial'
            ]);

            // Algunas salidas
            if ($producto->cantidad > 3) {
                Movimiento::create([
                    'producto_id' => $producto->id,
                    'tipo' => TipoMovimiento::SALIDA,
                    'cantidad' => 2,
                    'motivo' => 'Venta a cliente'
                ]);
            }
        }
    }
}
<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Enums\TipoMovimiento;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    // Uso de named arguments (PHP 8.0+)
    public function ajustarInventario(
        Producto $producto,
        int $cantidad,
        string $motivo,
        string $responsable = null
    ): Movimiento {
        return DB::transaction(function () use ($producto, $cantidad, $motivo, $responsable) {
            $tipo = $cantidad > 0 ? TipoMovimiento::ENTRADA : TipoMovimiento::SALIDA;
            $cantidadAbs = abs($cantidad);

            $movimiento = Movimiento::registrar(
                producto: $producto,
                tipo: $tipo,
                cantidad: $cantidadAbs,
                motivo: $motivo,
                responsable: $responsable
            );

            // Actualización optimizada con operador match (PHP 8.0+)
            $producto->forceFill([
                'cantidad' => match($tipo) {
                    TipoMovimiento::ENTRADA => $producto->cantidad + $cantidadAbs,
                    TipoMovimiento::SALIDA => $producto->cantidad - $cantidadAbs,
                }
            ])->save();

            return $movimiento->refresh();
        });
    }

    // Método para generar informes con caché
    public function generarInformeStock(): array
    {
        return cache()->remember('informe_stock', now()->addHours(6), function () {
            return [
                'total_productos' => Producto::count(),
                'stock_total' => Producto::sum('cantidad'),
                'productos_bajo_stock' => Producto::where('cantidad', '<', 5)->count(),
                'estadisticas_por_estado' => Producto::groupBy('estado')
                    ->selectRaw('estado, count(*) as total, sum(cantidad) as stock')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->estado->value => $item]),
            ];
        });
    }
}
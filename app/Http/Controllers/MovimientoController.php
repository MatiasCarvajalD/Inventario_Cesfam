<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Enums\TipoMovimiento;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Historial con filtros
     */
    public function index(Request $request)
    {
        $movimientos = Movimiento::query()
            ->with(['producto' => fn($q) => $q->withTrashed()])
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->producto_id, fn($q) => $q->where('producto_id', $request->producto_id))
            ->latest()
            ->paginate(15);

        return view('movimientos.index', [
            'movimientos' => $movimientos,
            'productos' => Producto::orderBy('nombre')->get(),
            'tipos' => TipoMovimiento::cases()
        ]);
    }

    /**
     * Registra movimiento y actualiza stock
     */
    public function store(Request $request, Producto $producto)
    {
        $request->validate([
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($request, $producto) {
            $movimiento = Movimiento::create([
                'producto_id' => $producto->id,
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo
            ]);

            $request->tipo === 'entrada'
                ? $producto->increment('cantidad', $request->cantidad)
                : $producto->decrement('cantidad', $request->cantidad);
        });

        return back()->with('success', '¡Movimiento registrado!');
    }

    /**
     * Elimina movimiento y revierte stock
     */
    public function destroy(Movimiento $movimiento)
    {
        DB::transaction(function () use ($movimiento) {
            $producto = $movimiento->producto;
            
            $movimiento->tipo === 'entrada'
                ? $producto->decrement('cantidad', $movimiento->cantidad)
                : $producto->increment('cantidad', $movimiento->cantidad);

            $movimiento->delete();
        });

        return back()->with('success', '¡Movimiento revertido!');
    }
}
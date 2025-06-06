<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Enums\ProductoEstado;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca'])
            ->when($request->estado, function ($q, $estado) {
                $q->where('estado', ProductoEstado::from($estado)->value);
            })
            ->when($request->search, function ($q, $search) {
                $q->whereFullText(['nombre', 'descripcion', 'numero_inventario'], $search);
            })
            ->orderByDesc('created_at');

        return view('productos.index', [
            'productos' => $query->paginate(20),
            'estados' => ProductoEstado::cases(),
            'filters' => $request->only(['search', 'estado'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('productos.create', [
            'categorias' => Categoria::activas()->get(),
            'marcas' => Marca::activas()->get(),
            'estados' => ProductoEstado::cases(),
            'estadoDefault' => ProductoEstado::DISPONIBLE
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            $producto = Producto::create([
                'numero_serie' => $request->numero_serie,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'cantidad' => $request->cantidad ?? 0,
                'modelo' => $request->modelo,
                'ubicacion' => $request->ubicacion,
                'estado' => ProductoEstado::from($request->estado),
                'categoria_id' => $request->categoria_id,
                'marca_id' => $request->marca_id,
                'metadata' => $request->metadata ? json_decode($request->metadata) : null,
            ]);

            return redirect()->route('productos.show', $producto)
                ->with('success', 'Producto creado correctamente');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()
                ->withErrors(['estado' => 'Estado de producto inválido']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        return view('productos.show', [
            'producto' => $producto->load(['movimientos', 'categoria', 'marca']),
            'historial' => $producto->movimientos()->recientes()->paginate(10),
            'estadosPermitidos' => $producto->estado->transicionesPermitidas()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::activas()->get(),
            'marcas' => Marca::activas()->get(),
            'estadosPermitidos' => $producto->estado->transicionesPermitidas(),
            'estadoActual' => $producto->estado
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            $nuevoEstado = ProductoEstado::from($request->estado);

            if (!$producto->estado->puedeTransicionarA($nuevoEstado)) {
                throw new \RuntimeException('Transición de estado no permitida');
            }

            $producto->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'modelo' => $request->modelo,
                'ubicacion' => $request->ubicacion,
                'estado' => $nuevoEstado,
                'categoria_id' => $request->categoria_id,
                'marca_id' => $request->marca_id,
                'metadata' => $request->metadata ? json_decode($request->metadata) : null,
            ]);

            return redirect()->route('productos.show', $producto)
                ->with('success', 'Producto actualizado correctamente');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()
                ->withErrors(['estado' => 'Estado de producto inválido']);
        } catch (\RuntimeException $e) {
            return back()->withInput()
                ->withErrors(['estado' => $e->getMessage()]);
        }
    }

    /**
     * Change product state (custom method)
     */
    public function cambiarEstado(Request $request, Producto $producto)
    {
        $request->validate([
            'estado' => ['required', Rule::in(ProductoEstado::values())],
            'motivo' => ['nullable', 'string', 'max:255']
        ]);

        try {
            $nuevoEstado = ProductoEstado::from($request->estado);
            $producto->cambiarEstado($nuevoEstado);

            if ($request->motivo) {
                $producto->movimientos()->create([
                    'tipo' => $nuevoEstado === ProductoEstado::DISPONIBLE ? 'entrada' : 'salida',
                    'cantidad' => 0,
                    'motivo' => "Cambio de estado: {$producto->estado->label()} → {$nuevoEstado->label()}. {$request->motivo}"
                ]);
            }

            return back()->with('success', 'Estado actualizado correctamente');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['estado' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        try {
            throw_if(
                $producto->estado->esEstadoFinal(),
                \RuntimeException::class,
                'No se puede eliminar un producto en estado final'
            );

            $producto->delete();

            return redirect()->route('productos.index')
                ->with('success', 'Producto eliminado correctamente');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Restore soft deleted product
     */
    public function restore($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);
        $producto->restore();

        return back()->with('success', 'Producto restaurado correctamente');
    }
}
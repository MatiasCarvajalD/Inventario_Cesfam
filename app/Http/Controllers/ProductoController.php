<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Enums\ProductoEstado;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class ProductoController extends Controller
{
    public function __construct(
        protected InventarioService $inventarioService
    ) {
        $this->middleware('can:gestionar_productos')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $cacheKey = 'productos.index.' . md5(serialize($request->all()));
        
        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($request) {
            $query = Producto::with(['categoria', 'marca'])
                ->when($request->estado, fn($q, $estado) => $q->where('estado', ProductoEstado::from($estado)))
                ->when($request->search, fn($q, $search) => $q->whereFullText([
                    'nombre', 'descripcion', 'numero_inventario', 'numero_serie'
                ], $search))
                ->orderByDesc('created_at');

            return [
                'productos' => $query->paginate(20),
                'estados' => ProductoEstado::cases(),
                'filters' => $request->only(['search', 'estado'])
            ];
        });

        return view('productos.index', $data);
    }

    public function create()
    {
        return view('productos.create', [
            'categorias' => Categoria::activas()->get(),
            'marcas' => Marca::activas()->get(),
            'estados' => ProductoEstado::cases(),
            'estadoDefault' => ProductoEstado::DISPONIBLE,
            'metadataFields' => config('productos.metadata_fields', [])
        ]);
    }

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
                'metadata' => $this->processMetadata($request->metadata),
            ]);

            return redirect()
                ->route('productos.show', $producto)
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Producto creado correctamente'
                ]);

        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Estado de producto inválido'
                ]);
        }
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'marca']);

        $historial = Cache::remember(
            "productos.{$producto->id}.historial",
            now()->addHours(1),
            fn() => $producto->movimientos()
                ->with('producto')
                ->recientes()
                ->paginate(10)
        );

        return view('productos.show', [
            'producto' => $producto,
            'historial' => $historial,
            'estadosPermitidos' => $producto->estado->transicionesPermitidas(),
            'stockAlert' => $producto->cantidad < config('productos.stock_minimo', 5)
        ]);
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::activas()->get(),
            'marcas' => Marca::activas()->get(),
            'estadosPermitidos' => $producto->estado->transicionesPermitidas(),
            'estadoActual' => $producto->estado,
            'metadataFields' => config('productos.metadata_fields', [])
        ]);
    }

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
                'metadata' => $this->processMetadata($request->metadata),
            ]);

            Cache::forget("productos.{$producto->id}.historial");

            return redirect()
                ->route('productos.show', $producto)
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Producto actualizado correctamente'
                ]);

        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    public function cambiarEstado(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'estado' => ['required', Rule::in(ProductoEstado::values())],
            'motivo' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['sometimes', 'integer', 'min:0']
        ]);

        try {
            $nuevoEstado = ProductoEstado::from($validated['estado']);
            
            DB::transaction(function () use ($producto, $nuevoEstado, $validated) {
                $producto->cambiarEstado($nuevoEstado);

                if (!empty($validated['motivo'])) {
                    $this->inventarioService->ajustarStock(
                        $producto,
                        $validated['cantidad'] ?? 0,
                        "Cambio de estado: {$producto->estado->label()} → {$nuevoEstado->label()}. {$validated['motivo']}"
                    );
                }
            });

            return back()
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Estado actualizado correctamente'
                ]);

        } catch (\RuntimeException $e) {
            return back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    public function destroy(Producto $producto)
    {
        try {
            throw_if(
                $producto->estado->esEstadoFinal(),
                \RuntimeException::class,
                'No se puede eliminar un producto en estado final'
            );

            $producto->delete();

            return redirect()
                ->route('productos.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Producto eliminado correctamente'
                ]);

        } catch (\RuntimeException $e) {
            return back()
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    public function restore($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);
        $producto->restore();

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' => 'Producto restaurado correctamente'
            ]);
    }

    protected function processMetadata(?string $metadata): ?array
    {
        if (empty($metadata)) {
            return null;
        }

        $decoded = json_decode($metadata, true);

        return is_array($decoded) ? array_filter($decoded) : null;
    }
}
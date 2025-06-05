<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Movimiento;
use App\Enums\EstadoProducto;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;

class ProductoController extends Controller
{
    /**
     * Muestra el listado de productos con opciones de filtrado
     */
    public function index(Request $request)
    {
        // Consulta base con relaciones y conteo de movimientos
        $query = Producto::query()
            ->with(['categoria', 'marca'])
            ->withCount('movimientos')
            ->orderBy('nombre');

        // Aplica filtro de búsqueda si existe
        if ($request->has('search')) {
            $query->buscar($request->search);
        }

        // Filtra por estado si se especifica
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Pagina los resultados
        $productos = $query->paginate(10);
        $estados = EstadoProducto::cases(); // Todos los estados posibles

        return view('productos.index', compact('productos', 'estados'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto
     */
    public function create()
    {
        // Obtiene datos necesarios para los selects del formulario
        $categorias = Categoria::activas()->orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        $estados = EstadoProducto::cases();

        return view('productos.create', compact('categorias', 'marcas', 'estados'));
    }

    /**
     * Almacena un nuevo producto en la base de datos
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            $producto = Producto::create($request->validated());
            
            return redirect()
                ->route('productos.show', $producto)
                ->with('success', 'Producto creado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }    
    /**
     * Registra un movimiento de entrada/salida para el producto
     */
    public function registrarMovimiento(Request $request, Producto $producto)
    {
        // Valida los datos del formulario
        $request->validate([
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255'
        ]);

        try {
            // Determina el tipo de movimiento
            $tipo = $request->tipo === 'entrada' 
                ? TipoMovimiento::ENTRADA 
                : TipoMovimiento::SALIDA;

            // Registra el movimiento usando el método del modelo
            Movimiento::registrar(
                $producto,
                $tipo,
                $request->cantidad,
                $request->motivo
            );

            return redirect()
                ->route('productos.show', $producto)
                ->with('success', 'Movimiento registrado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar el movimiento: ' . $e->getMessage());
        }
    }
}
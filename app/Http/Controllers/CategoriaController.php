<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;

class CategoriaController extends Controller
{
    /**
     * Muestra un listado paginado de categorías
     */
    public function index(Request $request)
    {
        // Construye la consulta base con conteo de productos
        $query = Categoria::query()
            ->withCount('productos')
            ->activas() // Usa el scope para solo categorías activas
            ->orderBy('nombre');

        // Aplica filtro de búsqueda si existe
        if ($request->has('search')) {
            $query->porNombre($request->search);
        }

        // Pagina los resultados (10 por página)
        $categorias = $query->paginate(10);

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Muestra el formulario para crear una nueva categoría
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Almacena una nueva categoría en la base de datos
     */
    public function store(StoreCategoriaRequest $request)
    {
        try {
            Categoria::create($request->validated());
            
            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría creada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Muestra los detalles de una categoría específica
     */
    public function show(Categoria $categoria)
    {
        // Carga los productos relacionados ordenados por nombre
        $categoria->load(['productos' => function($query) {
            $query->with('marca')->orderBy('nombre');
        }]);

        return view('categorias.show', compact('categoria'));
    }

    /**
     * Muestra el formulario para editar una categoría
     */
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Actualiza una categoría existente en la base de datos
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        try {
            $categoria->update($request->validated());
            
            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría actualizada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Elimina (soft delete) una categoría de la base de datos
     */
    public function destroy(Categoria $categoria)
    {
        try {
            // Verifica si tiene productos asociados
            if ($categoria->productos()->exists()) {
                return back()
                    ->with('error', 'No se puede eliminar la categoría porque tiene productos asociados');
            }

            $categoria->delete();
            
            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría eliminada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }
}

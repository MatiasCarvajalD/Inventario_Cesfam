<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;

class CategoriaController extends Controller
{
    /**
     * Muestra listado paginado con búsqueda
     */
    public function index(Request $request)
    {
        $categorias = Categoria::query()
            ->withCount('productos')
            ->when($request->search, fn($q) => $q->where('nombre', 'LIKE', "%{$request->search}%"))
            ->orderBy('nombre')
            ->paginate(10);

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Muestra formulario de creación
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Almacena nueva categoría con validación
     */
    public function store(StoreCategoriaRequest $request)
    {
        try {
            Categoria::create($request->validated());
            return redirect()->route('categorias.index')->with('success', '¡Categoría creada!');
        } catch (\Exception $e) {
            return back()->with('error', "Error: {$e->getMessage()}");
        }
    }

    /**
     * Muestra detalles con productos asociados
     */
    public function show(Categoria $categoria)
    {
        $categoria->load(['productos' => fn($q) => $q->with('marca')->latest()]);
        return view('categorias.show', compact('categoria'));
    }

    /**
     * Muestra formulario de edición
     */
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Actualiza categoría existente
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        try {
            $categoria->update($request->validated());
            return redirect()->route('categorias.index')->with('success', '¡Cambios guardados!');
        } catch (\Exception $e) {
            return back()->with('error', "Error: {$e->getMessage()}");
        }
    }

    /**
     * Elimina categoría (soft delete)
     */
    public function destroy(Categoria $categoria)
    {
        try {
            if ($categoria->productos()->exists()) {
                throw new \Exception('No se puede eliminar: tiene productos asociados');
            }
            
            $categoria->delete();
            return redirect()->route('categorias.index')->with('success', '¡Categoría eliminada!');
        } catch (\Exception $e) {
            return back()->with('error', "Error: {$e->getMessage()}");
        }
    }
}
@extends('layouts.app')

@section('content')
    <h1>Listado de Productos</h1>
    
    <a href="{{ route('productos.create') }}" class="btn btn-primary">
        Nuevo Producto
    </a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Stock</th>
                <th>N° Inventario</th>
                <th>N° Serie</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->descripcion }}</td>
                <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                <td>{{ $producto->marca->nombre ?? '-' }}</td>
                <td>{{ $producto->cantidad }}</td>
                <td>{{ $producto->numero_inventario }}</td>
                <td>{{ $producto->numero_serie }}</td>
                <td>{{ $producto->estado }}</td>
                <td>{{ $producto->created_at }}</td>
                <td>{{ $producto->updated_at }}</td>
                <td>
                    <a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-info">
                        Ver
                    </a>
                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-warning">
                        Editar
                    </a>
                    <form action="{{ route('productos.destroy', $producto) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $productos->links() }}
@endsection
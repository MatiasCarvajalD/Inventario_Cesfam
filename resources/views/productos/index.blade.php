@extends('layouts.app')

@section('content')
    <h1>Listado de Productos</h1>
    
    <a href="{{ route('productos.create') }}" class="btn btn-primary">
        Nuevo Producto
    </a>

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            <tr>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->categoria->nombre }}</td>
                <td>{{ $producto->cantidad }}</td>
                <td>
                    <a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-info">
                        Ver
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $productos->links() }}
@endsection
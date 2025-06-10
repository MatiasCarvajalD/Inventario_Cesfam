@extends('layouts.app')

@section('content')
    <h1>Detalle del Producto</h1>
    <div class="mb-3">
        <strong>Nombre:</strong> {{ $producto->nombre }}
    </div>
    <div class="mb-3">
        <strong>Categoría:</strong> {{ $producto->categoria->nombre }}
    </div>
    <div class="mb-3">
        <strong>Stock:</strong> {{ $producto->cantidad }}
    </div>
    <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver</a>
    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-warning">Editar</a>
@endsection
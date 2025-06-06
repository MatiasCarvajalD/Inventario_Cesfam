<?php

namespace App\Http\Requests;

use App\Enums\ProductoEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_serie' => 'nullable|string|max:100|unique:productos',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'cantidad' => 'nullable|integer|min:0',
            'modelo' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:100',
            'estado' => ['required', Rule::in(ProductoEstado::values())],
            'categoria_id' => 'nullable|exists:categorias,id',
            'marca_id' => 'nullable|exists:marcas,id',
            'metadata' => 'nullable|json',
        ];
    }

    public function messages(): array
    {
        return [
            'estado.in' => 'El estado seleccionado no es válido',
            'categoria_id.exists' => 'La categoría seleccionada no existe',
            'marca_id.exists' => 'La marca seleccionada no existe',
        ];
    }
}
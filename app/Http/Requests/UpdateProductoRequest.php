<?php

namespace App\Http\Requests;

use App\Enums\ProductoEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends StoreProductoRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'numero_serie' => 'nullable|string|max:100|unique:productos,numero_serie,'.$this->producto->id,
        ]);
    }
}
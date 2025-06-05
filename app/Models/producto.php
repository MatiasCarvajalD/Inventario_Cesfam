<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\EstadoProducto;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'numero_inventario',
        'numero_serie',
        'nombre',
        'descripcion',
        'cantidad',
        'modelo',
        'ubicacion',
        'estado',
        'categoria_id',
        'marca_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'estado' => EstadoProducto::class,
        'cantidad' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Relación con la categoría
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id')->withDefault([
            'nombre' => 'Sin categoría'
        ])->withTrashed();
    }

    /**
     * Relación con la marca
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id')->withDefault([
            'nombre' => 'Sin marca'
        ])->withTrashed();
    }

    /**
     * Relación con los movimientos
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'producto_id');
    }

    /**
     * Scope para productos disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', EstadoProducto::DISPONIBLE->value);
    }

    /**
     * Scope para búsqueda avanzada
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where('nombre', 'like', "%{$termino}%")
            ->orWhere('numero_inventario', 'like', "%{$termino}%")
            ->orWhere('numero_serie', 'like', "%{$termino}%")
            ->orWhere('modelo', 'like', "%{$termino}%")
            ->orWhereHas('categoria', function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%");
            })
            ->orWhereHas('marca', function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%");
            });
    }

    /**
     * Verificar si el producto está disponible
     */
    public function estaDisponible(): bool
    {
        return $this->estado === EstadoProducto::DISPONIBLE && $this->cantidad > 0;
    }

    /**
     * Obtener la ubicación formateada
     */
    public function ubicacionFormateada(): string
    {
        return $this->ubicacion ?? 'Sin ubicación asignada';
    }
}
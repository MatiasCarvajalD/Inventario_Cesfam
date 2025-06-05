<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
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
     * Comportamientos al iniciar el modelo
     */
    protected static function booted()
    {
        // Genera número de inventario automáticamente si no existe
        static::creating(function ($producto) {
            if (empty($producto->numero_inventario)) {
                $ultimoId = self::withTrashed()->max('id') ?? 0;
                $producto->numero_inventario = 'INV-'.date('Y').'-'.str_pad($ultimoId + 1, 4, '0', STR_PAD_LEFT);
            }
        });

        // Previene eliminar productos con movimientos asociados
        static::deleting(function ($producto) {
            if ($producto->movimientos()->exists()) {
                throw new \Exception('No se puede eliminar: tiene movimientos registrados');
            }
        });
    }

    /**
     * Relación con la categoría (opcional)
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id')
            ->withDefault(['nombre' => 'Sin categoría'])
            ->withTrashed();
    }

    /**
     * Relación con la marca (opcional)
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id')
            ->withDefault(['nombre' => 'Sin marca'])
            ->withTrashed();
    }

    /**
     * Relación con los movimientos (historial)
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'producto_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Productos disponibles
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', EstadoProducto::DISPONIBLE->value);
    }

    /**
     * Scope: Búsqueda avanzada
     */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where('nombre', 'like', "%{$termino}%")
            ->orWhere('numero_inventario', 'like', "%{$termino}%")
            ->orWhere('numero_serie', 'like', "%{$termino}%")
            ->orWhere('modelo', 'like', "%{$termino}%")
            ->orWhereHas('categoria', fn($q) => $q->where('nombre', 'like', "%{$termino}%"))
            ->orWhereHas('marca', fn($q) => $q->where('nombre', 'like', "%{$termino}%"));
    }

    /**
     * Scope: Productos con bajo stock
     */
    public function scopeBajoStock(Builder $query, int $nivel = 5): Builder
    {
        return $query->where('cantidad', '<=', $nivel);
    }

    /**
     * Verifica si el producto está disponible
     */
    public function estaDisponible(): bool
    {
        return $this->estado === EstadoProducto::DISPONIBLE && $this->cantidad > 0;
    }

    /**
     * Obtiene la ubicación formateada
     */
    public function ubicacionFormateada(): string
    {
        return $this->ubicacion ?? 'Sin ubicación asignada';
    }

    /**
     * Cambia el estado del producto con validación
     * @throws \Exception Si el producto está dado de baja
     */
    public function cambiarEstado(EstadoProducto $estado): void
    {
        if ($this->estado === EstadoProducto::BAJA) {
            throw new \Exception('Producto dado de baja no puede cambiar estado');
        }
        
        $this->update(['estado' => $estado]);
    }

    /**
     * Registra un movimiento y actualiza el stock
     */
    public function registrarMovimiento(
        string $tipo, 
        int $cantidad, 
        string $motivo = null
    ): Movimiento {
        return Movimiento::registrar($this, $tipo, $cantidad, $motivo);
    }
}
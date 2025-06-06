<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ProductoEstado;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_serie',
        'numero_inventario', 
        'nombre',
        'descripcion',
        'cantidad',
        'modelo',
        'ubicacion',
        'estado',
        'categoria_id',
        'marca_id',
        'metadata'
    ];

    protected $casts = [
        'estado' => ProductoEstado::class,
        'metadata' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Atributo computado modernizado (Laravel 12)
    protected function numeroInventario(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?: 'INV-'.now()->format('Y').'-'.str($this->id ?? self::count() + 1)->padLeft(4, '0'),
            set: fn (string $value) => strtoupper($value)
        )->shouldCache();
    }

    // Relaciones con sintaxis mejorada
    public function categoria()
    {
        return $this->belongsTo(Categoria::class)
            ->withDefault(['nombre' => 'Sin categoría'])
            ->withTrashed();
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class)
            ->withDefault(['nombre' => 'Sin marca'])
            ->withTrashed();
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class)
            ->orderByDesc('created_at')
            ->cacheFor(now()->addHours(6)); // Cache de relación
    }

    // Búsqueda full-text con soporte Laravel 12
    public function scopeBusquedaAvanzada($query, string $termino)
    {
        return $query->when($termino, fn($q) => $q->whereFullText([
            'nombre', 'descripcion', 'numero_inventario', 'numero_serie'
        ], $termino));
    }

    // Métodos de negocio mejorados
    public function registrarMovimiento(string $tipo, int $cantidad, ?string $motivo = null): Movimiento
    {
        return Movimiento::registrar($this, TipoMovimiento::from($tipo), $cantidad, $motivo);
    }

    public function actualizarStock(int $nuevaCantidad, bool $forzar = false): void
    {
        throw_if(
            !$forzar && !$this->estado->permiteMovimientos(),
            \RuntimeException::class,
            'No se puede modificar el stock en el estado actual: '.$this->estado->label()
        );

        throw_if(
            $nuevaCantidad < 0,
            \InvalidArgumentException::class,
            'La cantidad no puede ser negativa'
        );

        $this->forceFill(['cantidad' => $nuevaCantidad])->save();
    }

    /**
     * Verifica si el producto está disponible para movimientos
     * (Ahora usa el Enum directamente para mayor consistencia)
     */
    public function estaDisponible(): bool
    {
        return $this->estado === ProductoEstado::DISPONIBLE;
    }

    /**
     * Versión mejorada que considera múltiples estados como "disponible"
     */
    public function estaDisponiblePara(string $accion): bool
    {
        return match($accion) {
            'venta' => in_array($this->estado, [
                ProductoEstado::DISPONIBLE,
                ProductoEstado::RESERVADO
            ]),
            'movimiento' => $this->estado->permiteMovimientos(),
            default => $this->estaDisponible(),
        };
    }
    public function cambiarEstado(ProductoEstado $nuevoEstado): void
    {
        throw_unless(
            $this->estado->puedeTransicionarA($nuevoEstado),
            \RuntimeException::class,
            "Transición no permitida de {$this->estado->label()} a {$nuevoEstado->label()}"
        );

        $this->update(['estado' => $nuevoEstado]);
    }

    public function scopePorEstado($query, ProductoEstado $estado)
    {
        return $query->where('estado', $estado->value);
    }
}
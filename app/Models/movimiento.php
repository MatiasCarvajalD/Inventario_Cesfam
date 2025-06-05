<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\TipoMovimiento;

class Movimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'movimientos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'motivo'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'tipo' => TipoMovimiento::class,
        'cantidad' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Relación con el producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }

    /**
     * Scope para movimientos de entrada
     */
    public function scopeEntradas(Builder $query): Builder
    {
        return $query->where('tipo', TipoMovimiento::ENTRADA->value);
    }

    /**
     * Scope para movimientos de salida
     */
    public function scopeSalidas(Builder $query): Builder
    {
        return $query->where('tipo', TipoMovimiento::SALIDA->value);
    }

    /**
     * Scope para movimientos recientes
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Registrar un nuevo movimiento y actualizar stock
     */
    public static function registrar(
        Producto $producto,
        TipoMovimiento $tipo,
        int $cantidad,
        ?string $motivo = null
    ): self {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero');
        }

        if ($tipo === TipoMovimiento::SALIDA && $producto->cantidad < $cantidad) {
            throw new \RuntimeException('Stock insuficiente para este movimiento');
        }

        $movimiento = self::create([
            'producto_id' => $producto->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'motivo' => $motivo
        ]);

        // Actualizar stock del producto
        $tipo === TipoMovimiento::ENTRADA
            ? $producto->increment('cantidad', $cantidad)
            : $producto->decrement('cantidad', $cantidad);

        return $movimiento;
    }

    /**
     * Obtener el tipo formateado
     */
    public function tipoFormateado(): string
    {
        return $this->tipo === TipoMovimiento::ENTRADA 
            ? 'Entrada de inventario' 
            : 'Salida de inventario';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class movimiento extends Model
{
    use hasFactory;
    protected $table = 'movimientos';
    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'fecha_movimiento',
        'usuario_id',
        'observaciones'
    ];
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}

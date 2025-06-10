<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tipo_Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_productos';
    protected $primaryKey = 'id';

    protected $fillable = ['nombre'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Relación con productos usando sintaxis simplificada de Laravel 12
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'tipo_productos_id')
            ->withTrashed()
            ->cacheFor(now()->addDay()); // Nuevo en Laravel 12: cache de relaciones
    }

    // Scopes usando sintaxis estándar de métodos
    public function scopePorNombre($query, string $nombre)
    {
        return $query->where('nombre', 'like', "%{$nombre}%");
    }

    public function scopeActivas($query)
    {
        return $query->whereNull('deleted_at');
    }
}
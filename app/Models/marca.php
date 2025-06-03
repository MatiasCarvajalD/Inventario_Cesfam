<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class marca extends Model
{
    use hasFactory;
    protected $table = 'marcas';
    protected $fillable = ['marca'];
    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }
    public function scopeSearch($query, $search)
    {
        return $query->where('marca', 'like', '%' . $search . '%');
    }
    public function scopeFilter($query, $filters)
    {
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }
        return $query;
    }
        
}

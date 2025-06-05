<?php

namespace App\Enums;

enum EstadoProducto: string
{
    case DISPONIBLE = 'Disponible';
    case EN_USO = 'En Uso';
    case MANTENIMIENTO = 'Mantenimiento';
    case BAJA = 'Baja';

    public function color(): string
    {
        return match($this) {
            self::DISPONIBLE => 'green',
            self::EN_USO => 'blue',
            self::MANTENIMIENTO => 'yellow',
            self::BAJA => 'red',
        };
    }
}
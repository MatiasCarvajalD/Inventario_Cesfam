<?php

namespace App\Enums;

enum TipoMovimiento: string
{
    case ENTRADA = 'entrada';
    case SALIDA = 'salida';

    public function icono(): string
    {
        return match($this) {
            self::ENTRADA => '↑',
            self::SALIDA => '↓',
        };
    }
}
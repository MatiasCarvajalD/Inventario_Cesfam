<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum ProductoEstado: string
{
    case DISPONIBLE = 'disponible';
    case EN_USO = 'en_uso';
    case MANTENIMIENTO = 'mantenimiento';
    case BAJA = 'baja';
    case RESERVADO = 'reservado';
    case PENDIENTE_REVISION = 'pendiente_revision';

    // 1. Métodos básicos de representación
    public function label(): string
    {
        return match($this) {
            self::DISPONIBLE => 'Disponible',
            self::EN_USO => 'En uso',
            self::MANTENIMIENTO => 'En mantenimiento',
            self::BAJA => 'Dado de baja',
            self::RESERVADO => 'Reservado',
            self::PENDIENTE_REVISION => 'Pendiente de revisión',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DISPONIBLE => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::EN_USO => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::MANTENIMIENTO => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::BAJA => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            self::RESERVADO => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            self::PENDIENTE_REVISION => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::DISPONIBLE => '✓',
            self::EN_USO => '↻',
            self::MANTENIMIENTO => '🛠️',
            self::BAJA => '✗',
            self::RESERVADO => '⏳',
            self::PENDIENTE_REVISION => '?',
        };
    }

    // 2. Lógica de negocio
    public function permiteMovimientos(): bool
    {
        return match($this) {
            self::BAJA, self::MANTENIMIENTO, self::PENDIENTE_REVISION => false,
            default => true,
        };
    }

    public function permiteModificaciones(): bool
    {
        return !in_array($this, [self::BAJA]);
    }

    public function esEstadoFinal(): bool
    {
        return $this === self::BAJA;
    }

    // 3. Métodos para transiciones de estado
    public function transicionesPermitidas(): array
    {
        return match($this) {
            self::DISPONIBLE => [self::EN_USO, self::RESERVADO, self::MANTENIMIENTO, self::BAJA],
            self::EN_USO => [self::DISPONIBLE, self::MANTENIMIENTO, self::BAJA],
            self::RESERVADO => [self::DISPONIBLE, self::EN_USO, self::BAJA],
            self::MANTENIMIENTO => [self::DISPONIBLE, self::BAJA],
            self::PENDIENTE_REVISION => [self::DISPONIBLE, self::MANTENIMIENTO, self::BAJA],
            self::BAJA => [],
        };
    }

    public function puedeTransicionarA(ProductoEstado $nuevoEstado): bool
    {
        return in_array($nuevoEstado, $this->transicionesPermitidas());
    }

    // 4. Métodos de contexto específico
    public function esDisponiblePara(string $contexto): bool
    {
        return match($contexto) {
            'venta' => in_array($this, [self::DISPONIBLE, self::RESERVADO]),
            'prestamo' => $this === self::DISPONIBLE,
            'mantenimiento' => !in_array($this, [self::BAJA, self::MANTENIMIENTO]),
            'reporte' => !$this->esEstadoFinal(),
            default => $this === self::DISPONIBLE,
        };
    }

    // 5. Métodos estáticos útiles
    public static function paraSelect(): Collection
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($estado) => [$estado->value => $estado->label()]);
    }

    public static function valoresActivos(): array
    {
        return [
            self::DISPONIBLE->value,
            self::EN_USO->value,
            self::RESERVADO->value,
            self::PENDIENTE_REVISION->value,
        ];
    }

    public static function valoresInactivos(): array
    {
        return [
            self::BAJA->value,
            self::MANTENIMIENTO->value,
        ];
    }

    // 6. Soporte para Laravel
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value) ?? throw new \InvalidArgumentException("Estado inválido: {$value}");
    }
}
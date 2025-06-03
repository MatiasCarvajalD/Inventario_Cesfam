<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            // 1. IDs
            $table->id()->comment('Clave primaria');
            
            // 2. Relación con producto
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('cascade')
                ->comment('Producto asociado al movimiento');
            
            // 3. Datos del movimiento
            $table->enum('tipo', ['entrada', 'salida'])->comment('Tipo de movimiento');
            $table->integer('cantidad')->unsigned()->comment('Cantidad afectada (siempre positiva)');
            $table->string('motivo')->nullable()->comment('Razón del movimiento (Ej: "Compra", "Venta")');
            
            // 4. Metadata
            $table->timestamps();
            $table->softDeletes();
            
            // 5. Índices
            $table->index('tipo');
            $table->index('producto_id');});
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
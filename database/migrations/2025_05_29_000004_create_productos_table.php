<?php

use App\Enums\ProductoEstado;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id()->comment('ID técnico autoincrementable');
            
            // Identificadores
            $table->string('numero_serie')->unique()->nullable()
                ->comment('Número de serie del fabricante');
            $table->string('numero_inventario')->unique()->nullable()
                ->comment('ID interno asignado por la organización');

            // Datos básicos
            $table->string('nombre')->fulltext()->comment('Nombre descriptivo del producto');
            $table->text('descripcion')->fulltext()->nullable();
            $table->integer('cantidad')->default(0)->comment('Stock actual');
            $table->string('modelo')->nullable();
            $table->string('ubicacion')->nullable();

            // Estado usando Enum
            $table->enum('estado', \App\Enums\ProductoEstado::values())
                ->default(\App\Enums\ProductoEstado::DISPONIBLE->value)
                ->comment('Estado actual del producto según enumeración');

            // Relaciones
            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->nullOnDelete();
                
            $table->foreignId('marca_id')
                ->nullable()
                ->constrained('marcas')
                ->nullOnDelete();

            // Metadata para atributos dinámicos
            $table->json('metadata')->nullable();

            // Timestamps con precisión mejorada
            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);

            // Índices optimizados
            $table->index(['numero_serie', 'numero_inventario'], 'productos_identificadores_idx');
            $table->index('estado');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
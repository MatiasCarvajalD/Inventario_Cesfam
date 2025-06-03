<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            // ===== 1. CLAVES =====
            $table->id()->comment('ID técnico autoincrementable');
            
            // Identificadores de negocio
            $table->string('numero_serie')->unique()
                  ->nullable()
                  ->comment('Número de serie del fabricante');
                  
            $table->string('numero_inventario')
                  ->unique()
                  ->nullable()
                  ->comment('ID interno asignado por la organización');

            // ===== 2. DATOS BÁSICOS =====
            $table->string('nombre')->comment('Nombre descriptivo del producto');
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(0)->comment('Stock actual');
            $table->string('modelo')->nullable();
            $table->string('ubicacion')->nullable();

            // ===== 3. ESTADO (ENUM) =====
            $table->enum('estado', [
                'Disponible', 
                'En Uso', 
                'Mantenimiento', 
                'Baja'
            ])->default('Disponible')->comment('Estado físico del producto');

            // ===== 4. RELACIONES =====
            $table->foreignId('categoria_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->nullOnDelete()
                  ->comment('Categorización del producto');
                  
            $table->foreignId('marca_id')
                  ->nullable()
                  ->constrained('marcas')
                  ->nullOnDelete()
                  ->comment('Marca asociada');

            // ===== 5. METADATOS =====
            $table->timestamps();
            $table->softDeletes();

            // ===== 6. ÍNDICES =====
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
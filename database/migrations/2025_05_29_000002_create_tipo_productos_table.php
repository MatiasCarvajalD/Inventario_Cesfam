<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_producto', function (Blueprint $table) {
            // ID y nombre
            $table->id()->comment('Clave primaria autoincrementable');
            $table->string('nombre')->unique()->comment('Nombre único de la categoría (Ej: "Electrónicos")');
            
            // Metadata
            $table->timestamps();
            $table->softDeletes()->comment('Fecha de eliminación suave');
            
            // Índices
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
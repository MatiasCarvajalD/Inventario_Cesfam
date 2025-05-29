<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    Schema::create('productos', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        $table->string('producto')->nullable();
        $table->string('nombre');
        $table->text('descripcion')->nullable();
        $table->integer('cantidad')->default(0);
        
        $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
        $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
        
        $table->string('Numero_Serie')->unique()->nullable();
        $table->string('Modelo')->nullable();
        $table->string("Numero_Inventario")->unique()->nullable();
        $table->string('Ubicacion')->nullable();
        $table->string('Estado')->default('Disponible');
    });

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Crear una tabla short_links con 5 parametros, esto nos dice que los links acortados no tienen fecha de expiracion
         * si lo tuvieran tendrian timestamps expire_at
         */
        Schema::create('short_links',function (Blueprint $table) {
             $table -> id('id');
             $table -> text('long_url');
             $table -> string('short_code',10)->unique(); // Codigo para crear la url pequeña, unique() define un indice para buscar mas rapido, obvio verificando que este atributo va a ser importante para busquedas
             $table -> unsignedInteger('clicks')->default(0); // Agregar un integer por los clicks y por defecto 0
             $table -> timestamp('expires_at')->nullable(); // Fecha de expiracion si es necesario, se usa timestamp OJO
             $table -> timestamps(); // Fecha de creación
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema:: dropIfExists('short_links');
    }
};

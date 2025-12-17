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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario dueño del subdominio
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // El subdominio (ej: "milink") debe ser único en todo el sistema
            $table->string('subdomain')->unique();

            // Opcional: Para saber si es el dominio principal del usuario
            $table->boolean('default')->default(false);

            $table->timestamps();
        });

        Schema::create('user_short_links', function (Blueprint $table) {
            $table->id();

            // Relación con la tabla domains. 
            // Esto es vital: El enlace pertenece a un DOMINIO, no solo a un usuario.
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');

            $table->string('short_code'); // Ej: jt124
            $table->text('original_url'); // Usamos text por si la URL es muy larga
            $table->string('etiquetas')->nullable();

            // Contador simple (cache) para no contar en la tabla clicks cada vez
            $table->unsignedBigInteger('clicks')->default(0);

            $table->timestamps();

            // REGLA DE ORO: Un mismo código no puede repetirse dentro del mismo dominio.
            $table->unique(['domain_id', 'short_code']);
        });

        Schema::create('clicks', function (Blueprint $table) {
            $table->id();

            // Relación con el enlace específico
            $table->foreignId('user_short_link_id')->constrained('user_short_links')->onDelete('cascade');

            $table->timestamp('clicked_at')->useCurrent();
            $table->string('ip_address', 45)->nullable(); // 45 caracteres soporta IPv6
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // Ej: Windows, iOS
            $table->string('referer')->nullable(); // De dónde vienen

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Eliminar en orden inverso a la creación
        Schema::dropIfExists('clicks');
        Schema::dropIfExists('user_short_links');
        Schema::dropIfExists('domains');
    }
};

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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subdomain')->unique();
            $table->boolean('default')->default(false);
            $table->timestamps();
        });

        Schema::create('user_short_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->onDelete('cascade');
            $table->string('short_code'); 
            $table->text('original_url'); 
            $table->string('etiquetas')->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->unique(['domain_id', 'short_code']);
        });

        Schema::create('clicks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_short_link_id')->constrained('user_short_links')->onDelete('cascade');

            $table->timestamp('clicked_at')->useCurrent();
            $table->string('ip_address', 45)->nullable(); 
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); 
            $table->string('referer')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('clicks');
        Schema::dropIfExists('user_short_links');
        Schema::dropIfExists('domains');
    }
};

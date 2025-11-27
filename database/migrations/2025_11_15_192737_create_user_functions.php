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
            $table->unsignedBigInteger('user_id');
            $table->string('subdomain')->unique();
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        Schema::create('user_short_links',function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('domain_id');
            $table->string('short_code');
            $table->string('original_url');
            $table->string('etiquetas')->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });

        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_short_link_id'); 
            $table->timestamp('clicked_at')->useCurrent();
            $table->string('ip_address')->nullable(); 
            $table->string('browser')->nullable(); 
            $table->string('method')->nullable(); 
            $table->string('status')->nullable(); 
            $table->timestamps();
            $table->foreign('user_short_link_id')->references('id')->on('user_short_links')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
        Schema::dropIfExists('clicks');
        Schema::dropIfExists('user_short_links');
    }
};


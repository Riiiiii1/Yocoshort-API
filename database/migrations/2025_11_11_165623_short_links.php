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

        Schema::create('short_links',function (Blueprint $table) {
             $table -> id('id');
             $table -> text('long_url');
             $table -> string('short_code',10)->unique(); 
             $table -> unsignedInteger('clicks')->default(0); 
             $table -> timestamp('expires_at')->nullable(); 
             $table -> timestamps();
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

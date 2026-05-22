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
    Schema::create('user_profiles', function (Blueprint $table) {
        $table->id();
        // Relación con el usuario original
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Campos de lógica de acceso
        $table->string('seccion')->default('general'); 
        $table->integer('nivel')->default(1);
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

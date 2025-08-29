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
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['software', 'redes', 'hardware', 'otros']);
            $table->string('categoria', 100)->nullable();
            $table->enum('estado', ['Planificado', 'En Ejecución', 'En Auditoría', 'Finalizado'])->default('Planificado');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin_estimada')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->decimal('porcentaje_avance', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

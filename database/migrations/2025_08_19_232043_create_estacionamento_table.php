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
        Schema::create('estacionamento', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('motorista_id');
            $table->dateTime('entrada');
            $table->dateTime('saida')->nullable();
            $table->string('tipo_pagamento', 20)->nullable();
            $table->decimal('valor_pagamento', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estacionamento');
    }
};

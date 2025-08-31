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
        Schema::table('estacionamento', function (Blueprint $table) {
            $table->enum('tipo_veiculo', ['truck_ls', 'bitrem'])->nullable()->after('tipo_pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estacionamento', function (Blueprint $table) {
            $table->dropColumn('tipo_veiculo');
        });
    }
};

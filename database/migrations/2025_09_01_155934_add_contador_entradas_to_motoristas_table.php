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
        Schema::table('motorista', function (Blueprint $table) {
            $table->integer('contador_entradas')->default(0)->after('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motorista', function (Blueprint $table) {
            $table->dropColumn('contador_entradas');
        });
    }
};

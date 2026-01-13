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
            $table->integer('quantidade_tickets')->default(1)->after('valor_pagamento');
            $table->integer('tickets_pagos')->default(1)->after('quantidade_tickets');
            $table->integer('tickets_gratuitos')->default(0)->after('tickets_pagos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estacionamento', function (Blueprint $table) {
            $table->dropColumn(['quantidade_tickets', 'tickets_pagos', 'tickets_gratuitos']);
        });
    }
};

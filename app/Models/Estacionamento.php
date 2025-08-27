<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estacionamento extends Model
{
    use HasFactory;
    protected $table = 'estacionamento';
    protected $fillable = [
        'motorista_id',
        'entrada',
        'saida',
        'tipo_pagamento',
        'valor_pagamento',
        'valor',
    ];

    public function motorista()
    {
        return $this->belongsTo(Motorista::class);
    }
}

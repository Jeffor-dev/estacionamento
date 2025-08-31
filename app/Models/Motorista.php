<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motorista extends Model
{
    use HasFactory;
    protected $table = 'motorista';
    protected $fillable = [
        'nome',
        'cpf',
        'tipo_documento',
        'telefone',
        'empresa',
        'observacao',
        'caminhao_id'
    ];

    public function caminhao()
    {
        return $this->belongsTo(Caminhao::class, 'caminhao_id');
    }
}

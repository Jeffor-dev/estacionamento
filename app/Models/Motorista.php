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
        'caminhao_id',
        'contador_entradas'
    ];

    public function caminhao()
    {
        return $this->belongsTo(Caminhao::class, 'caminhao_id');
    }

    public function temDireitoGratuidade()
    {
        return $this->contador_entradas > 0 && $this->contador_entradas % 11 == 0;
    }

    public function proximaGratuidadeEm()
    {
        $proximo = 11 - ($this->contador_entradas % 10);
        return $proximo == 10 ? 0 : $proximo;
    }

    public function incrementarContador()
    {
        $this->increment('contador_entradas');
    }
}

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
        return $this->contador_entradas == 10;
    }

    public function proximaGratuidadeEm()
    {
        if ($this->contador_entradas >= 10) {
            return 0; // Próxima entrada será gratuita
        }
        return 10 - $this->contador_entradas;
    }

    public function incrementarContador()
    {
        $this->increment('contador_entradas');
    }
}

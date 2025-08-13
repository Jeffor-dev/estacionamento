<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caminhao extends Model
{
    use HasFactory;

    protected $table = 'caminhao';
    protected $fillable = [
        'placa',
        'modelo',
        'cor',
        'motorista_id'
    ];

    public function motorista()
    {
        return $this->belongsTo(Motorista::class, 'motorista_id');
    }
}

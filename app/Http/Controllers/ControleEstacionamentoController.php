<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ControleEstacionamentoController extends Controller
{
    public function index()
    {
        return Inertia::render('Estacionamento/Index', [
            'user' => auth()->user(),
            'title' => 'Estacionamento',
        ]);
    }
}

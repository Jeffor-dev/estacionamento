<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MotoristaController extends Controller
{
    public function index()
    {
        return Inertia::render('Motorista/Index', [
            'title' => 'Motoristas'
        ]);
    }
}

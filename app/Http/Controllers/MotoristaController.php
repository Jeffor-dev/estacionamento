<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Motorista;
use App\Models\Caminhao;

class MotoristaController extends Controller
{
    public function index()
    {
        $motoristas = Motorista::all();

        return Inertia::render('Motorista/Index', [
            'title' => 'Motoristas',
            'motoristas' => $motoristas,
        ]);
    }

    public function cadastroMotorista()
    {
        return Inertia::render('Motorista/Cadastro', [
            'title' => 'Cadastro de Motorista',
        ]);
    }

    public function cadastrar(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'telefone' => 'required|string|max:15',
            'placa' => 'required|string|max:8',
            'cor' => 'required|string|max:20',
            'modelo' => 'required|string|max:20',
        ]);

        $caminhao = Caminhao::create([
            'placa' => $request->placa,
            'modelo' => $request->modelo,
            'cor' => $request->cor,
        ]);

        $motorista = Motorista::create([
            'nome' => $request->nome,
            'cpf' => $request->cpf,
            'telefone' => $request->telefone,
            'caminhao_id' => $caminhao->id, 
        ]);

        $caminhao->motorista_id = $motorista->id;

        $caminhao->save();

        return redirect()->route('motorista.index')->with('success', 'Motorista cadastrado com sucesso!');
    }
}

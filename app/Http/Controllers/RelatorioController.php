<?php

namespace App\Http\Controllers;

use App\Models\Motorista;
use App\Models\Estacionamento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RelatorioController extends Controller
{
    public function index()
    {
        return Inertia::render('Relatorio/Index', [
            'title' => 'Relatórios',
        ]);
    }

    public function motoristasJson()
    {
        $motoristas = Motorista::with('caminhao')->get()->map(function($m) {
            return [
                'id' => $m->id,
                'nome' => $m->nome,
                'cpf' => $m->cpf,
                'telefone' => $m->telefone,
                'empresa' => $m->empresa,
                'placa' => $m->caminhao->placa ?? '',
                'modelo' => $m->caminhao->modelo ?? '',
                'cor' => $m->caminhao->cor ?? '',
            ];
        });
        return response()->json(['motoristas' => $motoristas]);
    }

    public function movimentacoesJson()
    {
        $hoje = now()->format('Y-m-d');
        $movimentacoes = Estacionamento::with('motorista.caminhao')
            ->whereDate('entrada', $hoje)
            ->orWhereDate('saida', $hoje)
            ->get()->map(function($m) {
                return [
                    'id' => $m->id,
                    'motorista' => $m->motorista->nome ?? '',
                    'placa' => $m->motorista->caminhao->placa ?? '',
                    'modelo' => $m->motorista->caminhao->modelo ?? '',
                    'cor' => $m->motorista->caminhao->cor ?? '',
                    'entrada' => $m->entrada ? date('d/m/Y H:i', strtotime($m->entrada)) : '',
                    'saida' => $m->saida ? date('d/m/Y H:i', strtotime($m->saida)) : '',
                    'valor' => $m->valor ? 'R$ ' . number_format($m->valor, 2, ',', '.') : '-',
                ];
            });
        return response()->json(['movimentacoes' => $movimentacoes]);
    }
}


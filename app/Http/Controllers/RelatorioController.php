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

    public function movimentacoesJson(Request $request)
    {
        // Usar data do parâmetro ou hoje como padrão
        $data = $request->get('data', now()->format('Y-m-d'));
        
        $movimentacoes = Estacionamento::with('motorista.caminhao')
            ->whereDate('entrada', $data)
            ->orWhereDate('saida', $data)
            ->get();
            
        // Calcular valor total
        $valorTotal = $movimentacoes->sum('valor_pagamento');
        
        $movimentacoesMapeadas = $movimentacoes->map(function($m) {
            return [
                'id' => $m->id,
                'motorista' => $m->motorista->nome ?? '',
                'modelo' => $m->motorista->caminhao->modelo ?? '',
                'placa' => $m->motorista->caminhao->placa ?? '',
                'entrada' => $m->entrada ? date('d/m/Y H:i', strtotime($m->entrada)) : '',
                'valor' => $m->valor_pagamento ? 'R$ ' . number_format($m->valor_pagamento, 2, ',', '.') : '-',
                'valor_numerico' => $m->valor_pagamento ?? 0, // Para cálculos
                'tipo_pagamento' => $m->tipo_pagamento ?? '-'
            ];
        });
        
        return response()->json([
            'movimentacoes' => $movimentacoesMapeadas,
            'valor_total' => $valorTotal,
            'valor_total_formatado' => 'R$ ' . number_format($valorTotal, 2, ',', '.')
        ]);
    }
}


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
        $turno = $request->get('turno', 1);
        
        $query = Estacionamento::with('motorista.caminhao');
        
        // Aplicar filtros de horário baseados no turno - APENAS pela ENTRADA
        switch ($turno) {
            case 1: // Turno 1: 05:15 até 22:15
                $query->whereDate('entrada', $data)
                      ->whereTime('entrada', '>=', '05:15:00')
                      ->whereTime('entrada', '<=', '22:15:00');
                break;
                
            case 2: // Turno 2: 22:15 do dia selecionado até 05:15 do dia seguinte
                $dataSeguinte = date('Y-m-d', strtotime($data . ' +1 day'));
                $query->where(function($q) use ($data, $dataSeguinte) {
                    $q->where(function($sub1) use ($data) {
                        // Entrada no dia selecionado após 22:15
                        $sub1->whereDate('entrada', $data)
                             ->whereTime('entrada', '>=', '22:15:00');
                    })->orWhere(function($sub2) use ($dataSeguinte) {
                        // OU entrada no dia seguinte até 05:15
                        $sub2->whereDate('entrada', $dataSeguinte)
                             ->whereTime('entrada', '<=', '05:15:00');
                    });
                });
                break;
        }
        
        $movimentacoes = $query->get();
            
        // Calcular valor total
        $valorTotal = $movimentacoes->sum('valor_pagamento');
        
        // Calcular totais por categoria de pagamento
        $totalDinheiro = $movimentacoes->where('tipo_pagamento', 'Dinheiro')->sum('valor_pagamento');
        $totalCartao = $movimentacoes->where('tipo_pagamento', 'Cartão')->sum('valor_pagamento');
        $totalPix = $movimentacoes->where('tipo_pagamento', 'Pix')->sum('valor_pagamento');
        $quantidadeAbastecimento = $movimentacoes->where('tipo_pagamento', 'Abastecimento')->count();
        
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
            'valor_total_formatado' => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
            'totais_por_categoria' => [
                'dinheiro' => [
                    'valor' => $totalDinheiro,
                    'formatado' => 'R$ ' . number_format($totalDinheiro, 2, ',', '.')
                ],
                'cartao' => [
                    'valor' => $totalCartao,
                    'formatado' => 'R$ ' . number_format($totalCartao, 2, ',', '.')
                ],
                'pix' => [
                    'valor' => $totalPix,
                    'formatado' => 'R$ ' . number_format($totalPix, 2, ',', '.')
                ],
                'abastecimento' => [
                    'valor' => $quantidadeAbastecimento,
                    'formatado' => $quantidadeAbastecimento
                ]
            ]
        ]);
    }

    public function relatorioMensalJson(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $ano = $request->get('ano', now()->year);
        
        // Buscar todas as movimentações do mês/ano
        $movimentacoes = Estacionamento::with('motorista.caminhao')
            ->whereMonth('entrada', $mes)
            ->whereYear('entrada', $ano)
            ->get();
            
        // Calcular estatísticas
        $quantidadeRegistros = $movimentacoes->count();
        $valorTotalArrecadado = $movimentacoes->sum('valor_pagamento');
        
        // Totais por forma de pagamento
        $totalDinheiro = $movimentacoes->where('tipo_pagamento', 'Dinheiro')->sum('valor_pagamento');
        $totalCartao = $movimentacoes->where('tipo_pagamento', 'Cartão')->sum('valor_pagamento');
        $totalPix = $movimentacoes->where('tipo_pagamento', 'Pix')->sum('valor_pagamento');
        $quantidadeAbastecimento = $movimentacoes->where('tipo_pagamento', 'Abastecimento')->count();
        
        // Estatísticas por dia (para gráfico se necessário)
        $estatisticasPorDia = $movimentacoes->groupBy(function($item) {
            return $item->entrada ? date('d', strtotime($item->entrada)) : '01';
        })->map(function($group) {
            return [
                'quantidade' => $group->count(),
                'valor_total' => $group->sum('valor_pagamento')
            ];
        });
        
        // Nome do mês em português
        $nomesMeses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        return response()->json([
            'mes' => $mes,
            'ano' => $ano,
            'nome_mes' => $nomesMeses[$mes] ?? 'Mês',
            'quantidade_registros' => $quantidadeRegistros,
            'valor_total_arrecadado' => $valorTotalArrecadado,
            'valor_total_formatado' => 'R$ ' . number_format($valorTotalArrecadado, 2, ',', '.'),
            'totais_por_forma_pagamento' => [
                'dinheiro' => [
                    'valor' => $totalDinheiro,
                    'formatado' => 'R$ ' . number_format($totalDinheiro, 2, ',', '.')
                ],
                'cartao' => [
                    'valor' => $totalCartao,
                    'formatado' => 'R$ ' . number_format($totalCartao, 2, ',', '.')
                ],
                'pix' => [
                    'valor' => $totalPix,
                    'formatado' => 'R$ ' . number_format($totalPix, 2, ',', '.')
                ],
                'abastecimento' => [
                    'quantidade' => $quantidadeAbastecimento,
                    'formatado' => $quantidadeAbastecimento . ' registros'
                ]
            ],
            'estatisticas_por_dia' => $estatisticasPorDia
        ]);
    }
}


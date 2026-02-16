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
        $turno = $request->get('turno', 1); // Padrão turno 1
        
        // Buscar movimentações baseado no turno
        if ($turno == 1) {
            // Turno 1: 05:00 até 21:59 do mesmo dia
            $movimentacoes = Estacionamento::with('motorista.caminhao')
                ->where(function($query) use ($data) {
                    $query->whereBetween('entrada', [
                        $data . ' 05:00:00',
                        $data . ' 21:59:59'
                    ]);
                })
                ->get();
        } else {
            // Turno 2: 22:00 de um dia até 04:59 do dia seguinte
            // Para o turno 2, a data selecionada é considerada como o dia de início
            $dataInicio = $data . ' 22:00:00';
            $dataFim = date('Y-m-d', strtotime($data . ' +1 day')) . ' 04:59:59';
            
            $movimentacoes = Estacionamento::with('motorista.caminhao')
                ->where(function($query) use ($dataInicio, $dataFim) {
                    $query->whereBetween('entrada', [$dataInicio, $dataFim]);
                })
                ->get();
        }
            
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
                'tickets' => $m->quantidade_tickets ?? 0,
                'valor' => $m->valor_pagamento ? 'R$ ' . number_format($m->valor_pagamento, 2, ',', '.') : '-',
                'valor_numerico' => $m->valor_pagamento ?? 0, // Para cálculos
                'tipo_pagamento' => $m->tipo_pagamento ?? '-'
            ];
        });
        
        return response()->json([
            'movimentacoes' => $movimentacoesMapeadas,
            'valor_total' => $valorTotal,
            'valor_total_formatado' => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
            'turno' => $turno,
            'data_referencia' => $data,
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


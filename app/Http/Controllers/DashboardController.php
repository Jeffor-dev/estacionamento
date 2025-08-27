<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\Estacionamento;
use App\Models\Motorista;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        // Lucro do dia
        $lucroDia = Estacionamento::whereDate('entrada', $hoje)
            ->whereNotNull('saida')
            ->sum('valor_pagamento');

        // Lucro do mês
        $lucroMes = Estacionamento::whereBetween('entrada', [$inicioMes, $fimMes])
            ->whereNotNull('saida')
            ->sum('valor_pagamento');

        // Lucro total
        $lucroTotal = Estacionamento::whereNotNull('saida')
            ->sum('valor_pagamento');

        // Número de motoristas cadastrados
        $totalMotoristas = Motorista::count();

        // Movimentações do dia (entrada + saída = 1 movimentação)
        $movimentacoesDia = Estacionamento::whereDate('entrada', $hoje)->count();

        // Veículos atualmente estacionados
        $veiculosAtivos = Estacionamento::whereNull('saida')->count();

        // Gráfico: Lucro dos últimos 7 dias
        $lucroSemana = [];
        for ($i = 6; $i >= 0; $i--) {
            $data = Carbon::today()->subDays($i);
            $lucro = Estacionamento::whereDate('entrada', $data)
                ->whereNotNull('saida')
                ->sum('valor_pagamento');
            
            $lucroSemana[] = [
                'data' => $data->format('d/m'),
                'valor' => (float) $lucro
            ];
        }

        // Gráfico: Movimentações por tipo de pagamento
        $tiposPagamento = Estacionamento::select('tipo_pagamento', DB::raw('count(*) as total'))
            ->whereNotNull('saida')
            ->groupBy('tipo_pagamento')
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => $item->tipo_pagamento,
                    'total' => $item->total
                ];
            });

        // Gráfico: Movimentações por mês (últimos 6 meses)
        $movimentacoesMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $total = Estacionamento::whereYear('entrada', $mes->year)
                ->whereMonth('entrada', $mes->month)
                ->count();
            
            $movimentacoesMes[] = [
                'mes' => $mes->format('M/Y'),
                'total' => $total
            ];
        }

        return Inertia::render('Dashboard', [
            'user' => auth()->user(),
            'title' => 'Dashboard',
            'stats' => [
                'lucroDia' => (float) $lucroDia,
                'lucroMes' => (float) $lucroMes,
                'lucroTotal' => (float) $lucroTotal,
                'totalMotoristas' => $totalMotoristas,
                'movimentacoesDia' => $movimentacoesDia,
                'veiculosAtivos' => $veiculosAtivos,
            ],
            'graficos' => [
                'lucroSemana' => $lucroSemana,
                'tiposPagamento' => $tiposPagamento->toArray(),
                'movimentacoesMes' => $movimentacoesMes,
            ]
        ]);
    }
}

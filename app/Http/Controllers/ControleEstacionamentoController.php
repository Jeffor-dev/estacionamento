<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Motorista;
use App\Models\Estacionamento;
use Illuminate\Database\Eloquent\Builder;

class ControleEstacionamentoController extends Controller
{
    public function index()
    {
        $registros = Estacionamento::with('motorista.caminhao')->get()->map(function ($registro) {
            return [
                'id' => $registro->id,
                'motorista_id' => $registro->motorista_id,
                'entrada' => $registro->entrada,
                'saida' => $registro->saida ? $registro->saida : null,
                'tipo_pagamento' => $registro->tipo_pagamento,
                'valor_pagamento' => $registro->valor_pagamento,
                'motorista' => $registro->motorista ? [
                    'nome' => $registro->motorista->nome,
                    'caminhao' => [
                        'placa' => $registro->motorista->caminhao ? $registro->motorista->caminhao->placa : null,
                        'modelo' => $registro->motorista->caminhao ? $registro->motorista->caminhao->modelo : null,
                        'cor' => $registro->motorista->caminhao ? $registro->motorista->caminhao->cor : null,
                    ],
                ] : null,
            ];
        });

        return Inertia::render('Estacionamento/Index', [
            'user' => auth()->user(),
            'title' => 'Estacionamento',
            'registros' => $registros,
        ]);
    }

    public function cadastroEstacionamento()
    {
        // Buscar IDs dos motoristas que estão atualmente estacionados (têm entrada sem saída)
        $motoristasAtivos = Estacionamento::whereNull('saida')
            ->pluck('motorista_id')
            ->toArray();

        // Buscar apenas motoristas que NÃO estão na lista de ativos
        $motoristas = Motorista::with('caminhao')
            ->whereNotIn('id', $motoristasAtivos)
            ->get()
            ->map(function ($motorista) {
                $proximaGratuidade = $motorista->proximaGratuidadeEm();
                if ($proximaGratuidade === 0) {
                    $proximaGratuidade = 10;
                }
                $fidelidadeInfo = $motorista->temDireitoGratuidade()
                    ? ' 🎉 ENTRADA GRATUITA!' 
                    : " (Faltam {$proximaGratuidade} para gratuidade)";
                
                return [
                    'id' => $motorista->id,
                    'nome' => $motorista->nome,
                    'cpf' => $motorista->cpf,
                    'modelo' => $motorista->caminhao->modelo ?? 'N/A',
                    'placa' => $motorista->caminhao->placa ?? 'N/A',
                    'cor' => $motorista->caminhao->cor ?? 'N/A',
                    'contador_entradas' => $motorista->contador_entradas,
                    'proxima_gratuidade' => $proximaGratuidade,
                    'tem_direito_gratuidade' => $motorista->temDireitoGratuidade(),
                    'label' => 'Placa: ' . ($motorista->caminhao->placa ?? 'N/A') . ' - ' . 'Caminhão: ' . ($motorista->caminhao->modelo ?? 'N/A') . ' '. ($motorista->caminhao->cor ?? 'N/A') . $fidelidadeInfo,
                ];
            });

        return Inertia::render('Estacionamento/Cadastro', [
            'title' => 'Registrar Entrada',
            'motoristas' => $motoristas,
        ]);
    }

    public function cadastrar(Request $request)
    {
        $request->validate([
            'motorista' => 'required',
            'pagamento' => 'required|string|max:20',
            'tipoVeiculo' => 'required|in:truck_ls,bitrem',
            'valorPagamento' => 'required|numeric|min:0',
            'quantidadeTickets' => 'required|integer|min:1|max:50',
        ]);

        // Verificar se o motorista já está ativo no estacionamento
        $motoristaAtivo = Estacionamento::where('motorista_id', $request->motorista)
            ->whereNull('saida')
            ->exists();

        if ($motoristaAtivo) {
            return back()->withErrors(['motorista' => 'Este motorista já está registrado no estacionamento.']);
        }

        // Buscar o motorista para verificar fidelidade
        $motorista = Motorista::findOrFail($request->motorista);
        
        $quantidadeTickets = $request->quantidadeTickets;
        $ticketsPagos = 0;
        $ticketsGratuitos = 0;
        $contadorAtual = $motorista->contador_entradas;

        // Calcular quantos tickets serão pagos e quantos serão gratuitos
        if ($request->pagamento === 'Abastecimento') {
            // Para abastecimento, sempre 1 ticket gratuito
            $ticketsPagos = 0;
            $ticketsGratuitos = 1;
            $quantidadeTickets = 1;
        } else {
            // Simular compra ticket por ticket
            for ($i = 0; $i < $quantidadeTickets; $i++) {
                if ($contadorAtual === 10) {
                    $ticketsGratuitos++;
                    $contadorAtual = 1; // Reinicia após o gratuito
                } else {
                    $ticketsPagos++;
                    $contadorAtual++;
                }
            }
        }

        // Atualizar contador do motorista
        $motorista->update(['contador_entradas' => $contadorAtual]);

        // Calcular o turno baseado no horário de entrada
        $entrada = now('America/Sao_Paulo');
        
        $registro = Estacionamento::create([
            'motorista_id' => $request->motorista,
            'entrada' => $entrada,
            'saida' => null,
            'tipo_pagamento' => $request->pagamento,
            'tipo_veiculo' => $request->tipoVeiculo,
            'valor_pagamento' => $request->valorPagamento,
            'quantidade_tickets' => $quantidadeTickets,
            'tickets_pagos' => $ticketsPagos,
            'tickets_gratuitos' => $ticketsGratuitos,
        ]);

        $mensagemTickets = "";
        if ($ticketsGratuitos > 0) {
            $mensagemTickets = " Você recebeu {$ticketsGratuitos} ticket(s) gratuito(s)!";
        }

        $mensagem = "Compra de {$quantidadeTickets} ticket(s) registrada com sucesso!{$mensagemTickets}";

        return redirect()->route('estacionamento.index')->with('success', $mensagem);
    }

    public function api(Request $request)
    {
        $busca = $request->get('busca');
        $page = $request->get('page', 1);
        $rowsPerPage = $request->get('rowsPerPage', 10);
        $sortBy = $request->get('sortBy');
        $descending = $request->get('descending', false);
        
        $startRow = ($page - 1) * $rowsPerPage;
        
        $ret = [];
        
        // Incluir todos os registros (ativos e finalizados)
        $consulta = Estacionamento::with(['motorista.caminhao']);
        
        if ($busca) {
            $consulta->where(function (Builder $q) use ($busca) {
                $q->whereHas('motorista', function ($subQ) use ($busca) {
                    $subQ->where('nome', 'like', '%' . $busca . '%')
                         ->orWhere('cpf', 'like', '%' . $busca . '%');
                })->orWhereHas('motorista.caminhao', function ($subQ) use ($busca) {
                    $subQ->where('placa', 'like', '%' . $busca . '%')
                         ->orWhere('modelo', 'like', '%' . $busca . '%')
                         ->orWhere('cor', 'like', '%' . $busca . '%');
                });
            });
        }
        
        $consulta->orderBy('entrada', 'desc');

        $ret['rowsNumber'] = $consulta->count();
        $registros = $consulta->skip($startRow)->take($rowsPerPage)->get();
        
        // Formatar dados conforme necessário
        $ret['lista'] = $registros->map(function ($registro) {
            return [
                'id' => $registro->id,
                'motorista' => $registro->motorista ? $registro->motorista->nome : 'N/A',
                'entrada' => $registro->entrada,
                'saida' => $registro->saida,
                'valor_pagamento' => $registro->valor_pagamento,
                'tipo_pagamento' => $registro->tipo_pagamento,
                'status' => $registro->saida ? 'finalizado' : 'ativo',
                'caminhao' => [
                    'placa' => $registro->motorista->caminhao ? $registro->motorista->caminhao->placa : 'N/A',
                    'modelo' => $registro->motorista->caminhao ? $registro->motorista->caminhao->modelo : 'N/A',
                    'cor' => $registro->motorista->caminhao ? $registro->motorista->caminhao->cor : 'N/A',
                ],
            ];
        });

        return response()->json($ret);
    }

    public function registrarSaida($id)
    {
        try {
            // Buscar o registro de estacionamento
            $estacionamento = Estacionamento::with('motorista')->findOrFail($id);
            
            // Verificar se já não tem saída registrada
            if ($estacionamento->saida) {
                return back()->withErrors(['error' => 'Este veículo já possui saída registrada.']);
            }
            
            $agora = now();
            $entrada = \Carbon\Carbon::parse($estacionamento->entrada);
            
            // Calcular o tempo em horas (mínimo 1 hora)
            $horas = $entrada->diffInHours($agora);
            if ($horas < 1) {
                $horas = 1; // Cobrança mínima de 1 hora
            }
            
            // Valor por hora (pode ser configurável no futuro)
            $valorTotal = $estacionamento->valor_pagamento; 
            
            // Registrar a saída com horário atual e valor calculado
            $estacionamento->update([
                'saida' => $agora,
                'valor' => $valorTotal
            ]);
            
            return back()->with('success', 'Saída registrada com sucesso para ' . $estacionamento->motorista->nome . '. Valor: R$ ' . number_format($valorTotal, 2, ',', '.'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao registrar saída: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $estacionamento = Estacionamento::with(['motorista.caminhao'])->findOrFail($id);

        return Inertia::render('Estacionamento/Edit', [
            'title' => 'Editar Estacionamento',
            'estacionamento' => $estacionamento
        ]);
    }

    public function update(Request $request, $id)
    {
        $estacionamento = Estacionamento::findOrFail($id);

        // Validação
        $request->validate([
            'tipo_pagamento' => 'required|string|in:Dinheiro,Cartão,Pix,Abastecimento',
            'valor_pagamento' => 'required|numeric|min:0',
            'tipo_veiculo' => 'required|string|in:truck_ls,bitrem'
        ]);

        // Se o tipo de pagamento for abastecimento, zerar o valor
        $valorFinal = $request->tipo_pagamento === 'Abastecimento' ? 0 : $request->valor_pagamento;

        // Atualizar o registro
        $estacionamento->update([
            'tipo_pagamento' => $request->tipo_pagamento,
            'valor_pagamento' => $valorFinal,
            'tipo_veiculo' => $request->tipo_veiculo
        ]);

        return redirect()->route('estacionamento.index')
            ->with('success', 'Registro de estacionamento atualizado com sucesso!');
    }

    public function corrigirMotorista()
    {
        try {
            // Contar quantos registros serão removidos
            $registrosParaRemover = Estacionamento::whereNull('saida')->count();
            
            // Apagar todos os registros onde saida = null
            $registrosRemovidos = Estacionamento::whereNull('saida')->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Correção executada com sucesso!',
                'registros_removidos' => $registrosRemovidos
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao corrigir motoristas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor ao executar correção.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

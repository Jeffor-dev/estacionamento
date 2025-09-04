<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Motorista;
use App\Models\Caminhao;
use App\Facades\Toast;
use Illuminate\Database\Eloquent\Builder;

class MotoristaController extends Controller
{
    protected static $searchableAttributes = [
        'nome',
        'cpf',
        'telefone',
    ];
    public function index()
    {
        $motoristas = Motorista::with('caminhao')->get();

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

    public function editMotorista($id)
    {
        $motorista = Motorista::with('caminhao')->findOrFail($id);
        // Prepara dados para o front
        $dados = [
            'id' => $motorista->id,
            'nome' => $motorista->nome,
            'cpf' => $motorista->cpf,
            'tipo_documento' => $motorista->tipo_documento ?? 'CPF',
            'telefone' => $motorista->telefone,
            'empresa' => $motorista->empresa,
            'observacao' => $motorista->observacao,
            'placa' => $motorista->caminhao ? $motorista->caminhao->placa : '',
            'modelo' => $motorista->caminhao ? $motorista->caminhao->modelo : '',
            'cor' => $motorista->caminhao ? $motorista->caminhao->cor : '',
        ];
        return Inertia::render('Motorista/Edit', [
            'motorista' => $dados,
            'title' => 'Editar Motorista',
        ]);
    }

    public function atualizarMotorista($id)
    {
        $motorista = Motorista::findOrFail($id);
        
        // Verificar se já existe um caminhão com essa placa (excluindo o caminhão atual)
        $placaInformada = strtoupper(request('placa'));
        $placaExistente = Caminhao::where('placa', $placaInformada)
                                 ->where('id', '!=', $motorista->caminhao_id)
                                 ->exists();
        
        if ($placaExistente) {
            Toast::error('Um caminhão com essa placa já está cadastrado.');
            return back()->withInput();
        }
        
        $motorista->nome = request('nome');
        $motorista->cpf = request('cpf');
        $motorista->tipo_documento = request('tipoDocumento', 'CPF');
        $motorista->telefone = request('telefone');
        $motorista->empresa = request('empresa');
        $motorista->observacao = request('observacao');
        $motorista->save();

        // Atualiza caminhão
        if ($motorista->caminhao) {
            $motorista->caminhao->placa = $placaInformada;
            $motorista->caminhao->modelo = request('modelo');
            $motorista->caminhao->cor = request('cor');
            $motorista->caminhao->save();
        }
        return redirect()->route('motorista.index')->with('success', 'Motorista atualizado com sucesso!');
    }

    public function cadastrar(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:18', // Aumentado para aceitar CNPJ
            'tipoDocumento' => 'required|in:CPF,CNPJ',
            'telefone' => 'required|string|max:15',
            'observacao' => 'nullable|string|max:500',
            'empresa' => 'nullable|string|max:255',
            'placa' => 'required|string|max:8',
            'cor' => 'required|string|max:20',
            'modelo' => 'required|string|max:20',
        ]);

        // Verificar se já existe um caminhão com essa placa
        $placaExistente = Caminhao::where('placa', strtoupper($request->placa))->exists();
        
        if ($placaExistente) {
            Toast::error('Um caminhão com essa placa já está cadastrado.');
            return back()->withInput();
        }

        $caminhao = Caminhao::create([
            'placa' => strtoupper($request->placa),
            'modelo' => $request->modelo,
            'cor' => $request->cor,
        ]);

        $motorista = Motorista::create([
            'nome' => strtoupper($request->nome),
            'cpf' => $request->cpf,
            'tipo_documento' => $request->tipoDocumento,
            'telefone' => $request->telefone,
            'observacao' => strtoupper($request->observacao),
            'empresa' => strtoupper($request->empresa),
            'caminhao_id' => $caminhao->id, 
        ]);

        $caminhao->motorista_id = $motorista->id;

        $caminhao->save();

        return redirect()->route('motorista.index')->with('success', 'Motorista cadastrado com sucesso!');
    }

        public function api(Request $request)
    {
        $busca = $request->get('busca');
        $page = $request->get('page', 1);
        $rowsPerPage = $request->get('rowsPerPage', 10);
        $sortBy = $request->get('sortBy');
        $descending = $request->get('descending', false);

        $startRow = ($page - 1) * $rowsPerPage;

        $consulta = Motorista::with('caminhao');

        if ($busca) {
            $consulta->where(function (Builder $q) use ($busca) {
            $q->where('nome', 'like', '%' . $busca . '%')
              ->orWhere('cpf', 'like', '%' . $busca . '%')
              ->orWhere('telefone', 'like', '%' . $busca . '%')
              ->orWhereHas('caminhao', function (Builder $qc) use ($busca) {
                  $qc->where('placa', 'like', '%' . $busca . '%');
              });
            });
        }

        if ($sortBy) {
            $consulta->orderBy($sortBy, $descending ? 'desc' : 'asc');
        } else {
            $consulta->orderBy('nome', 'asc');
        }

        $ret = [];
        $ret['rowsNumber'] = $consulta->count();
        $registros = $consulta->skip($startRow)->take($rowsPerPage)->get();

        $ret['lista'] = $registros->map(function ($motorista) {
            return [
                'id' => $motorista->id,
                'nome' => $motorista->nome,
                'cpf' => $motorista->cpf,
                'telefone' => $motorista->telefone,
                'empresa' => $motorista->empresa,
                'observacao' => $motorista->observacao,
                'caminhao' => $motorista->caminhao ? [
                    'placa' => $motorista->caminhao->placa,
                    'modelo' => $motorista->caminhao->modelo,
                    'cor' => $motorista->caminhao->cor,
                ] : null,
            ];
        });

        return response()->json($ret);
    }

    public function excluirMotorista($id)
    {
        try {
            $motorista = Motorista::findOrFail($id);
            
            // Verificar se o motorista tem registros no estacionamento
            $temRegistros = \App\Models\Estacionamento::where('motorista_id', $id)->exists();
            
            if ($temRegistros) {
                return back()->withErrors(['error' => 'Não é possível excluir este motorista pois ele possui registros no estacionamento.']);
            }
            
            // Excluir caminhão relacionado se existir
            if ($motorista->caminhao) {
                $motorista->caminhao->delete();
            }
            
            // Excluir motorista
            $motorista->delete();
            
            return redirect()->route('motorista.index')->with('success', 'Motorista excluído com sucesso!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao excluir motorista: ' . $e->getMessage()]);
        }
    }
}



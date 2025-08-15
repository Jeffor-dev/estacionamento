<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Motorista;
use App\Models\Caminhao;
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

    public function api(Request $request){
        $page = $request->get('page', 1);
        $rowsPerPage = $request->get('rowsPerPage', 10);
        $startRow = ($page - 1) * $rowsPerPage;

        $consulta = Motorista::with('caminhao');
        if ($request->filled('busca')) {
            $consulta->where(function (Builder $query) use ($request) {
                foreach (self::$searchableAttributes as $atributo) {
                    $query->orWhere($atributo, 'like', '%' . $request->get('busca') . '%');
                }
            });
        }
        $consulta->orderByRaw('1');

        $ret['rowsNumber'] = $consulta->count();
        $ret['lista'] = $consulta->skip($startRow)->take($rowsPerPage)->get();
        return response()->json($ret);
    }
}

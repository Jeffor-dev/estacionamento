<?php

namespace App\Http\Controllers;

use App\Models\Estacionamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CupomController extends Controller
{
    public function gerarCupom($id)
    {
        $estacionamento = Estacionamento::with(['motorista', 'motorista.caminhao'])
            ->findOrFail($id);

        // Verifica se o estacionamento foi finalizado (tem saida)
        if (!$estacionamento->saida) {
            return redirect()->back()->with('error', 'Cupom só pode ser gerado após a finalização do estacionamento.');
        }

        // Calcula informações do cupom
        $entrada = Carbon::parse($estacionamento->entrada);
        $saida = Carbon::parse($estacionamento->saida);
        $duracao = $entrada->diff($saida);
        
        // Formatar duração
        $duracaoFormatada = '';
        if ($duracao->days > 0) {
            $duracaoFormatada .= $duracao->days . ' dia(s) ';
        }
        if ($duracao->h > 0) {
            $duracaoFormatada .= $duracao->h . ' hora(s) ';
        }
        if ($duracao->i > 0) {
            $duracaoFormatada .= $duracao->i . ' minuto(s)';
        }
        
        $dados = [
            'estacionamento' => $estacionamento,
            'duracaoFormatada' => trim($duracaoFormatada),
            'dataAtual' => now()->format('d/m/Y H:i:s'),
            'numeroTicket' => str_pad($estacionamento->id, 6, '0', STR_PAD_LEFT)
        ];

        return view('cupom', $dados);
    }
}

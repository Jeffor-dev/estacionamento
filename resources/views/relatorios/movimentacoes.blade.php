<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Movimentações do Dia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Movimentações do Dia ({{ $hoje }})</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Motorista</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Cor</th>
                <th>Entrada</th>
                <th>Saída</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimentacoes as $mov)
            <tr>
                <td>{{ $mov->id }}</td>
                <td>{{ $mov->motorista->nome ?? '' }}</td>
                <td>{{ $mov->motorista->caminhao->placa ?? '' }}</td>
                <td>{{ $mov->motorista->caminhao->modelo ?? '' }}</td>
                <td>{{ $mov->motorista->caminhao->cor ?? '' }}</td>
                <td>{{ $mov->entrada ? date('d/m/Y H:i', strtotime($mov->entrada)) : '' }}</td>
                <td>{{ $mov->saida ? date('d/m/Y H:i', strtotime($mov->saida)) : '' }}</td>
                <td>{{ $mov->valor ? 'R$ ' . number_format($mov->valor, 2, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
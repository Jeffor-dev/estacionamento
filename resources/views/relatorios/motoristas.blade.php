<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Motoristas Cadastrados</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Lista de Motoristas Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Empresa</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Cor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($motoristas as $motorista)
            <tr>
                <td>{{ $motorista->id }}</td>
                <td>{{ $motorista->nome }}</td>
                <td>{{ $motorista->cpf }}</td>
                <td>{{ $motorista->telefone }}</td>
                <td>{{ $motorista->empresa }}</td>
                <td>{{ $motorista->caminhao->placa ?? '' }}</td>
                <td>{{ $motorista->caminhao->modelo ?? '' }}</td>
                <td>{{ $motorista->caminhao->cor ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
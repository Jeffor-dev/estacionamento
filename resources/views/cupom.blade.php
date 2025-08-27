<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom de Estacionamento</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
            .logo {
                width: 50px !important;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .cupom {
            background: white;
            padding: 20px;
            border: 2px dashed #333;
            margin-bottom: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 60px;
            height: auto;
            border-radius: 4px;
        }
        
        .empresa-info {
            flex: 1;
            text-align: center;
        }
        
        .empresa {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .subtitulo {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .ticket-numero {
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #333;
            padding: 5px;
            display: inline-block;
        }
        
        .info-linha {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 12px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .valor-total {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border: 1px solid #333;
            padding: 10px;
            margin: 15px 0;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .botoes {
            text-align: center;
            margin-top: 20px;
        }
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 10px;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="cupom">
        <div class="header">
            <div class="header-content">
                <img src="{{ asset('maguary.jpeg') }}" alt="Logo Maguary" class="logo">
                <div class="empresa-info">
                    <div class="empresa">MAGUARY AUTO POSTO</div>
                    <div class="subtitulo">COMPROVANTE DE ESTACIONAMENTO</div>
                </div>
                <div style="width: 60px;"></div> <!-- Espaçador para centralizar -->
            </div>
            <div class="ticket-numero">TICKET: {{ $numeroTicket }}</div>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Data/Hora:</span>
            <span>{{ $dataAtual }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Motorista:</span>
            <span>{{ $estacionamento->motorista->nome }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">CPF:</span>
            <span>{{ $estacionamento->motorista->cpf }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Veículo:</span>
            <span>{{ $estacionamento->motorista->caminhao->modelo }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Placa:</span>
            <span>{{ $estacionamento->motorista->caminhao->placa }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Entrada:</span>
            <span>{{ \Carbon\Carbon::parse($estacionamento->entrada)->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Saída:</span>
            <span>{{ \Carbon\Carbon::parse($estacionamento->saida)->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Duração:</span>
            <span>{{ $duracaoFormatada }}</span>
        </div>
        
        <div class="valor-total">
            VALOR TOTAL: R$ {{ number_format($estacionamento->valor_pagamento, 2, ',', '.') }}
        </div>
        
        <div class="footer">
            <div>OBRIGADO PELA PREFERÊNCIA!</div>
            <div>Este cupom não possui valor fiscal</div>
            <div>Guarde este comprovante</div>
        </div>
    </div>
    
    <div class="botoes no-print">
        <button class="btn" onclick="window.print()">Imprimir Cupom</button>
        <a href="{{ route('estacionamento.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
    
    <script>
        // Auto-print quando a página carregar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>

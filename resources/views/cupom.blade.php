<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pernoite</title>
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
            @page {
                margin: 0;
                size: auto;
            }
            /* Oculta cabeçalho, rodapé e paginação do navegador */
            html, body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            max-width: 280px;
            margin: 0 auto;
            padding: 15px;
            background: #f5f5f5;
        }
        
        .cupom {
            background: white;
            padding: 15px;
            border: 2px dashed #333;
            margin-bottom: 15px;
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
            width: 45px;
            height: auto;
            border-radius: 4px;
        }
        
        .empresa-info {
            flex: 1;
            text-align: center;
        }
        
        .empresa {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .subtitulo {
            font-size: 11px;
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
            margin: 6px 0;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .valor-total {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #333;
            padding: 8px;
            margin: 12px 0;
        }
        
        .footer {
            text-align: center;
            font-size: 9px;
            border-top: 1px solid #333;
            padding-top: 8px;
            margin-top: 12px;
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
    <div class="cupom" style="margin-top: 40px;">
        <div class="header">
            <div class="header-content">
                <img src="{{ asset('maguary.jpeg') }}" alt="Logo Maguary" class="logo">
                <div class="empresa-info">
                    <div class="empresa">MAGUARY AUTO POSTO</div>
                    <div class="subtitulo">COMPROVANTE DE PERNOITE</div>
                </div>
                <div style="width: 45px;"></div> <!-- Espaçador para centralizar -->
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
            <span class="info-label">{{ $estacionamento->motorista->tipo_documento === 'CNPJ' ? 'CNPJ:' : 'CPF:' }}</span>
            <span>{{ $estacionamento->motorista->cpf }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Empresa:</span>
            <span>{{ $estacionamento->motorista->empresa }}</span>
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
            <span class="info-label">Tipo Veículo:</span>
            <span>{{ $estacionamento->tipo_veiculo == 'truck_ls' ? 'Truck/LS' : 'Bitrem' }}</span>
        </div>
        
        <div class="info-linha">
            <span class="info-label">Entrada:</span>
            <span>{{ \Carbon\Carbon::parse($estacionamento->entrada)->format('d/m/Y H:i') }}</span>
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

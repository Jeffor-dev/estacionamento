# Script PowerShell para iniciar o ambiente de desenvolvimento Laravel + Vite
# Executa php artisan serve e npm run dev simultaneamente

Write-Host "🚀 Iniciando ambiente de desenvolvimento..." -ForegroundColor Green
Write-Host "📦 Projeto: Estacionamento Laravel" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green

# Função para cleanup
function Stop-DevServers {
    Write-Host "`n🛑 Encerrando servidores..." -ForegroundColor Yellow
    
    # Para os processos se estiverem rodando
    if ($laravelJob) {
        Stop-Job $laravelJob
        Remove-Job $laravelJob
    }
    if ($viteJob) {
        Stop-Job $viteJob
        Remove-Job $viteJob
    }
    
    # Mata processos do Laravel e Vite que possam estar rodando
    Get-Process | Where-Object {$_.ProcessName -eq "php" -and $_.CommandLine -like "*artisan serve*"} | Stop-Process -Force -ErrorAction SilentlyContinue
    Get-Process | Where-Object {$_.ProcessName -eq "node" -and $_.CommandLine -like "*vite*"} | Stop-Process -Force -ErrorAction SilentlyContinue
    
    Write-Host "✅ Servidores encerrados!" -ForegroundColor Red
    exit 0
}

# Configura handler para Ctrl+C
[Console]::TreatControlCAsInput = $false
$null = Register-EngineEvent PowerShell.Exiting -Action { Stop-DevServers }

# Verifica se o arquivo artisan existe
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Erro: arquivo 'artisan' não encontrado. Execute este script na raiz do projeto Laravel." -ForegroundColor Red
    exit 1
}

# Verifica se o package.json existe
if (-not (Test-Path "package.json")) {
    Write-Host "❌ Erro: arquivo 'package.json' não encontrado." -ForegroundColor Red
    exit 1
}

# Verifica se as dependências do Node estão instaladas
if (-not (Test-Path "node_modules")) {
    Write-Host "📦 Instalando dependências do Node.js..." -ForegroundColor Yellow
    npm install
}

# Verifica se as dependências do Composer estão instaladas
if (-not (Test-Path "vendor")) {
    Write-Host "📦 Instalando dependências do Composer..." -ForegroundColor Yellow
    composer install
}

# Verifica se o arquivo .env existe
if (-not (Test-Path ".env")) {
    Write-Host "⚙️  Criando arquivo .env..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    php artisan key:generate
}

Write-Host "🔧 Iniciando Laravel server (php artisan serve)..." -ForegroundColor Green

# Inicia o servidor Laravel como job em background
$laravelJob = Start-Job -ScriptBlock {
    Set-Location $using:PWD
    php artisan serve --host=127.0.0.1 --port=8000
}

# Aguarda um momento para o Laravel inicializar
Start-Sleep -Seconds 3

Write-Host "⚡ Iniciando Vite dev server (npm run dev)..." -ForegroundColor Blue

# Inicia o Vite dev server como job em background
$viteJob = Start-Job -ScriptBlock {
    Set-Location $using:PWD
    npm run dev
}

Write-Host "✅ Servidores iniciados com sucesso!" -ForegroundColor Green
Write-Host "🌐 Laravel: http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "⚡ Vite: http://127.0.0.1:5173" -ForegroundColor Blue
Write-Host ""
Write-Host "💡 Para parar os servidores, pressione Ctrl+C" -ForegroundColor Yellow
Write-Host ""

# Monitora os jobs e exibe output
try {
    while ($true) {
        # Recebe output dos jobs
        Receive-Job $laravelJob -ErrorAction SilentlyContinue | Write-Host
        Receive-Job $viteJob -ErrorAction SilentlyContinue | Write-Host
        
        # Verifica se os jobs ainda estão rodando
        if ($laravelJob.State -eq "Failed" -or $viteJob.State -eq "Failed") {
            Write-Host "❌ Um dos servidores falhou. Verifique os logs acima." -ForegroundColor Red
            break
        }
        
        Start-Sleep -Seconds 1
    }
}
catch {
    Write-Host "Script interrompido pelo usuário." -ForegroundColor Yellow
}
finally {
    Stop-DevServers
}

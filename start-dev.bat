@echo off
title Estacionamento Laravel - Ambiente de Desenvolvimento

echo.
echo ============================================
echo   🚀 Iniciando ambiente de desenvolvimento
echo   📦 Projeto: Estacionamento Laravel  
echo ============================================
echo.

:: Verifica se estamos na pasta correta
if not exist "artisan" (
    echo ❌ Erro: arquivo 'artisan' nao encontrado.
    echo    Execute este script na raiz do projeto Laravel.
    pause
    exit /b 1
)

if not exist "package.json" (
    echo ❌ Erro: arquivo 'package.json' nao encontrado.
    pause
    exit /b 1
)

:: Atualiza o código do repositório
echo 🔄 Atualizando codigo do repositorio...
git pull
if %errorlevel% neq 0 (
    echo ⚠️  Aviso: Nao foi possivel fazer git pull. Continuando...
)
echo.

:: Verifica dependências do Node
if not exist "node_modules" (
    echo 📦 Instalando dependencias do Node.js...
    npm install
)

:: Verifica dependências do Composer
if not exist "vendor" (
    echo 📦 Instalando dependencias do Composer...
    composer install
)

:: Verifica arquivo .env
if not exist ".env" (
    echo ⚙️  Criando arquivo .env...
    copy ".env.example" ".env"
    php artisan key:generate
)

:: Verifica e executa migrations pendentes
echo 🔍 Verificando migrations pendentes...
php artisan migrate:status > nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Database nao conectado ou nao configurado. Pulando verificacao de migrations.
) else (
    php artisan migrate --dry-run > nul 2>&1
    if %errorlevel% equ 0 (
        echo ✅ Nenhuma migration pendente encontrada.
    ) else (
        echo 🔄 Migrations pendentes encontradas. Executando...
        php artisan migrate --force
        if %errorlevel% equ 0 (
            echo ✅ Migrations executadas com sucesso!
        ) else (
            echo ❌ Erro ao executar migrations.
        )
    )
)
echo.

echo 🔧 Iniciando Laravel server...
echo 🌐 Laravel estara disponivel em: http://127.0.0.1:8000
echo.

:: Inicia Laravel server em background
start "Laravel Server" cmd /c "php artisan serve --host=127.0.0.1 --port=8000"

:: Aguarda um pouco para o Laravel inicializar
timeout /t 3 /nobreak > nul

echo ⚡ Iniciando Vite dev server...
echo ⚡ Vite estara disponivel em: http://127.0.0.1:5173
echo.

:: Inicia Vite dev server em background para aguardar inicialização
start "Vite Dev Server" cmd /c "npm run dev"

:: Aguarda um pouco para o Vite inicializar
echo ⏳ Aguardando Vite dev server inicializar...
timeout /t 5 /nobreak > nul

:: Abre o browser padrão na URL do Laravel após ambos servidores estarem rodando
echo 🌐 Abrindo browser em http://127.0.0.1:8000...
echo 💡 Dica: Use Ctrl+F5 para forcar atualizacao sem cache
start http://127.0.0.1:8000

echo.
echo ✅ Servidores iniciados com sucesso!
echo 🌐 Laravel: http://127.0.0.1:8000
echo ⚡ Vite: http://127.0.0.1:5173
echo.
echo 💡 Para parar os servidores, feche as janelas do terminal
echo.
pause

:: Se chegou aqui, o script foi finalizado
echo.
echo 🛑 Script finalizado.
echo 💡 Para parar os servidores, feche as janelas do Laravel e Vite.

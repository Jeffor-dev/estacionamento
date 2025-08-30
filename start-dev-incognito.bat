@echo off
title Estacionamento Laravel - Ambiente de Desenvolvimento (Modo Incognito)

echo.
echo ============================================
echo   🚀 Iniciando ambiente de desenvolvimento
echo   📦 Projeto: Estacionamento Laravel  
echo   🔒 Modo Incognito (sem cache)
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

:: Tenta abrir no Chrome em modo incógnito primeiro
echo 🌐 Tentando abrir browser em modo incognito...
where chrome >nul 2>nul && (
    echo 🚀 Abrindo Chrome em modo incognito...
    start chrome --incognito http://127.0.0.1:8000
    goto :opened
)

:: Se não encontrou Chrome, tenta Edge
where msedge >nul 2>nul && (
    echo 🚀 Abrindo Edge em modo incognito...
    start msedge --inprivate http://127.0.0.1:8000
    goto :opened
)

:: Se não encontrou navegadores específicos, abre o padrão normalmente
echo 🚀 Abrindo browser padrão...
echo 💡 Dica: Use Ctrl+Shift+N para modo incognito manual
start http://127.0.0.1:8000

:opened
echo.
echo ✅ Servidores iniciados com sucesso!
echo 🌐 Laravel: http://127.0.0.1:8000
echo ⚡ Vite: http://127.0.0.1:5173
echo.
echo 💡 Para parar os servidores, feche as janelas do terminal
echo 🔒 Browser aberto em modo incognito (sem cache/localStorage)
echo.
pause

:: Se chegou aqui, o script foi finalizado
echo.
echo 🛑 Script finalizado.
echo 💡 Para parar os servidores, feche as janelas do Laravel e Vite.

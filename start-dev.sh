#!/bin/bash

# Script para iniciar o ambiente de desenvolvimento Laravel + Vite
# Executa php artisan serve e npm run dev simultaneamente

echo "🚀 Iniciando ambiente de desenvolvimento..."
echo "📦 Projeto: Estacionamento Laravel"
echo "=================================="

# Cores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Função para cleanup quando o script for interrompido
cleanup() {
    echo -e "\n${YELLOW}🛑 Encerrando servidores...${NC}"
    # Mata todos os processos filhos
    jobs -p | xargs -r kill
    echo -e "${RED}✅ Servidores encerrados!${NC}"
    exit 0
}

# Configura trap para cleanup
trap cleanup SIGINT SIGTERM

# Verifica se o arquivo artisan existe
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: arquivo 'artisan' não encontrado. Execute este script na raiz do projeto Laravel.${NC}"
    exit 1
fi

# Verifica se o package.json existe
if [ ! -f "package.json" ]; then
    echo -e "${RED}❌ Erro: arquivo 'package.json' não encontrado.${NC}"
    exit 1
fi

# Verifica se as dependências do Node estão instaladas
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}📦 Instalando dependências do Node.js...${NC}"
    npm install
fi

# Verifica se as dependências do Composer estão instaladas
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}📦 Instalando dependências do Composer...${NC}"
    composer install
fi

# Verifica se o arquivo .env existe
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚙️  Criando arquivo .env...${NC}"
    cp .env.example .env
    php artisan key:generate
fi

echo -e "${GREEN}🔧 Iniciando Laravel server (php artisan serve)...${NC}"
# Inicia o servidor Laravel em background
php artisan serve --host=127.0.0.1 --port=8000 &
LARAVEL_PID=$!

# Aguarda um momento para o Laravel inicializar
sleep 2

echo -e "${BLUE}⚡ Iniciando Vite dev server (npm run dev)...${NC}"
# Inicia o Vite dev server em background
npm run dev &
VITE_PID=$!

echo -e "${GREEN}✅ Servidores iniciados com sucesso!${NC}"
echo -e "${GREEN}🌐 Laravel: http://127.0.0.1:8000${NC}"
echo -e "${BLUE}⚡ Vite: http://127.0.0.1:5173${NC}"
echo ""
echo -e "${YELLOW}💡 Para parar os servidores, pressione Ctrl+C${NC}"
echo ""

# Aguarda os processos terminarem
wait $LARAVEL_PID $VITE_PID

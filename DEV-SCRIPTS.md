# Scripts de Desenvolvimento - Estacionamento Laravel

Este projeto inclui scripts para facilitar o desenvolvimento, executando simultaneamente o servidor Laravel e o Vite dev server.

## 📁 Scripts Disponíveis

### 1. `start-dev.bat` (Windows - Recomendado)
**Arquivo batch simples para Windows**

**Como usar:**
```cmd
# Na pasta raiz do projeto, execute:
start-dev.bat
```

**Características:**
- ✅ Mais simples e compatível com Windows
- ✅ Verifica dependências automaticamente
- ✅ Cria arquivo .env se não existir
- ✅ Abre Laravel em uma janela separada
- ✅ Mantém Vite na janela principal

### 2. `start-dev.ps1` (PowerShell)
**Script PowerShell mais avançado**

**Como usar:**
```powershell
# Primeiro, permita execução de scripts (execute como administrador):
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Depois, na pasta do projeto:
.\start-dev.ps1
```

**Características:**
- ✅ Interface mais bonita com cores
- ✅ Melhor controle de processos
- ✅ Cleanup automático ao fechar

### 3. `start-dev.sh` (Bash/Linux/WSL)
**Script bash para ambientes Unix**

**Como usar:**
```bash
# Torne o script executável:
chmod +x start-dev.sh

# Execute:
./start-dev.sh
```

## 🚀 O que os Scripts Fazem

1. **Verificações automáticas:**
   - ✅ Verifica se está na pasta correta do projeto
   - ✅ Verifica se `node_modules` existe (instala se necessário)
   - ✅ Verifica se `vendor` existe (instala se necessário)
   - ✅ Verifica se `.env` existe (cria a partir do `.env.example` se necessário)

2. **Execução simultânea:**
   - 🔧 **Laravel Server**: `php artisan serve` em `http://127.0.0.1:8000`
   - ⚡ **Vite Dev Server**: `npm run dev` em `http://127.0.0.1:5173`

3. **Cleanup:**
   - 🛑 Encerra ambos os servidores ao pressionar `Ctrl+C`

## 🌐 URLs do Projeto

Após executar o script:
- **Laravel Application**: http://127.0.0.1:8000
- **Vite Dev Server**: http://127.0.0.1:5173

## 💡 Dicas

- **Para Windows**: Use o `start-dev.bat` que é mais simples e direto
- **Para desenvolvimento**: O Vite dev server oferece hot reload automático
- **Para parar**: Feche as janelas do terminal ou pressione `Ctrl+C`
- **Problemas**: Verifique se PHP, Node.js e Composer estão instalados

## 🔧 Requisitos

- PHP >= 8.1
- Node.js >= 16
- Composer
- NPM

## 📝 Comandos Manuais (Alternativa)

Se preferir executar manualmente:

```cmd
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite  
npm run dev
```

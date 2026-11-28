# ============================================================
# START-ALL — Preparar e enviar para o Railway
# ============================================================
# - Limpa caches locais
# - Compila os assets (Vite) para produção
# - Faz commit e push para o GitHub (que despoleta o deploy)
# ============================================================

$ErrorActionPreference = "Stop"
$projeto = "C:\xampp\htdocs\Ex_Estudantes_Uniluanda"

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  UNILUANDA — PREPARAR DEPLOY RAILWAY" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

Set-Location $projeto

# ─── 1. Limpar caches locais ────────────────────────────────
Write-Host "[1/4] A limpar caches locais..." -ForegroundColor Yellow
php artisan config:clear | Out-Null
php artisan optimize:clear | Out-Null
php artisan view:clear | Out-Null
Write-Host "    ✅ Caches limpas" -ForegroundColor Green

# ─── 2. Compilar os assets (Vite) ───────────────────────────
Write-Host "[2/4] A compilar assets (Vite)..." -ForegroundColor Yellow

# Remove a pasta build antiga e o ficheiro hot (se existirem)
Remove-Item "public\build" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item "public\hot"   -Force -ErrorAction SilentlyContinue

# Verifica se o Node/npm está instalado
if (!(Get-Command npm -ErrorAction SilentlyContinue)) {
    Write-Host "    ❌ npm não encontrado. Instala o Node.js." -ForegroundColor Red
    exit 1
}

# Compila para produção
$saida = npm run build 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "    ❌ npm run build falhou:" -ForegroundColor Red
    $saida | Select-Object -Last 20
    exit 1
}
Write-Host "    ✅ Assets compilados" -ForegroundColor Green

# ─── 3. Commit e Push para o GitHub ─────────────────────────
Write-Host "[3/4] A enviar para o GitHub..." -ForegroundColor Yellow

git add .
$commitMsg = "Deploy Railway: build de assets e alterações locais - $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
git commit -m $commitMsg
git push origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "    ❌ Push falhou. Verifica a ligação ao GitHub." -ForegroundColor Red
    exit 1
}
Write-Host "    ✅ Push concluído" -ForegroundColor Green

# ─── 4. Fim ─────────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ✅ CÓDIGO ENVIADO!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  O Railway vai detetar o novo commit e iniciar o deploy automaticamente." -ForegroundColor White
Write-Host "  Acompanha o progresso em: https://railway.com/dashboard" -ForegroundColor Gray
Write-Host ""
Write-Host "  Para testar o site online, abre:" -ForegroundColor Yellow
Write-Host "    https://portal-alumni-track-uniluanda-production.up.railway.app" -ForegroundColor White
Write-Host ""
Write-Host "  Nota: O Reverb (WebSockets) e o MySQL estão a correr no Railway." -ForegroundColor Gray
Write-Host "        Não é necessário abrir túneis nem iniciar servidores locais." -ForegroundColor Gray
Write-Host ""
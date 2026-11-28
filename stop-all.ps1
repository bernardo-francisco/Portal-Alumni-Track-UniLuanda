# ============================================================
# STOP-ALL - Para processos locais e tuneis antigos
# ============================================================

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "A parar processos locais (PHP, Node, Cloudflared)..." -ForegroundColor Yellow

Get-Process php,node,cloudflared -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue

Start-Sleep -Seconds 2

Write-Host "Processos locais parados." -ForegroundColor Green
Write-Host ""
Write-Host "INFO: Os servicos no Railway (Site, Reverb e MySQL) continuam ONLINE." -ForegroundColor Gray
Write-Host "      Para os suspender, vai ao painel do Railway > Settings > Suspend." -ForegroundColor Gray
Write-Host ""
# ============================================================
# STOP-ALL — Para todos os processos do sistema
# ============================================================

Write-Host ""
Write-Host "A parar todos os processos..." -ForegroundColor Yellow

Get-Process php,node,cloudflared -ErrorAction SilentlyContinue | Stop-Process -Force

Start-Sleep -Seconds 2

Write-Host "✅ Tudo parado." -ForegroundColor Green
Write-Host ""

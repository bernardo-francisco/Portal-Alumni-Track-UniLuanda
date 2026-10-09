# ============================================================
# STATUS — Verifica o estado do sistema
# ============================================================

Write-Host ""
Write-Host "=========== ESTADO DO SISTEMA ===========" -ForegroundColor Cyan
Write-Host ""

# Serviços internos
Write-Host "Serviços internos:" -ForegroundColor Yellow
$portas = @(8000, 8080, 9000)
foreach ($porta in $portas) {
    $nome = switch ($porta) {
        8000 { "Laravel" }
        8080 { "Reverb" }
        9000 { "PeerServer" }
    
    }
    $linha = netstat -ano | Select-String ":$porta" | Select-String "LISTENING"
    if ($linha) {
        Write-Host "  ✅ $nome (porta $porta)" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $nome (porta $porta) — NÃO ESTÁ A CORRER" -ForegroundColor Red
    }
}

# Tuneis Cloudflared
Write-Host ""
Write-Host "Tuneis Cloudflared:" -ForegroundColor Yellow
$cf = Get-Process cloudflared -ErrorAction SilentlyContinue
if ($cf) {
    Write-Host "  ✅ $($cf.Count) processos cloudflared a correr" -ForegroundColor Green
} else {
    Write-Host "  ❌ Nenhum cloudflared a correr" -ForegroundColor Red
}

# URLs atuais
Write-Host ""
Write-Host "URLs atuais (urls-atuais.txt):" -ForegroundColor Yellow
if (Test-Path "urls-atuais.txt") {
    Get-Content "urls-atuais.txt" | ForEach-Object { Write-Host "  $_" -ForegroundColor White }
} else {
    Write-Host "  ⚠️  Ficheiro não encontrado" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

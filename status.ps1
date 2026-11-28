# ============================================================
# STATUS — Verifica o estado do sistema no Railway
# ============================================================

$ErrorActionPreference = "Continue"

$urlSite   = "https://portal-alumni-track-uniluanda-production.up.railway.app"
$urlReverb = "https://striking-trust-production-505176b1.up.railway.app"

Write-Host ""
Write-Host "=========== ESTADO DO SISTEMA (RAILWAY) ===========" -ForegroundColor Cyan
Write-Host ""

# ─── Serviços online ────────────────────────────────────────
Write-Host "Serviços no Railway:" -ForegroundColor Yellow

# Verifica o site (Laravel)
try {
    $r = Invoke-WebRequest -Uri $urlSite -Method Head -TimeoutSec 10 -UseBasicParsing -ErrorAction Stop
    if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 400) {
        Write-Host "  ✅ Site (Laravel) — ONLINE ($($r.StatusCode))" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  Site (Laravel) — respondeu $($r.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ❌ Site (Laravel) — OFFLINE ou inacessível" -ForegroundColor Red
}

# Verifica o Reverb
try {
    $r = Invoke-WebRequest -Uri $urlReverb -Method Head -TimeoutSec 10 -UseBasicParsing -ErrorAction Stop
    if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 400) {
        Write-Host "  ✅ Reverb (WebSockets) — ONLINE ($($r.StatusCode))" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  Reverb — respondeu $($r.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  ❌ Reverb — OFFLINE ou inacessível" -ForegroundColor Red
}

# ─── Processos locais (apenas informativo) ──────────────────
Write-Host ""
Write-Host "Processos locais (se estiverem a correr no PC):" -ForegroundColor Yellow

$portas = @(8000, 8080, 9000)
$nomes  = @{ 8000 = "Laravel"; 8080 = "Reverb"; 9000 = "PeerServer" }

foreach ($porta in $portas) {
    $linha = netstat -ano | Select-String ":$porta" | Select-String "LISTENING"
    if ($linha) {
        Write-Host "  ✅ $($nomes[$porta]) (porta $porta) — a correr localmente" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  $($nomes[$porta]) (porta $porta) — não está a correr localmente" -ForegroundColor Yellow
    }
}

# ─── Cloudflared (se ainda estiver a usar) ──────────────────
Write-Host ""
Write-Host "Tuneis Cloudflared (local):" -ForegroundColor Yellow
$cf = Get-Process cloudflared -ErrorAction SilentlyContinue
if ($cf) {
    Write-Host "  ✅ $($cf.Count) processos cloudflared a correr localmente" -ForegroundColor Green
} else {
    Write-Host "  ℹ️  Nenhum cloudflared a correr (normal no Railway)" -ForegroundColor Gray
}

# ─── URLs atuais ────────────────────────────────────────────
Write-Host ""
Write-Host "URLs do Railway:" -ForegroundColor Yellow
Write-Host "  Site:   $urlSite" -ForegroundColor White
Write-Host "  Reverb: $urlReverb" -ForegroundColor White

Write-Host ""
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host ""
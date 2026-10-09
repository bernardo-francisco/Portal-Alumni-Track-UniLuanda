# ============================================================
# START-ALL — Arranca o sistema completo automaticamente
# ============================================================
# - Mata processos antigos
# - Arranca Laravel, Reverb e PeerServer em janelas CMD
# - Verifica cada serviço
# - Arranca 3 tuneis Cloudflared
# - Extrai os 3 URLs automaticamente
# - Atualiza o .env
# - Rebuild
# - Abre o browser
# ============================================================

$ErrorActionPreference = "Continue"
$projeto = "C:\xampp\htdocs\Ex_Estudantes_Uniluanda"
$cloudflared = "C:\cloudflared\cloudflared.exe"

# ─── Verificações iniciais ──────────────────────────────────
if (!(Test-Path $cloudflared)) {
    Write-Host "❌ cloudflared.exe não encontrado em: $cloudflared" -ForegroundColor Red
    exit 1
}

if (!(Test-Path "$projeto\.env")) {
    Write-Host "❌ .env não encontrado em: $projeto\.env" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  UNILUANDA VIDEO CALL — ARRANQUE TOTAL" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ─── 1. Matar processos antigos ─────────────────────────────
Write-Host "[1/7] A matar processos antigos..." -ForegroundColor Yellow
Get-Process php,node,cloudflared -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 4
Write-Host "    ✅ Processos antigos terminados" -ForegroundColor Green

# ─── 2. Arrancar os 3 serviços Laravel ──────────────────────
Write-Host "[2/7] A arrancar Laravel, Reverb e PeerServer..." -ForegroundColor Yellow

# --- Laravel ---
Write-Host "    → Laravel..." -ForegroundColor Gray
Start-Process cmd.exe -ArgumentList "/k", "title Laravel && cd /d `"$projeto`" && php artisan serve --host=0.0.0.0 --port=8000" -WindowStyle Normal

# Esperar até 30s pelo Laravel
$ok = $false
for ($i = 0; $i -lt 30; $i++) {
    Start-Sleep -Seconds 1
    if (netstat -ano | Select-String ":8000" | Select-String "LISTENING") {
        $ok = $true
        break
    }
}
if ($ok) {
    Write-Host "    ✅ Laravel OK (porta 8000)" -ForegroundColor Green
} else {
    Write-Host "    ❌ Laravel NÃO arrancou. Verifica a janela 'Laravel'." -ForegroundColor Red
    exit 1
}

# --- Reverb ---
Write-Host "    → Reverb..." -ForegroundColor Gray
Start-Process cmd.exe -ArgumentList "/k", "title Reverb && cd /d `"$projeto`" && php artisan reverb:start --debug" -WindowStyle Normal

$ok = $false
for ($i = 0; $i -lt 30; $i++) {
    Start-Sleep -Seconds 1
    if (netstat -ano | Select-String ":8080" | Select-String "LISTENING") {
        $ok = $true
        break
    }
}
if ($ok) {
    Write-Host "    ✅ Reverb OK (porta 8080)" -ForegroundColor Green
} else {
    Write-Host "    ❌ Reverb NÃO arrancou. Verifica a janela 'Reverb'." -ForegroundColor Red
    exit 1
}

# --- PeerServer ---
Write-Host "    → PeerServer..." -ForegroundColor Gray
Start-Process cmd.exe -ArgumentList "/k", "title PeerServer && cd /d `"$projeto`" && npm run peer" -WindowStyle Normal

$ok = $false
for ($i = 0; $i -lt 30; $i++) {
    Start-Sleep -Seconds 1
    if (netstat -ano | Select-String ":9000" | Select-String "LISTENING") {
        $ok = $true
        break
    }
}
if ($ok) {
    Write-Host "    ✅ PeerServer OK (porta 9000)" -ForegroundColor Green
} else {
    Write-Host "    ❌ PeerServer NÃO arrancou. Verifica a janela 'PeerServer'." -ForegroundColor Red
    exit 1
}

Write-Host "    ✅ Todos os serviços arrancados" -ForegroundColor Green

# ─── 3. Arrancar 3 tuneis Cloudflared ───────────────────────
Write-Host "[3/7] A arrancar tuneis Cloudflared..." -ForegroundColor Yellow

# Limpar logs antigos
$logs = @(
    "$env:TEMP\tunel-laravel.log", "$env:TEMP\tunel-laravel.err",
    "$env:TEMP\tunel-reverb.log",  "$env:TEMP\tunel-reverb.err",
    "$env:TEMP\tunel-peer.log",    "$env:TEMP\tunel-peer.err"
)
foreach ($f in $logs) {
    if (Test-Path $f) { Remove-Item $f -Force -ErrorAction SilentlyContinue }
}

Start-Process $cloudflared -ArgumentList "tunnel", "--protocol", "http2", "--url", "http://localhost:8000" -RedirectStandardOutput "$env:TEMP\tunel-laravel.log" -RedirectStandardError "$env:TEMP\tunel-laravel.err" -WindowStyle Minimized
Start-Sleep -Seconds 2

Start-Process $cloudflared -ArgumentList "tunnel", "--protocol", "http2", "--url", "http://localhost:8080" -RedirectStandardOutput "$env:TEMP\tunel-reverb.log" -RedirectStandardError "$env:TEMP\tunel-reverb.err" -WindowStyle Minimized
Start-Sleep -Seconds 2

Start-Process $cloudflared -ArgumentList "tunnel", "--protocol", "http2", "--url", "http://localhost:9000" -RedirectStandardOutput "$env:TEMP\tunel-peer.log" -RedirectStandardError "$env:TEMP\tunel-peer.err" -WindowStyle Minimized

Write-Host "    ⏳ A aguardar 45 segundos pelos URLs..." -ForegroundColor Yellow
Start-Sleep -Seconds 45

# ─── 4. Extrair URLs ────────────────────────────────────────
Write-Host "[4/7] A extrair URLs dos tuneis..." -ForegroundColor Yellow

function Extrair-URL($logPath) {
    # Ler do .log
    if (Test-Path $logPath) {
        $conteudo = Get-Content $logPath -Raw -ErrorAction SilentlyContinue
        if (![string]::IsNullOrEmpty($conteudo)) {
            $match = [regex]::Match($conteudo, 'https://[a-z0-9\-]+\.trycloudflare\.com')
            if ($match.Success) { return $match.Value }
        }
    }
    # Ler do .err
    $errPath = $logPath -replace '\.log$', '.err'
    if (Test-Path $errPath) {
        $conteudo = Get-Content $errPath -Raw -ErrorAction SilentlyContinue
        if (![string]::IsNullOrEmpty($conteudo)) {
            $match = [regex]::Match($conteudo, 'https://[a-z0-9\-]+\.trycloudflare\.com')
            if ($match.Success) { return $match.Value }
        }
    }
    return $null
}

$urlLaravel = Extrair-URL "$env:TEMP\tunel-laravel.log"
$urlReverb  = Extrair-URL "$env:TEMP\tunel-reverb.log"
$urlPeer    = Extrair-URL "$env:TEMP\tunel-peer.log"

if (!$urlLaravel -or !$urlReverb -or !$urlPeer) {
    Write-Host "    ❌ Não consegui extrair os URLs." -ForegroundColor Red
    Write-Host ""
    Write-Host "  Diagnóstico:" -ForegroundColor Yellow
    if (!$urlLaravel) { Write-Host "  ❌ Laravel — URL não encontrado" -ForegroundColor Red }
    if (!$urlReverb)  { Write-Host "  ❌ Reverb — URL não encontrado" -ForegroundColor Red }
    if (!$urlPeer)    { Write-Host "  ❌ Peer — URL não encontrado" -ForegroundColor Red }
    exit 1
}

Write-Host "    ✅ Laravel: $urlLaravel" -ForegroundColor Green
Write-Host "    ✅ Reverb:  $urlReverb" -ForegroundColor Green
Write-Host "    ✅ Peer:    $urlPeer" -ForegroundColor Green

# ─── 5. Atualizar .env ──────────────────────────────────────
Write-Host "[5/7] A atualizar o .env..." -ForegroundColor Yellow

$hostReverb = $urlReverb -replace '^https://', ''
$hostPeer   = $urlPeer   -replace '^https://', ''

$envFile = "$projeto\.env"
$conteudo = Get-Content $envFile -Raw

$conteudo = $conteudo -replace '(?m)^APP_URL=.*$',          "APP_URL=$urlLaravel"
$conteudo = $conteudo -replace '(?m)^PEER_HOST=.*$',         "PEER_HOST=$hostPeer"
$conteudo = $conteudo -replace '(?m)^REVERB_HOST=.*$',       "REVERB_HOST=$hostReverb"
$conteudo = $conteudo -replace '(?m)^VITE_REVERB_HOST=.*$',  "VITE_REVERB_HOST=$hostReverb"

Set-Content $envFile -Value $conteudo -Encoding UTF8
Write-Host "    ✅ .env atualizado" -ForegroundColor Green

# ─── 6. Limpar cache + rebuild ──────────────────────────────
Write-Host "[6/7] A limpar cache e rebuild..." -ForegroundColor Yellow

Set-Location $projeto
php artisan config:clear   | Out-Null
php artisan optimize:clear | Out-Null
php artisan view:clear     | Out-Null
Remove-Item "public\build" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item "public\hot"   -Force -ErrorAction SilentlyContinue   # ⬅️ NOVO
npm run build | Out-Null

Write-Host "    ✅ Rebuild concluído" -ForegroundColor Green

# ─── 7. Abrir browser ───────────────────────────────────────
Write-Host "[7/7] A abrir o browser..." -ForegroundColor Yellow
Start-Process $urlLaravel

"$urlLaravel`n$urlReverb`n$urlPeer" | Out-File -FilePath "$projeto\urls-atuais.txt" -Encoding UTF8

# ─── FIM ────────────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ✅ SISTEMA PRONTO!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Acede por (PC e telemóvel):" -ForegroundColor Yellow
Write-Host "    $urlLaravel" -ForegroundColor White
Write-Host ""
Write-Host "  URLs guardados em:" -ForegroundColor Gray
Write-Host "    $projeto\urls-atuais.txt" -ForegroundColor Gray
Write-Host ""
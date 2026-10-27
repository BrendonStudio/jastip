# =====================================================================
#  Setup website Jastip di Laravel Herd - jalankan sekali:
#     cd C:\Users\rendy\Herd\jastip
#     powershell -ExecutionPolicy Bypass -File .\setup.ps1
#  Opsi:  -TanpaContoh   (database kosong: hanya akun admin + harga awal)
# =====================================================================
param([switch]$TanpaContoh)

$ErrorActionPreference = "Stop"
Set-Location (Split-Path -Parent $MyInvocation.MyCommand.Path)

function Info($t) { Write-Host "==> $t" -ForegroundColor Cyan }
function Gagal($t) { Write-Host "`n[!] $t" -ForegroundColor Red; exit 1 }

if (-not (Get-Command php -ErrorAction SilentlyContinue)) { Gagal "PHP tidak ditemukan. Buka Herd sekali, lalu buka PowerShell baru." }
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) { Gagal "Composer tidak ditemukan. Buka Herd sekali, lalu buka PowerShell baru." }
Info ("PHP " + (php -r "echo PHP_VERSION;"))

Info "Install paket Laravel (composer install, butuh internet)"
composer install --no-interaction
if ($LASTEXITCODE -ne 0) { Gagal "composer install gagal. Cek koneksi internet lalu ulangi." }

Info "Menyiapkan .env dan database SQLite"
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    php artisan key:generate --force
}
if (-not (Test-Path "database\database.sqlite")) { New-Item -ItemType File -Path "database\database.sqlite" | Out-Null }
New-Item -ItemType Directory -Force -Path "public\uploads" | Out-Null
php artisan optimize:clear | Out-Null

Info "Membuat tabel database"
php artisan migrate:fresh --force
if ($LASTEXITCODE -ne 0) { Gagal "Migrasi database gagal." }
if ($TanpaContoh) { php artisan db:seed --force } else { php artisan db:seed --class=DemoSeeder --force }
if ($LASTEXITCODE -ne 0) { Gagal "Seeding gagal." }

Write-Host ""
Write-Host "=====================================================" -ForegroundColor Green
Write-Host " Website jastip siap di Herd!" -ForegroundColor Green
Write-Host "  Website : http://jastip.test"
Write-Host "  Admin   : http://jastip.test/admin"
Write-Host "            admin@jastip.test / admin12345"
Write-Host "  Penitip : masuk pakai email apa saja; kode masuk"
Write-Host "            tampil di layar selama email belum disetel"
if (-not $TanpaContoh) { Write-Host "  Contoh  : nadia@contoh.test" }
Write-Host "=====================================================" -ForegroundColor Green
Start-Process "http://jastip.test"

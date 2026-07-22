# ============================================================
# Script: instalar_y_levantar.ps1
# Descripcion: Instala Docker Desktop y levanta La China SportBook
# ============================================================

$installer = "C:\Users\lachi\Downloads\DockerDesktopInstaller.exe"
$projectDir = "C:\Users\lachi\Documents\GitHub\Proyecto-III"

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  La China SportBook - Setup Automatico" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan

# --- PASO 1: Verificar instalador ---
if (-Not (Test-Path $installer)) {
    Write-Host "ERROR: No se encontro el instalador en $installer" -ForegroundColor Red
    exit 1
}
$sizeMB = [math]::Round((Get-Item $installer).Length / 1MB, 2)
Write-Host "[1/4] Instalador encontrado: $sizeMB MB" -ForegroundColor Green

# --- PASO 2: Instalar Docker Desktop en modo silencioso ---
Write-Host "[2/4] Instalando Docker Desktop (modo silencioso)..." -ForegroundColor Yellow
Write-Host "      Esto puede tardar 2-5 minutos..."
Start-Process -FilePath $installer -ArgumentList "install --quiet --accept-license" -Wait
Write-Host "      Instalacion completada!" -ForegroundColor Green

# --- PASO 3: Iniciar Docker Desktop ---
Write-Host "[3/4] Iniciando Docker Desktop..." -ForegroundColor Yellow
$dockerDesktop = "C:\Program Files\Docker\Docker\Docker Desktop.exe"
if (Test-Path $dockerDesktop) {
    Start-Process $dockerDesktop
    Write-Host "      Esperando que el Docker Engine arranque (60 segundos)..."
    Start-Sleep -Seconds 60
    
    $maxRetries = 10
    $retryCount = 0
    do {
        $retryCount++
        Write-Host "      Intento $retryCount/$maxRetries - Verificando Docker..."
        $result = & docker ps 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "      Docker Engine ACTIVO!" -ForegroundColor Green
            break
        }
        Start-Sleep -Seconds 15
    } while ($retryCount -lt $maxRetries)
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ADVERTENCIA: Docker tardando en iniciar. Corre manualmente:" -ForegroundColor Yellow
        Write-Host "  cd '$projectDir'" -ForegroundColor White
        Write-Host "  docker-compose up --build -d" -ForegroundColor White
        exit 1
    }
} else {
    Write-Host "ERROR: Docker Desktop no se instalo correctamente" -ForegroundColor Red
    exit 1
}

# --- PASO 4: Levantar contenedores ---
Write-Host "[4/4] Levantando contenedores (PHP + MySQL)..." -ForegroundColor Yellow
Set-Location $projectDir
& docker-compose up --build -d
if ($LASTEXITCODE -eq 0) {
    Write-Host "" 
    Write-Host "============================================" -ForegroundColor Green
    Write-Host "  APLICACION LISTA!" -ForegroundColor Green
    Write-Host "============================================" -ForegroundColor Green
    Write-Host "  URL: http://localhost:8080" -ForegroundColor Cyan
    Write-Host "  Login: http://localhost:8080/login.html" -ForegroundColor Cyan
    Write-Host "  Usuario: jazpaczl@hotmail.com" -ForegroundColor White
    Write-Host "  Clave: 4321" -ForegroundColor White
    Write-Host "============================================" -ForegroundColor Green
    Start-Process "http://localhost:8080"
} else {
    Write-Host "ERROR al levantar los contenedores. Ver logs arriba." -ForegroundColor Red
}

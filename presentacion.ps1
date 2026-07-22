# =========================================================
# Script de Despliegue y Presentación - La China Sports
# =========================================================

Write-Host ""
Write-Host "🚀 Levantando los contenedores de Docker (Web + MySQL 8.0)..." -ForegroundColor Cyan
docker-compose up -d

Start-Sleep -Seconds 2

Write-Host "🌐 Abriendo Aplicación Web y Diagramas en el Navegador..." -ForegroundColor Green

# 1. Aplicación Web Principal
Start-Process "http://localhost:8080"

# 2. Diagrama Entidad-Relación (DER)
Start-Process "$PSScriptRoot\diagrama_der.html"

# 3. Diagrama de Casos de Uso (UML)
Start-Process "$PSScriptRoot\diagrama_casos_de_uso.html"

Write-Host ""
Write-Host "✨ ¡Todo abierto y listo para la presentación con el profesor!" -ForegroundColor Yellow
Write-Host ""

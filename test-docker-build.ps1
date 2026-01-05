# Test Docker build locally (PowerShell)
Write-Host "🐳 Testing Docker build locally..." -ForegroundColor Cyan
Write-Host ""

# Build the Docker image
Write-Host "Step 1: Building Docker image..." -ForegroundColor Yellow
docker build -t guildhall:test .

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Docker build successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Step 2: Testing with docker-compose..." -ForegroundColor Yellow
    Write-Host "Starting services..."
    docker-compose up -d
    
    Write-Host ""
    Write-Host "✅ Services started!" -ForegroundColor Green
    Write-Host ""
    Write-Host "You can now:" -ForegroundColor Cyan
    Write-Host "  - Visit http://localhost:8000"
    Write-Host "  - Check logs: docker-compose logs -f app"
    Write-Host "  - Stop services: docker-compose down"
} else {
    Write-Host "❌ Docker build failed!" -ForegroundColor Red
    Write-Host "Check the error messages above."
    exit 1
}


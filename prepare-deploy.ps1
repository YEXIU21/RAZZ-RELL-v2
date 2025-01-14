# Deployment preparation script for Windows
Write-Host "🚀 Starting deployment preparation..." -ForegroundColor Green

# Create temporary directories
Write-Host "Creating temporary directories..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "deploy-temp/frontend" | Out-Null
New-Item -ItemType Directory -Force -Path "deploy-temp/api" | Out-Null

# Frontend preparation
Write-Host "Preparing frontend..." -ForegroundColor Yellow
Set-Location frontend
npm install
npm run build
Set-Location ..

# Copy frontend build files
Write-Host "Copying frontend build files..." -ForegroundColor Yellow
Copy-Item "frontend/dist/*" -Destination "deploy-temp/frontend" -Recurse -Force

# Backend preparation
Write-Host "Preparing backend files..." -ForegroundColor Yellow
# Copy only necessary backend files
$backendFiles = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "lang",
    "public",
    "resources",
    "routes",
    "storage",
    "artisan",
    "composer.json",
    "composer.lock",
    ".env.example"
)

foreach ($file in $backendFiles) {
    Copy-Item "backend/$file" -Destination "deploy-temp/api" -Recurse -Force
}

# Create deployment packages
Write-Host "Creating deployment packages..." -ForegroundColor Yellow
Compress-Archive -Path "deploy-temp/frontend/*" -DestinationPath "frontend-deploy.zip" -Force
Compress-Archive -Path "deploy-temp/api/*" -DestinationPath "backend-deploy.zip" -Force

# Cleanup
Write-Host "Cleaning up..." -ForegroundColor Yellow
Remove-Item -Path "deploy-temp" -Recurse -Force

Write-Host "✅ Deployment packages created successfully!" -ForegroundColor Green
Write-Host "📦 frontend-deploy.zip - Upload contents to public_html/" -ForegroundColor Cyan
Write-Host "📦 backend-deploy.zip - Upload contents to public_html/api/" -ForegroundColor Cyan
Write-Host "`n⚠️ Remember to:" -ForegroundColor Yellow
Write-Host "1. Configure .env file in public_html/api/" -ForegroundColor Yellow
Write-Host "2. Set proper permissions (755 for folders, 644 for files)" -ForegroundColor Yellow
Write-Host "3. Make storage and bootstrap/cache writable (775)" -ForegroundColor Yellow 
# Create deployment package script for GoDaddy
Write-Host "Creating deployment package for GoDaddy..." -ForegroundColor Green

# Define the source directory and zip file name
$sourceDir = Join-Path $PSScriptRoot "backend"
$deployZip = Join-Path $PSScriptRoot "backend-deploy.zip"

# Remove existing zip if it exists
if (Test-Path $deployZip) {
    Remove-Item $deployZip -Force
    Write-Host "Removed existing deployment package." -ForegroundColor Yellow
}

# Create the deployment package
try {
    Compress-Archive -Path @(
        "$sourceDir\app",
        "$sourceDir\bootstrap",
        "$sourceDir\config",
        "$sourceDir\database",
        "$sourceDir\lang",
        "$sourceDir\public",
        "$sourceDir\resources",
        "$sourceDir\routes",
        "$sourceDir\storage",
        "$sourceDir\.env",
        "$sourceDir\artisan",
        "$sourceDir\composer.json",
        "$sourceDir\package.json",
        "$sourceDir\post-deploy.sh",
        "$sourceDir\socket-server.js"
    ) -DestinationPath $deployZip -Force

    Write-Host "Deployment package created successfully at: $deployZip" -ForegroundColor Green
    Write-Host "`nPackage contains:" -ForegroundColor Cyan
    Write-Host "- Laravel core directories (app, bootstrap, config, etc.)"
    Write-Host "- Configuration files (.env, composer.json, package.json)"
    Write-Host "- Deployment scripts (post-deploy.sh, socket-server.js)"
    Write-Host "`nExcludes:" -ForegroundColor Yellow
    Write-Host "- vendor/ directory (will be installed on server)"
    Write-Host "- node_modules/ directory (will be installed on server)"
    Write-Host "- Development and test files"
    Write-Host "- Cache and log files"
} catch {
    Write-Host "Error creating deployment package: $_" -ForegroundColor Red
    exit 1
} 
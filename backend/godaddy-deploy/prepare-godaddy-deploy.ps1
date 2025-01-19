# PowerShell script to prepare backend for GoDaddy deployment

# Create deployment directory
$deployDir = "godaddy-deploy"
Write-Host "Creating deployment directory: $deployDir"
New-Item -ItemType Directory -Force -Path $deployDir

# Copy essential directories
$essentialDirs = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "vendor",
    "lang"
)

foreach ($dir in $essentialDirs) {
    Write-Host "Copying $dir..."
    Copy-Item -Path $dir -Destination "$deployDir/$dir" -Recurse -Force
}

# Copy essential files
$essentialFiles = @(
    "composer.json",
    "composer.lock",
    "artisan",
    "socket-server.js",
    ".env.example"
)

foreach ($file in $essentialFiles) {
    Write-Host "Copying $file..."
    Copy-Item -Path $file -Destination "$deployDir/$file" -Force
}

# Create production .env
Write-Host "Creating production .env..."
Copy-Item -Path ".env.example" -Destination "$deployDir/.env"

# Create required directories
New-Item -ItemType Directory -Force -Path "$deployDir/storage/framework/sessions"
New-Item -ItemType Directory -Force -Path "$deployDir/storage/framework/views"
New-Item -ItemType Directory -Force -Path "$deployDir/storage/framework/cache"
New-Item -ItemType Directory -Force -Path "$deployDir/storage/logs"

Write-Host "`nDeployment package created in $deployDir"
Write-Host "`nNext steps:"
Write-Host "1. Configure $deployDir/.env with your GoDaddy production settings"
Write-Host "2. Upload the contents of $deployDir to public_html/api on GoDaddy"
Write-Host "3. Set proper file permissions on GoDaddy"
Write-Host "4. Run database migrations through phpMyAdmin" 
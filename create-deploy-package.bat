@echo off
echo Creating deployment package for GoDaddy...

REM Remove existing zip if it exists
if exist backend-deploy.zip del backend-deploy.zip

REM Create new deployment package
powershell -Command "Compress-Archive -Path '.\backend\app', '.\backend\bootstrap', '.\backend\config', '.\backend\database', '.\backend\lang', '.\backend\public', '.\backend\resources', '.\backend\routes', '.\backend\storage', '.\backend\.env', '.\backend\artisan', '.\backend\composer.json', '.\backend\package.json', '.\backend\post-deploy.sh', '.\backend\socket-server.js' -DestinationPath 'backend-deploy.zip' -Force"

echo.
echo Deployment package created successfully!
echo.
echo Package contains:
echo - Laravel core directories (app, bootstrap, config, etc.)
echo - Configuration files (.env, composer.json, package.json)
echo - Deployment scripts (post-deploy.sh, socket-server.js)
echo.
echo Excludes:
echo - vendor/ directory (will be installed on server)
echo - node_modules/ directory (will be installed on server)
echo - Development and test files
echo - Cache and log files
echo.
pause 
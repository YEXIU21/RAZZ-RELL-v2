@echo off
echo === Preparing Laravel Backend for GoDaddy Deployment ===

rem Clean up any existing deployment directory
if exist godaddy-deploy (
    echo Cleaning up old deployment directory...
    rd /s /q godaddy-deploy
)

rem Create deployment directory
echo Creating deployment directory...
mkdir godaddy-deploy

rem Check if backend directory exists
if not exist backend (
    echo Error: backend directory not found!
    echo Please run this script from the root project directory.
    pause
    exit /b 1
)

cd backend

rem Create exclude.txt file first
echo Creating exclude list...
(
echo node_modules\
echo .git\
echo tests\
echo .env.example
echo .gitignore
echo .gitattributes
echo README.md
echo phpunit.xml
echo vite.config.js
echo Dockerfile
echo .dockerignore
echo railway.json
echo railway.socket.json
echo post-deploy.sh
echo Procfile
echo socket-server.js
echo prepare-deploy.bat
echo prepare-godaddy-deploy.ps1
echo deploy-godaddy.sh
) > exclude.txt

rem Copy all Laravel files EXCEPT excluded ones
echo Copying Laravel files...
xcopy /E /I /EXCLUDE:exclude.txt . ..\godaddy-deploy\

rem Check if public directory exists before renaming
if exist ..\godaddy-deploy\public (
    echo Renaming public folder to public_html...
    cd ..
    ren godaddy-deploy\public public_html
) else (
    echo Error: public directory not found in deployment package!
    cd ..
    pause
    exit /b 1
)

rem Copy production .env file
echo Copying production .env file...
copy backend\.env godaddy-deploy\.env

echo === Deployment Files Ready ===
echo.
echo Your files are ready in the 'godaddy-deploy' folder:
echo.
echo 1. From godaddy-deploy folder:
echo    - Upload all files EXCEPT public_html folder to: /home/uzgoj867hnp5/
echo.
echo 2. From godaddy-deploy/public_html folder:
echo    - Upload all contents to: /home/uzgoj867hnp5/public_html/
echo.
echo IMPORTANT DEPLOYMENT STEPS:
echo 1. Configure .env file with your production settings
echo 2. Set file permissions:
echo    - Folders: 755
echo    - Files: 644
echo    - storage/ and bootstrap/cache: 775
echo.
echo Press any key to exit...
pause
@echo off
echo === Preparing Laravel Backend for GoDaddy Deployment ===

rem Create deployment directory
echo Creating deployment directory...
mkdir godaddy-deploy
cd backend

rem Copy all Laravel files EXCEPT node_modules and other unnecessary files
echo Copying Laravel files...
xcopy /E /I /EXCLUDE:exclude.txt . ..\godaddy-deploy\

rem Create exclude.txt file
echo node_modules\ > exclude.txt
echo .git\ >> exclude.txt
echo tests\ >> exclude.txt
echo .env.example >> exclude.txt
echo .gitignore >> exclude.txt
echo .gitattributes >> exclude.txt
echo README.md >> exclude.txt
echo phpunit.xml >> exclude.txt
echo vite.config.js >> exclude.txt
echo Dockerfile >> exclude.txt
echo .dockerignore >> exclude.txt
echo railway.json >> exclude.txt
echo railway.socket.json >> exclude.txt
echo post-deploy.sh >> exclude.txt
echo Procfile >> exclude.txt
echo socket-server.js >> exclude.txt
echo prepare-deploy.bat >> exclude.txt
echo prepare-godaddy-deploy.ps1 >> exclude.txt
echo deploy-godaddy.sh >> exclude.txt

rem Move public folder contents to public_html
echo Moving public folder to public_html...
ren ..\godaddy-deploy\public public_html

echo === Deployment Package Created ===
echo.
echo Your deployment package is ready in the 'godaddy-deploy' folder.
echo.
echo IMPORTANT DEPLOYMENT STEPS:
echo 1. Upload all contents EXCEPT public_html folder to the root directory
echo 2. Upload contents of public_html folder to public_html on GoDaddy
echo 3. Configure .env file with your production settings
echo 4. Set file permissions:
echo    - Folders: 755
echo    - Files: 644
echo    - storage/ and bootstrap/cache: 775
echo.
pause 
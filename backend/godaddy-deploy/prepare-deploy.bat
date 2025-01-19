@echo off
echo Creating deployment directory...
rmdir /s /q godaddy-deploy 2>nul
mkdir godaddy-deploy

echo Copying essential directories...
xcopy /E /I app godaddy-deploy\app
xcopy /E /I bootstrap godaddy-deploy\bootstrap
xcopy /E /I config godaddy-deploy\config
xcopy /E /I database godaddy-deploy\database
xcopy /E /I public godaddy-deploy\public
xcopy /E /I resources godaddy-deploy\resources
xcopy /E /I routes godaddy-deploy\routes
xcopy /E /I storage godaddy-deploy\storage
xcopy /E /I vendor godaddy-deploy\vendor
xcopy /E /I lang godaddy-deploy\lang

echo Copying essential files...
copy composer.json godaddy-deploy\
copy composer.lock godaddy-deploy\
copy artisan godaddy-deploy\
copy socket-server.js godaddy-deploy\
copy .env.example godaddy-deploy\.env

echo Creating required directories...
mkdir godaddy-deploy\storage\framework\sessions
mkdir godaddy-deploy\storage\framework\views
mkdir godaddy-deploy\storage\framework\cache
mkdir godaddy-deploy\storage\logs

echo Deployment package created in godaddy-deploy
echo.
echo Next steps:
echo 1. Configure godaddy-deploy\.env with your GoDaddy production settings
echo 2. Upload the contents of godaddy-deploy to public_html/api on GoDaddy
echo 3. Set proper file permissions on GoDaddy
echo 4. Run database migrations through phpMyAdmin 
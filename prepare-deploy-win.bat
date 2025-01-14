@echo off
echo 🚀 Preparing deployment packages for GoDaddy...

rem Prepare frontend
echo 📦 Building frontend...
cd frontend
call npm install
call npm run build
cd ..

rem Create frontend deployment zip
echo 🔧 Creating frontend deployment package...
cd frontend\dist
tar -czf ..\..\frontend-deploy.zip *
cd ..\..

rem Prepare backend
echo 📦 Creating backend deployment package...
rem Create temporary directory for backend files
mkdir deploy-temp\api

rem Copy necessary backend files
xcopy /E /I backend\app deploy-temp\api\app
xcopy /E /I backend\bootstrap deploy-temp\api\bootstrap
xcopy /E /I backend\config deploy-temp\api\config
xcopy /E /I backend\database deploy-temp\api\database
xcopy /E /I backend\public deploy-temp\api\public
xcopy /E /I backend\resources deploy-temp\api\resources
xcopy /E /I backend\routes deploy-temp\api\routes
xcopy /E /I backend\storage deploy-temp\api\storage
xcopy /E /I backend\vendor deploy-temp\api\vendor
copy backend\artisan deploy-temp\api\
copy backend\composer.json deploy-temp\api\
copy backend\composer.lock deploy-temp\api\
copy backend\.env.example deploy-temp\api\.env

rem Create backend deployment zip
cd deploy-temp\api
tar -czf ..\..\backend-deploy.zip *
cd ..\..

rem Cleanup
rmdir /S /Q deploy-temp

echo ✅ Deployment packages created:
echo    - frontend-deploy.zip (Upload contents to public_html/)
echo    - backend-deploy.zip (Upload contents to public_html/api/)
echo.
echo ⚠️ Remember to:
echo 1. Configure .env file in public_html/api/
echo 2. Set proper permissions (755 for folders, 644 for files)
echo 3. Make storage and bootstrap/cache writable (775) 
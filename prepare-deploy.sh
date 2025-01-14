#!/bin/bash

echo "🚀 Preparing deployment packages for GoDaddy..."

# Prepare frontend
echo "📦 Building frontend..."
cd frontend
npm install
npm run build
cd ..

# Create frontend deployment zip
echo "🔧 Creating frontend deployment package..."
cd frontend/dist
zip -r ../../frontend-deploy.zip ./*
cd ../..

# Prepare backend
echo "📦 Creating backend deployment package..."
cd backend
# Create temporary directory for backend files
mkdir -p ../deploy-temp/api

# Copy necessary backend files
cp -r app ../deploy-temp/api/
cp -r bootstrap ../deploy-temp/api/
cp -r config ../deploy-temp/api/
cp -r database ../deploy-temp/api/
cp -r public ../deploy-temp/api/
cp -r resources ../deploy-temp/api/
cp -r routes ../deploy-temp/api/
cp -r storage ../deploy-temp/api/
cp -r vendor ../deploy-temp/api/
cp artisan ../deploy-temp/api/
cp composer.json ../deploy-temp/api/
cp composer.lock ../deploy-temp/api/
cp .env.example ../deploy-temp/api/.env

# Create backend deployment zip
cd ../deploy-temp
zip -r ../backend-deploy.zip ./api
cd ..

# Cleanup
rm -rf deploy-temp

echo "✅ Deployment packages created:"
echo "   - frontend-deploy.zip (Upload contents to public_html/)"
echo "   - backend-deploy.zip (Upload contents to public_html/api/)"
echo ""
echo "⚠️ Remember to:"
echo "1. Configure .env file in public_html/api/"
echo "2. Set proper permissions (755 for folders, 644 for files)"
echo "3. Make storage and bootstrap/cache writable (775)" 
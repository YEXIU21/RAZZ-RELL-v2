#!/bin/bash

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Generate app key if not exists
php artisan key:generate --force

# Optimize the application
php artisan optimize

# Create production .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env file. Please update with production values."
fi

# Create storage link
php artisan storage:link

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 artisan

echo "Backend build complete! Ready for upload to GoDaddy."
echo "Remember to:"
echo "1. Update .env with production database credentials"
echo "2. Upload contents to public_html directory"
echo "3. Run migrations on the production server" 
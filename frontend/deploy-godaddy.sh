#!/bin/bash

# Install dependencies
npm install

# Build the application
npm run build

# Create a deployment directory
mkdir -p deploy

# Copy build files to deployment directory
cp -r dist/* deploy/

# Set proper permissions
find deploy -type f -exec chmod 644 {} \;
find deploy -type d -exec chmod 755 {} \;

echo "Frontend build complete! Ready for upload to GoDaddy."
echo "Remember to:"
echo "1. Upload contents of the 'deploy' directory to your desired location on GoDaddy"
echo "2. Update any environment variables in .env.production"
echo "3. Configure your domain/subdomain settings in GoDaddy" 
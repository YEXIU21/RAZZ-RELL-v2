const fs = require('fs');
const path = require('path');

function checkDeploymentReadiness() {
  console.log('🔍 Running pre-deployment checks...\n');
  const errors = [];
  const warnings = [];

  // Check required files
  const requiredFiles = [
    'vercel.json',
    '.env.production',
    'package.json',
    'vite.config.js'
  ];

  console.log('Checking required files...');
  requiredFiles.forEach(file => {
    if (!fs.existsSync(path.join(process.cwd(), file))) {
      errors.push(`Missing required file: ${file}`);
    } else {
      console.log(`✅ Found ${file}`);
    }
  });

  // Check package.json
  console.log('\nChecking package.json configuration...');
  const packageJson = require(path.join(process.cwd(), 'package.json'));
  
  if (!packageJson.scripts?.build) {
    errors.push('Missing build script in package.json');
  } else {
    console.log('✅ Build script found');
  }

  // Check environment variables
  console.log('\nChecking environment variables...');
  const envProduction = fs.readFileSync(path.join(process.cwd(), '.env.production'), 'utf8');
  const requiredEnvVars = [
    'VITE_API_URL',
    'VITE_SOCKET_URL',
    'VITE_GOOGLE_MAPS_API_KEY'
  ];

  requiredEnvVars.forEach(envVar => {
    if (!envProduction.includes(envVar)) {
      errors.push(`Missing environment variable: ${envVar}`);
    } else {
      console.log(`✅ Found ${envVar}`);
    }
  });

  // Check Vercel configuration
  console.log('\nChecking Vercel configuration...');
  const vercelConfig = require(path.join(process.cwd(), 'vercel.json'));
  
  if (!vercelConfig.framework === 'vite') {
    warnings.push('Framework not set to "vite" in vercel.json');
  } else {
    console.log('✅ Framework configuration correct');
  }

  if (!vercelConfig.headers) {
    warnings.push('Security headers not configured in vercel.json');
  } else {
    console.log('✅ Security headers configured');
  }

  // Report results
  console.log('\n📋 Pre-deployment Check Results:');
  
  if (warnings.length > 0) {
    console.log('\n⚠️ Warnings:');
    warnings.forEach(warning => console.log(`- ${warning}`));
  }

  if (errors.length > 0) {
    console.log('\n❌ Errors:');
    errors.forEach(error => console.log(`- ${error}`));
    process.exit(1);
  }

  if (warnings.length === 0 && errors.length === 0) {
    console.log('\n✨ All checks passed! Ready for deployment.');
    process.exit(0);
  }
}

checkDeploymentReadiness(); 
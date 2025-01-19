const axios = require('axios');

async function testDeployment() {
  const API_URL = process.env.VITE_API_URL;
  const SOCKET_URL = process.env.VITE_SOCKET_URL;
  
  console.log('🔍 Testing deployment configuration...\n');

  try {
    // Test API connection
    console.log('Testing API connection...');
    const apiHealth = await axios.get(`${API_URL}/api/health`);
    console.log('✅ API Health check:', apiHealth.data.status);

    // Test Socket Server
    console.log('\nTesting Socket Server connection...');
    const socketHealth = await axios.get(`${SOCKET_URL}/health`);
    console.log('✅ Socket Server Health check:', socketHealth.data.status);

    // Test CORS Headers
    console.log('\nTesting CORS configuration...');
    const corsResponse = await axios.options(`${API_URL}/api/health`);
    const corsHeaders = corsResponse.headers;
    console.log('✅ CORS Headers present:', !!corsHeaders['access-control-allow-origin']);

    console.log('\n✨ All tests passed successfully!');
    process.exit(0);
  } catch (error) {
    console.error('\n❌ Deployment test failed:');
    console.error(error.message);
    process.exit(1);
  }
}

testDeployment(); 
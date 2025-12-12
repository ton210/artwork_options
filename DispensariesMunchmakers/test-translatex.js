require('dotenv').config();
const axios = require('axios');

const API_KEY = 'AIzaTXCwvZt7iiRcuNDDjwpctNXrQnvayJ5KeDB';
const API_URL = 'https://api.translatex.com/translate';

async function testTranslateX() {
  console.log('Testing TranslateX API...\n');

  const testCases = [
    { text: 'Hello, how are you?', target: 'es', expected: 'Spanish' },
    { text: 'Find the best cannabis dispensaries', target: 'fr', expected: 'French' },
    { text: 'Top rated dispensary', target: 'de', expected: 'German' }
  ];

  for (const test of testCases) {
    try {
      console.log(`Testing ${test.expected} translation...`);
      console.log(`Original: "${test.text}"`);

      const url = `${API_URL}?sl=en&tl=${test.target}&key=${API_KEY}`;
      const body = `text=${encodeURIComponent(test.text)}`;

      const response = await axios.post(
        url,
        body,
        {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          timeout: 10000
        }
      );

      if (response.data && response.data.translation && response.data.translation.length > 0) {
        const translated = response.data.translation[0];
        console.log(`Translated (${test.target}): "${translated}"`);
        console.log('✅ Success!\n');
      } else {
        console.log('❌ Unexpected response format:', JSON.stringify(response.data, null, 2));
        console.log('');
      }
    } catch (error) {
      console.log(`❌ Error: ${error.message}`);
      if (error.response) {
        console.log('Response status:', error.response.status);
        console.log('Response data:', JSON.stringify(error.response.data, null, 2));
      }
      console.log('');
    }
  }

  console.log('Test complete!');
}

testTranslateX();

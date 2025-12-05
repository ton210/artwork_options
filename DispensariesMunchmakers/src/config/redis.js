const redis = require('redis');
require('dotenv').config();

let client;

async function getRedisClient() {
  if (!client) {
    client = redis.createClient({
      url: process.env.REDIS_URL || 'redis://localhost:6379',
      socket: {
        reconnectStrategy: (retries) => {
          if (retries > 10) {
            return new Error('Redis reconnection failed');
          }
          return retries * 100;
        }
      }
    });

    client.on('error', (err) => {
      console.error('Redis Client Error:', err);
    });

    client.on('connect', () => {
      console.log('Redis connected successfully');
    });

    await client.connect();
  }

  return client;
}

module.exports = { getRedisClient };

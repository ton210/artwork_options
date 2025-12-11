const { Pool } = require('pg');
require('dotenv').config();

// Enable SSL for Heroku databases (even in development)
const isHerokuDb = process.env.DATABASE_URL && process.env.DATABASE_URL.includes('amazonaws.com');
const useSSL = process.env.NODE_ENV === 'production' || isHerokuDb;

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: useSSL ? {
    rejectUnauthorized: false
  } : false,
  max: 20,
  idleTimeoutMillis: 30000,
  connectionTimeoutMillis: 2000,
});

pool.on('error', (err) => {
  console.error('Unexpected error on idle client', err);
  process.exit(-1);
});

pool.on('connect', () => {
  console.log('Database connected successfully');
});

module.exports = {
  query: (text, params) => pool.query(text, params),
  pool
};

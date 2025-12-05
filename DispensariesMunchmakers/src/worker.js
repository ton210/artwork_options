require('dotenv').config();
const Bull = require('bull');
const scraper = require('./services/scraper');
const rankingCalculator = require('./services/rankingCalculator');

// Create job queues
const scrapeQueue = new Bull('scrape', process.env.REDIS_URL || 'redis://localhost:6379');
const rankingQueue = new Bull('ranking', process.env.REDIS_URL || 'redis://localhost:6379');

// Scrape job processor
scrapeQueue.process(async (job) => {
  const { type, stateId, countyId } = job.data;

  console.log(`Processing scrape job: ${type}`);

  try {
    let result;

    switch (type) {
      case 'county':
        result = await scraper.scrapeCounty(countyId);
        break;
      case 'state':
        result = await scraper.scrapeState(stateId);
        break;
      case 'all':
        result = await scraper.scrapeAllStates();
        break;
      default:
        throw new Error(`Unknown scrape type: ${type}`);
    }

    console.log(`Scrape job completed:`, result);
    return result;
  } catch (error) {
    console.error('Scrape job failed:', error);
    throw error;
  }
});

// Ranking calculation processor
rankingQueue.process(async (job) => {
  console.log('Processing ranking calculation job');

  try {
    const result = await rankingCalculator.calculateAllRankings();
    console.log('Ranking calculation completed:', result);
    return result;
  } catch (error) {
    console.error('Ranking calculation failed:', error);
    throw error;
  }
});

// Job event listeners
scrapeQueue.on('completed', (job, result) => {
  console.log(`Scrape job ${job.id} completed:`, result);
});

scrapeQueue.on('failed', (job, err) => {
  console.error(`Scrape job ${job.id} failed:`, err.message);
});

rankingQueue.on('completed', (job, result) => {
  console.log(`Ranking job ${job.id} completed:`, result);
});

rankingQueue.on('failed', (job, err) => {
  console.error(`Ranking job ${job.id} failed:`, err.message);
});

console.log('Worker started and listening for jobs...');
console.log('  - Scrape queue: ready');
console.log('  - Ranking queue: ready');

// Graceful shutdown
process.on('SIGTERM', async () => {
  console.log('SIGTERM received, shutting down worker...');
  await scrapeQueue.close();
  await rankingQueue.close();
  process.exit(0);
});

module.exports = { scrapeQueue, rankingQueue };

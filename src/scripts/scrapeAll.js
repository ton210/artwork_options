#!/usr/bin/env node
require('dotenv').config();
const scraper = require('../services/scraper');

async function main() {
  console.log('Starting full scrape of all states...\n');
  console.log('⚠️  WARNING: This will take a long time and make many API calls!');
  console.log('Press Ctrl+C to cancel within 5 seconds...\n');

  await new Promise(resolve => setTimeout(resolve, 5000));

  try {
    const result = await scraper.scrapeAllStates();

    console.log('\n✓ Full scrape completed!');
    console.log(`  Total found: ${result.totalFound}`);
    console.log(`  Total added: ${result.totalAdded}`);
    console.log(`  Total updated: ${result.totalUpdated}`);
    console.log(`  States processed: ${result.states.length}`);

    process.exit(0);
  } catch (error) {
    console.error('\n✗ Scraping failed:', error);
    process.exit(1);
  }
}

main();

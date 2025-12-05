#!/usr/bin/env node
require('dotenv').config();
const scraper = require('../services/scraper');
const { State } = require('../models/State');

async function main() {
  const stateName = process.argv[2];

  if (!stateName) {
    console.error('Usage: node scrapeState.js <state-slug>');
    console.error('Example: node scrapeState.js california');
    process.exit(1);
  }

  try {
    const state = await State.findBySlug(stateName);

    if (!state) {
      console.error(`State not found: ${stateName}`);
      process.exit(1);
    }

    console.log(`Starting scrape for ${state.name}...\n`);

    const result = await scraper.scrapeState(state.id);

    console.log('\n✓ Scraping completed!');
    console.log(`  Total found: ${result.totalFound}`);
    console.log(`  Total added: ${result.totalAdded}`);
    console.log(`  Total updated: ${result.totalUpdated}`);

    process.exit(0);
  } catch (error) {
    console.error('\n✗ Scraping failed:', error);
    process.exit(1);
  }
}

main();

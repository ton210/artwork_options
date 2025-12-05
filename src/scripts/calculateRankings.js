#!/usr/bin/env node
require('dotenv').config();
const rankingCalculator = require('../services/rankingCalculator');

async function main() {
  console.log('Starting manual ranking calculation...\n');

  try {
    const result = await rankingCalculator.calculateAllRankings();

    console.log('\n✓ Ranking calculation completed successfully!');
    console.log(`  Processed: ${result.processed} dispensaries`);

    process.exit(0);
  } catch (error) {
    console.error('\n✗ Ranking calculation failed:', error);
    process.exit(1);
  }
}

main();

require('dotenv').config();
const scraper = require('./src/services/scraper');
const db = require('./src/config/database');

const states = [
  {abbr: 'ND', id: 60, name: 'North Dakota'},
  {abbr: 'NH', id: 56, name: 'New Hampshire'},
  {abbr: 'OK', id: 62, name: 'Oklahoma'},
  {abbr: 'PA', id: 64, name: 'Pennsylvania'},
  {abbr: 'SD', id: 66, name: 'South Dakota'},
  {abbr: 'UT', id: 67, name: 'Utah'},
  {abbr: 'WV', id: 71, name: 'West Virginia'}
];

async function scrapeFinal7() {
  console.log('Scraping final 7 states: ND, NH, OK, PA, SD, UT, WV\n');

  for (const state of states) {
    try {
      console.log(`\n${'='.repeat(60)}`);
      console.log(`Scraping ${state.name} (${state.abbr})`);
      console.log(`${'='.repeat(60)}`);

      const result = await scraper.scrapeState(state.id);

      console.log(`✓ ${state.name} complete: ${result.totalAdded} added, ${result.totalUpdated} updated`);
    } catch (error) {
      console.error(`❌ Error scraping ${state.name}:`, error.message);
    }
  }

  await db.pool.end();
  console.log('\n✅ All 7 states processed!');
}

scrapeFinal7();

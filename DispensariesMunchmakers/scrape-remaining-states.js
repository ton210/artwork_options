require('dotenv').config();
const scraper = require('./src/services/scraper');
const db = require('./src/config/database');

// Only states that haven't been scraped yet (0 dispensaries)
const remainingStateAbbrs = ['HI', 'KY', 'LA', 'MS', 'NH', 'ND', 'OK', 'PA', 'SD', 'UT', 'WV'];

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function scrapeRemainingStates() {
  console.log('Starting scrape of 11 remaining medical-only states...\n');
  console.log('States to process: HI, KY, LA, MS, NH, ND, OK, PA, SD, UT, WV\n');

  const results = {
    totalFound: 0,
    totalAdded: 0,
    totalUpdated: 0,
    states: []
  };

  try {
    for (const abbr of remainingStateAbbrs) {
      const stateResult = await db.query(
        'SELECT id, name FROM states WHERE abbreviation = $1',
        [abbr]
      );

      if (stateResult.rows.length === 0) {
        console.log(`⚠️  State not found: ${abbr}`);
        continue;
      }

      const state = stateResult.rows[0];
      console.log(`\n${'='.repeat(60)}`);
      console.log(`Scraping ${state.name} (${abbr})`);
      console.log(`${'='.repeat(60)}\n`);

      const stateStats = await scraper.scrapeState(state.id);

      results.totalFound += stateStats.totalFound;
      results.totalAdded += stateStats.totalAdded;
      results.totalUpdated += stateStats.totalUpdated;
      results.states.push({
        name: state.name,
        abbreviation: abbr,
        ...stateStats
      });

      console.log(`\n✓ Completed ${state.name}: ${stateStats.totalAdded} added, ${stateStats.totalUpdated} updated`);

      // Rate limiting between states
      if (remainingStateAbbrs.indexOf(abbr) < remainingStateAbbrs.length - 1) {
        console.log(`\nWaiting 5 seconds before next state...`);
        await delay(5000);
      }
    }

    console.log(`\n${'='.repeat(60)}`);
    console.log('SCRAPING COMPLETED - FINAL SUMMARY');
    console.log(`${'='.repeat(60)}\n`);
    console.log(`Total dispensaries found: ${results.totalFound}`);
    console.log(`Total dispensaries added: ${results.totalAdded}`);
    console.log(`Total dispensaries updated: ${results.totalUpdated}\n`);

    console.log('State-by-state breakdown:');
    results.states.forEach(state => {
      console.log(`  ${state.name} (${state.abbreviation}): ${state.totalAdded} added, ${state.totalUpdated} updated`);
    });

    console.log('\n✓ All remaining medical-only states have been scraped successfully!');

  } catch (error) {
    console.error('\n❌ Error during scraping:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

// Run the scraper
if (require.main === module) {
  scrapeRemainingStates()
    .then(() => {
      console.log('\nScript finished successfully');
      process.exit(0);
    })
    .catch((err) => {
      console.error('\nScript failed:', err);
      process.exit(1);
    });
}

module.exports = { scrapeRemainingStates };

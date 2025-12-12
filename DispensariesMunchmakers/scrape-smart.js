require('dotenv').config();
const scraper = require('./src/services/scraper');
const db = require('./src/config/database');

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// Minimum dispensaries to consider a state "complete"
const MIN_DISPENSARIES_TO_SKIP = 10;

async function getStateProgress() {
  const result = await db.query(`
    SELECT s.id, s.name, s.abbreviation, COUNT(d.id) as dispensary_count
    FROM states s
    LEFT JOIN counties c ON c.state_id = s.id
    LEFT JOIN dispensaries d ON d.county_id = c.id AND d.is_active = true
    GROUP BY s.id, s.name, s.abbreviation
    ORDER BY s.id
  `);
  return result.rows;
}

async function scrapeSmartResumable(targetStates = []) {
  console.log('🚀 Starting SMART resumable scraper...\n');

  try {
    // Get current progress
    const allStates = await getStateProgress();

    // Filter to target states if specified, otherwise all states
    let statesToProcess = allStates;
    if (targetStates.length > 0) {
      statesToProcess = allStates.filter(s => targetStates.includes(s.abbreviation));
    }

    // Separate complete vs incomplete states
    const incomplete = statesToProcess.filter(s => s.dispensary_count < MIN_DISPENSARIES_TO_SKIP);
    const complete = statesToProcess.filter(s => s.dispensary_count >= MIN_DISPENSARIES_TO_SKIP);

    console.log(`📊 Status Summary:`);
    console.log(`   Total states to check: ${statesToProcess.length}`);
    console.log(`   Already complete (${MIN_DISPENSARIES_TO_SKIP}+ dispensaries): ${complete.length}`);
    console.log(`   Need scraping (< ${MIN_DISPENSARIES_TO_SKIP} dispensaries): ${incomplete.length}\n`);

    if (complete.length > 0) {
      console.log('✅ Skipping complete states:');
      complete.forEach(s => console.log(`   - ${s.name} (${s.abbreviation}): ${s.dispensary_count} dispensaries`));
      console.log('');
    }

    if (incomplete.length === 0) {
      console.log('🎉 All states already have sufficient dispensaries!');
      return;
    }

    console.log('🔄 States/provinces to scrape:');
    incomplete.forEach(s => console.log(`   - ${s.name} (${s.abbreviation}): ${s.dispensary_count} dispensaries`));
    console.log('');

    const results = {
      totalFound: 0,
      totalAdded: 0,
      totalUpdated: 0,
      states: []
    };

    // Process only incomplete states
    for (let i = 0; i < incomplete.length; i++) {
      const state = incomplete[i];

      try {
        console.log(`\n${'='.repeat(60)}`);
        console.log(`[${i + 1}/${incomplete.length}] Scraping ${state.name} (${state.abbreviation})`);
        console.log(`Current count: ${state.dispensary_count} dispensaries`);
        console.log(`${'='.repeat(60)}\n`);

        const stateStats = await scraper.scrapeState(state.id);

        results.totalFound += stateStats.totalFound;
        results.totalAdded += stateStats.totalAdded;
        results.totalUpdated += stateStats.totalUpdated;
        results.states.push({
          name: state.name,
          abbreviation: state.abbreviation,
          previousCount: state.dispensary_count,
          ...stateStats
        });

        console.log(`\n✅ Completed ${state.name}: ${stateStats.totalAdded} added, ${stateStats.totalUpdated} updated`);
        console.log(`   New total: ${state.dispensary_count + stateStats.totalAdded}`);

        // Rate limiting between states
        if (i < incomplete.length - 1) {
          console.log(`\n⏳ Waiting 5 seconds before next state...\n`);
          await delay(5000);
        }

      } catch (error) {
        console.error(`\n❌ Error scraping ${state.name}:`, error.message);
        console.log(`   Continuing to next state...\n`);

        results.states.push({
          name: state.name,
          abbreviation: state.abbreviation,
          error: error.message,
          totalFound: 0,
          totalAdded: 0,
          totalUpdated: 0
        });
      }
    }

    console.log(`\n${'='.repeat(60)}`);
    console.log('SCRAPING COMPLETED - FINAL SUMMARY');
    console.log(`${'='.repeat(60)}\n`);
    console.log(`Total dispensaries found: ${results.totalFound}`);
    console.log(`Total dispensaries added: ${results.totalAdded}`);
    console.log(`Total dispensaries updated: ${results.totalUpdated}\n`);

    console.log('Results by state/province:');
    results.states.forEach(state => {
      if (state.error) {
        console.log(`  ❌ ${state.name} (${state.abbreviation}): ERROR - ${state.error}`);
      } else {
        console.log(`  ✅ ${state.name} (${state.abbreviation}): ${state.totalAdded} added, ${state.totalUpdated} updated`);
      }
    });

    console.log(`\n✅ Scraping complete! ${results.totalAdded} new dispensaries added.`);

  } catch (error) {
    console.error('\n❌ Fatal error during scraping:', error);
    throw error;
  } finally {
    try {
      await db.pool.end();
    } catch (e) {
      // Connection may already be closed
    }
  }
}

// Run the scraper
if (require.main === module) {
  // Check for command line arguments to specify provinces
  const args = process.argv.slice(2);
  const targetStates = args.length > 0 ? args : [];

  if (targetStates.length > 0) {
    console.log(`Target states specified: ${targetStates.join(', ')}\n`);
  }

  scrapeSmartResumable(targetStates)
    .then(() => {
      console.log('\nScript finished successfully');
      process.exit(0);
    })
    .catch((err) => {
      console.error('\nScript failed:', err);
      process.exit(1);
    });
}

module.exports = { scrapeSmartResumable };

require('dotenv').config();
const scraper = require('./src/services/scraper');
const db = require('./src/config/database');

// All 13 Canadian provinces/territories
const canadianProvinces = ['AB', 'BC', 'MB', 'NB', 'NL', 'NT', 'NS', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT'];

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function scrapeCanada() {
  console.log('Starting scrape of 13 Canadian provinces/territories...\n');
  console.log('Provinces: AB, BC, MB, NB, NL, NT, NS, NU, ON, PE, QC, SK, YT\n');

  const results = {
    totalFound: 0,
    totalAdded: 0,
    totalUpdated: 0,
    provinces: []
  };

  try {
    for (const abbr of canadianProvinces) {
      const provinceResult = await db.query(
        'SELECT id, name FROM states WHERE abbreviation = $1',
        [abbr]
      );

      if (provinceResult.rows.length === 0) {
        console.log(`⚠️  Province not found: ${abbr}`);
        continue;
      }

      const province = provinceResult.rows[0];
      console.log(`\n${'='.repeat(60)}`);
      console.log(`Scraping ${province.name} (${abbr})`);
      console.log(`${'='.repeat(60)}\n`);

      const provinceStats = await scraper.scrapeState(province.id);

      results.totalFound += provinceStats.totalFound;
      results.totalAdded += provinceStats.totalAdded;
      results.totalUpdated += provinceStats.totalUpdated;
      results.provinces.push({
        name: province.name,
        abbreviation: abbr,
        ...provinceStats
      });

      console.log(`\n✓ Completed ${province.name}: ${provinceStats.totalAdded} added, ${provinceStats.totalUpdated} updated`);

      // Rate limiting between provinces
      if (canadianProvinces.indexOf(abbr) < canadianProvinces.length - 1) {
        console.log(`\nWaiting 5 seconds before next province...`);
        await delay(5000);
      }
    }

    console.log(`\n${'='.repeat(60)}`);
    console.log('CANADA SCRAPING COMPLETED - FINAL SUMMARY');
    console.log(`${'='.repeat(60)}\n`);
    console.log(`Total dispensaries found: ${results.totalFound}`);
    console.log(`Total dispensaries added: ${results.totalAdded}`);
    console.log(`Total dispensaries updated: ${results.totalUpdated}\n`);

    console.log('Province-by-province breakdown:');
    results.provinces.forEach(prov => {
      console.log(`  ${prov.name} (${prov.abbreviation}): ${prov.totalAdded} added, ${prov.totalUpdated} updated`);
    });

    console.log('\n✅ All Canadian provinces/territories have been scraped successfully!');

  } catch (error) {
    console.error('\n❌ Error during scraping:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

// Run the scraper
if (require.main === module) {
  scrapeCanada()
    .then(() => {
      console.log('\nScript finished successfully');
      process.exit(0);
    })
    .catch((err) => {
      console.error('\nScript failed:', err);
      process.exit(1);
    });
}

module.exports = { scrapeCanada };

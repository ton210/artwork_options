const googlePlaces = require('./googlePlaces');
const googleSearch = require('./googleSearch');
const Dispensary = require('../models/Dispensary');
const { County } = require('../models/State');
const db = require('../config/database');

class DispensaryScraper {
  constructor() {
    this.delayMs = 1000; // Delay between API calls
  }

  async scrapeCounty(countyId) {
    try {
      const county = await County.findById(countyId);
      if (!county) {
        throw new Error(`County not found: ${countyId}`);
      }

      console.log(`Scraping dispensaries for ${county.name} County, ${county.state_abbr}...`);

      const logId = await this.createScrapeLog('county', `${county.name}, ${county.state_abbr}`);

      let allResults = [];
      let nextPageToken = null;
      const location = `${county.name} County, ${county.state_abbr}`;

      // Initial search
      const { results, nextPageToken: token } = await googlePlaces.searchDispensaries(location);
      allResults = [...results];
      nextPageToken = token;

      // Get additional pages (up to 60 results total - Google's limit)
      let pageCount = 1;
      while (nextPageToken && pageCount < 3) {
        await this.delay(2000); // Required delay for next_page_token
        const page = await googlePlaces.getNextPage(nextPageToken);
        allResults = [...allResults, ...page.results];
        nextPageToken = page.nextPageToken;
        pageCount++;
      }

      console.log(`  Found ${allResults.length} potential dispensaries`);

      let added = 0;
      let updated = 0;

      for (const place of allResults) {
        // Validate it's a real dispensary
        if (!googlePlaces.isValidDispensary(place)) {
          console.log(`  Skipping non-dispensary: ${place.name}`);
          continue;
        }

        await this.delay(this.delayMs);

        // Get detailed information
        const details = await googlePlaces.getPlaceDetails(place.place_id);
        const addressInfo = googlePlaces.parseAddressComponents(details.address_components);

        // Skip if not in USA
        if (addressInfo.country !== 'United States') {
          continue;
        }

        await this.delay(this.delayMs);

        // Search for external listings
        const externalListings = await googleSearch.searchForListing(
          details.name,
          addressInfo.city,
          addressInfo.state
        );

        // Process photos
        const photos = [];
        if (details.photos && details.photos.length > 0) {
          for (let i = 0; i < Math.min(details.photos.length, 5); i++) {
            photos.push(await googlePlaces.getPhotoUrl(details.photos[i].photo_reference));
          }
        }

        const logoUrl = photos.length > 0 ? photos[0] : null;

        // Prepare dispensary data
        const dispensaryData = {
          google_place_id: details.place_id,
          name: details.name,
          address_street: addressInfo.street,
          city: addressInfo.city,
          county_id: countyId,
          zip: addressInfo.zip,
          lat: details.geometry.location.lat,
          lng: details.geometry.location.lng,
          phone: details.formatted_phone_number || details.international_phone_number,
          website: details.website,
          logo_url: logoUrl,
          photos: photos,
          hours: details.opening_hours,
          google_rating: details.rating,
          google_review_count: details.user_ratings_total || 0,
          external_listings: externalListings,
          data_completeness_score: 0
        };

        // Calculate completeness score
        dispensaryData.data_completeness_score = Dispensary.calculateCompletenessScore(dispensaryData);

        // Save to database (upsert)
        const existing = await db.query(
          'SELECT id FROM dispensaries WHERE google_place_id = $1',
          [details.place_id]
        );

        if (existing.rows.length > 0) {
          await Dispensary.update(existing.rows[0].id, dispensaryData);
          updated++;
          console.log(`  ✓ Updated: ${details.name}`);
        } else {
          await Dispensary.create(dispensaryData);
          added++;
          console.log(`  ✓ Added: ${details.name}`);
        }
      }

      await this.completeScrapeLog(logId, allResults.length, added, updated);

      console.log(`✓ Scraping completed: ${added} added, ${updated} updated`);

      return { found: allResults.length, added, updated };
    } catch (error) {
      console.error('Error scraping county:', error);
      throw error;
    }
  }

  async scrapeState(stateId) {
    try {
      const counties = await db.query(
        'SELECT id, name FROM counties WHERE state_id = $1',
        [stateId]
      );

      console.log(`Scraping ${counties.rows.length} counties in state...`);

      const results = {
        totalFound: 0,
        totalAdded: 0,
        totalUpdated: 0,
        counties: []
      };

      for (const county of counties.rows) {
        const countyResult = await this.scrapeCounty(county.id);
        results.totalFound += countyResult.found;
        results.totalAdded += countyResult.added;
        results.totalUpdated += countyResult.updated;
        results.counties.push({
          name: county.name,
          ...countyResult
        });

        // Delay between counties to be respectful to the API
        await this.delay(2000);
      }

      console.log(`✓ State scraping completed: ${results.totalAdded} added, ${results.totalUpdated} updated`);

      return results;
    } catch (error) {
      console.error('Error scraping state:', error);
      throw error;
    }
  }

  async scrapeAllStates() {
    try {
      const states = await db.query('SELECT id, name FROM states');

      console.log(`Starting full scrape of all ${states.rows.length} states...`);

      const results = {
        totalFound: 0,
        totalAdded: 0,
        totalUpdated: 0,
        states: []
      };

      for (const state of states.rows) {
        console.log(`\n=== Scraping ${state.name} ===`);
        const stateResult = await this.scrapeState(state.id);

        results.totalFound += stateResult.totalFound;
        results.totalAdded += stateResult.totalAdded;
        results.totalUpdated += stateResult.totalUpdated;
        results.states.push({
          name: state.name,
          ...stateResult
        });

        // Delay between states
        await this.delay(5000);
      }

      console.log(`\n✓ Full scrape completed!`);
      console.log(`  Total found: ${results.totalFound}`);
      console.log(`  Total added: ${results.totalAdded}`);
      console.log(`  Total updated: ${results.totalUpdated}`);

      return results;
    } catch (error) {
      console.error('Error in full scrape:', error);
      throw error;
    }
  }

  async createScrapeLog(jobType, location) {
    const result = await db.query(
      `INSERT INTO scrape_logs (job_type, location, started_at)
       VALUES ($1, $2, NOW())
       RETURNING id`,
      [jobType, location]
    );
    return result.rows[0].id;
  }

  async completeScrapeLog(logId, found, added, updated, errors = []) {
    await db.query(
      `UPDATE scrape_logs
       SET dispensaries_found = $1,
           dispensaries_added = $2,
           dispensaries_updated = $3,
           errors = $4,
           completed_at = NOW(),
           status = 'completed'
       WHERE id = $5`,
      [found, added, updated, JSON.stringify(errors), logId]
    );
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

module.exports = new DispensaryScraper();

const express = require('express');
const router = express.Router();
const { State, County } = require('../models/State');
const Dispensary = require('../models/Dispensary');
const Ranking = require('../models/Ranking');
const Vote = require('../models/Vote');
const { getClientIP } = require('../middleware/analytics');
const SchemaGenerator = require('../utils/schemaGenerator');
const { isCurrentlyOpen } = require('../utils/hoursCalculator');
const db = require('../config/database');
const fs = require('fs');
const path = require('path');

// Load state information
const stateInfoPath = path.join(__dirname, '../../data/state-info.json');
const stateInfo = JSON.parse(fs.readFileSync(stateInfoPath, 'utf8'));

// Tag display names mapping
const TAG_DISPLAY_NAMES = {
  'edibles': 'Edibles',
  'flower': 'Flower',
  'vapes': 'Vapes',
  'concentrates': 'Concentrates',
  'pre-rolls': 'Pre-Rolls',
  'tinctures': 'Tinctures',
  'topicals': 'Topicals',
  'delivery': 'Delivery',
  'curbside-pickup': 'Curbside Pickup',
  'recreational': 'Recreational',
  'medical': 'Medical',
  'online-ordering': 'Online Ordering',
  'accessories': 'Accessories',
  'cbd': 'CBD'
};

// Minimum dispensaries required for a tag page
const MIN_DISPENSARIES_FOR_TAG_PAGE = 3;

// Individual dispensary detail page (check first if it has hyphens - dispensary slugs always have hyphens)
// Note: This route must check if the slug is a state first (e.g., new-mexico, new-york) and pass to next handler
router.get('/:slug([a-z0-9]+-[a-z0-9-]+)', async (req, res, next) => {
  try {
    // First check if this slug matches a state (e.g., new-mexico, new-jersey, new-york, rhode-island)
    const state = await State.findBySlug(req.params.slug);
    if (state) {
      return next(); // Let the state route handler below handle this
    }

    const dispensary = await Dispensary.findBySlug(req.params.slug);

    if (!dispensary) {
      return res.status(404).render('404', {
        title: 'Dispensary Not Found',
        baseUrl: process.env.BASE_URL || 'http://localhost:3000'
      });
    }

    // Get vote counts
    const votes = await Vote.getVoteCounts(dispensary.id);
    const recentVotes = await Vote.getRecentVotes(dispensary.id, 30);

    // Check if user can vote
    const clientIP = getClientIP(req);
    const canVote = await Vote.canVote(dispensary.id, clientIP);

    // Get rankings for this dispensary (county and state)
    const rankingsResult = await db.query(`
      SELECT r.location_type, r.rank,
             CASE WHEN r.location_type = 'county' THEN c.name
                  WHEN r.location_type = 'state' THEN s.name END as location_name,
             CASE WHEN r.location_type = 'county' THEN (SELECT COUNT(*) FROM dispensaries d2 WHERE d2.county_id = r.location_id AND d2.is_active = true)
                  WHEN r.location_type = 'state' THEN (SELECT COUNT(*) FROM dispensaries d2 JOIN counties c2 ON d2.county_id = c2.id WHERE c2.state_id = r.location_id AND d2.is_active = true) END as total_in_location
      FROM rankings r
      LEFT JOIN counties c ON r.location_type = 'county' AND r.location_id = c.id
      LEFT JOIN states s ON r.location_type = 'state' AND r.location_id = s.id
      WHERE r.dispensary_id = $1
      ORDER BY r.location_type
    `, [dispensary.id]);
    const rankings = rankingsResult.rows;

    // Get tags for this dispensary
    const tagsResult = await db.query(`
      SELECT tag FROM dispensary_tags WHERE dispensary_id = $1 ORDER BY tag
    `, [dispensary.id]);
    const tags = tagsResult.rows.map(t => TAG_DISPLAY_NAMES[t.tag] || t.tag);

    // Calculate if currently open (real-time based on hours)
    let hoursData = dispensary.hours;
    if (typeof hoursData === 'string') {
      try { hoursData = JSON.parse(hoursData); } catch(e) { hoursData = null; }
    }
    const isOpenNow = isCurrentlyOpen(hoursData);

    // Generate schema.org structured data
    const baseUrl = process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com';
    const schemas = {
      business: SchemaGenerator.generateDispensarySchema(dispensary, baseUrl),
      breadcrumb: SchemaGenerator.generateBreadcrumbSchema([
        { name: 'Home', url: '/' },
        { name: dispensary.state_name, url: `/dispensaries/${dispensary.state_slug}` },
        { name: dispensary.city, url: `/dispensaries/${dispensary.state_slug}/${dispensary.county_slug}` },
        { name: dispensary.name, url: null }
      ], baseUrl)
    };

    res.render('dispensary', {
      title: `${dispensary.name} - ${dispensary.city}, ${dispensary.state_abbr} | Cannabis Dispensary`,
      dispensary,
      votes,
      recentVotes,
      canVote,
      rankings,
      tags,
      schemas,
      baseUrl,
      isOpenNow,
      meta: {
        description: `${dispensary.name} in ${dispensary.city}, ${dispensary.state_abbr}. ${dispensary.google_rating ? dispensary.google_rating + ' stars' : ''} ${dispensary.google_review_count ? '(' + dispensary.google_review_count + ' reviews)' : ''}. Address, hours, phone, and reviews.`,
        keywords: `${dispensary.name}, ${dispensary.city} dispensary, cannabis ${dispensary.city}, marijuana dispensary ${dispensary.state_abbr}`
      }
    });
  } catch (error) {
    console.error('Error loading dispensary page:', error);
    res.status(500).send('Error loading dispensary page');
  }
});

// State-level tag rankings page (e.g., /dispensaries/california/best-edibles)
router.get('/:state/best-:tag', async (req, res) => {
  try {
    const { state: stateSlug, tag } = req.params;

    // Validate tag
    if (!TAG_DISPLAY_NAMES[tag]) {
      return res.status(404).render('404', {
        title: 'Page Not Found',
        message: 'The category you are looking for does not exist.'
      });
    }

    const state = await State.findBySlug(stateSlug);
    if (!state) {
      return res.status(404).render('404', {
        title: 'State Not Found',
        message: 'The state you are looking for does not exist.'
      });
    }

    // Get dispensaries with this tag in this state
    const result = await db.query(`
      SELECT DISTINCT d.*, s.name as state_name, s.slug as state_slug, s.abbreviation as state_abbr,
             c.name as county_name, c.slug as county_slug,
             array_agg(dt2.tag) as tags
      FROM dispensaries d
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      JOIN dispensary_tags dt ON d.id = dt.dispensary_id
      LEFT JOIN dispensary_tags dt2 ON d.id = dt2.dispensary_id
      WHERE c.state_id = $1
        AND dt.tag = $2
        AND d.is_active = true
      GROUP BY d.id, s.name, s.slug, s.abbreviation, c.name, c.slug
      ORDER BY d.google_rating DESC NULLS LAST, d.google_review_count DESC NULLS LAST
      LIMIT 50
    `, [state.id, tag]);

    const dispensaries = result.rows;

    // Check minimum count
    if (dispensaries.length < MIN_DISPENSARIES_FOR_TAG_PAGE) {
      return res.status(404).render('404', {
        title: 'Not Enough Dispensaries',
        message: `There are not enough dispensaries with ${TAG_DISPLAY_NAMES[tag]} in ${state.name} to display rankings.`
      });
    }

    // Calculate average rating
    const ratingsSum = dispensaries.reduce((sum, d) => sum + (parseFloat(d.google_rating) || 0), 0);
    const avgRating = dispensaries.length > 0 ? ratingsSum / dispensaries.length : null;

    // Get other tags available in this state
    const otherTagsResult = await db.query(`
      SELECT dt.tag, COUNT(DISTINCT d.id) as count
      FROM dispensary_tags dt
      JOIN dispensaries d ON dt.dispensary_id = d.id
      JOIN counties c ON d.county_id = c.id
      WHERE c.state_id = $1 AND d.is_active = true AND dt.tag != $2
      GROUP BY dt.tag
      HAVING COUNT(DISTINCT d.id) >= $3
      ORDER BY count DESC
    `, [state.id, tag, MIN_DISPENSARIES_FOR_TAG_PAGE]);

    const otherTags = otherTagsResult.rows
      .filter(t => TAG_DISPLAY_NAMES[t.tag])
      .map(t => ({
        slug: t.tag,
        display: TAG_DISPLAY_NAMES[t.tag],
        count: t.count,
        url: `/dispensaries/${stateSlug}/best-${t.tag}`
      }));

    const tagDisplay = TAG_DISPLAY_NAMES[tag];
    const baseUrl = process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com';
    const canonicalUrl = `/dispensaries/${stateSlug}/best-${tag}`;

    // Generate schema.org structured data
    const schemas = {
      itemList: {
        '@context': 'https://schema.org',
        '@type': 'ItemList',
        'name': `Best ${tagDisplay} Dispensaries in ${state.name}`,
        'description': `Top ${dispensaries.length} ${tagDisplay.toLowerCase()} dispensaries in ${state.name}, ranked by customer reviews`,
        'numberOfItems': dispensaries.length,
        'itemListElement': dispensaries.slice(0, 10).map((d, i) => ({
          '@type': 'ListItem',
          'position': i + 1,
          'item': {
            '@type': 'LocalBusiness',
            'name': d.name,
            'address': {
              '@type': 'PostalAddress',
              'streetAddress': d.address_street,
              'addressLocality': d.city,
              'addressRegion': d.state_abbr
            },
            ...(d.google_rating && {
              'aggregateRating': {
                '@type': 'AggregateRating',
                'ratingValue': d.google_rating,
                'reviewCount': d.google_review_count || 0
              }
            }),
            'url': `${baseUrl}/dispensary/${d.slug}`
          }
        }))
      },
      breadcrumb: SchemaGenerator.generateBreadcrumbSchema([
        { name: 'Home', url: '/' },
        { name: state.name, url: `/dispensaries/${stateSlug}` },
        { name: `Best ${tagDisplay}`, url: null }
      ], baseUrl)
    };

    res.render('tag-rankings', {
      title: `Best ${tagDisplay} Dispensaries in ${state.name} (2026) | Top ${dispensaries.length} Ranked`,
      dispensaries,
      tagDisplay,
      tag,
      avgRating,
      otherTags,
      location: {
        stateName: state.name,
        stateSlug: stateSlug,
        countyName: null,
        countySlug: null
      },
      schemas,
      baseUrl,
      canonicalUrl,
      meta: {
        description: `Find the best ${tagDisplay.toLowerCase()} dispensaries in ${state.name}. Top ${dispensaries.length} ranked by customer reviews, ratings, and quality. Updated 2026.`,
        keywords: `best ${tagDisplay.toLowerCase()} dispensaries ${state.name}, ${tagDisplay.toLowerCase()} ${state.name}, cannabis ${tagDisplay.toLowerCase()} ${state.name}, ${state.name} dispensary ${tagDisplay.toLowerCase()}`
      }
    });

  } catch (error) {
    console.error('Error loading tag rankings page:', error);
    res.status(500).send('Error loading page');
  }
});

// County-level tag rankings page (e.g., /dispensaries/california/los-angeles-county/best-edibles)
router.get('/:state/:county/best-:tag', async (req, res) => {
  try {
    const { state: stateSlug, county: countySlug, tag } = req.params;

    // Validate tag
    if (!TAG_DISPLAY_NAMES[tag]) {
      return res.status(404).render('404', {
        title: 'Page Not Found',
        message: 'The category you are looking for does not exist.'
      });
    }

    const county = await County.findBySlug(stateSlug, countySlug);
    if (!county) {
      return res.status(404).render('404', {
        title: 'County Not Found',
        message: 'The county you are looking for does not exist.'
      });
    }

    // Get dispensaries with this tag in this county
    const result = await db.query(`
      SELECT DISTINCT d.*, s.name as state_name, s.slug as state_slug, s.abbreviation as state_abbr,
             c.name as county_name, c.slug as county_slug,
             array_agg(dt2.tag) as tags
      FROM dispensaries d
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      JOIN dispensary_tags dt ON d.id = dt.dispensary_id
      LEFT JOIN dispensary_tags dt2 ON d.id = dt2.dispensary_id
      WHERE d.county_id = $1
        AND dt.tag = $2
        AND d.is_active = true
      GROUP BY d.id, s.name, s.slug, s.abbreviation, c.name, c.slug
      ORDER BY d.google_rating DESC NULLS LAST, d.google_review_count DESC NULLS LAST
      LIMIT 50
    `, [county.id, tag]);

    const dispensaries = result.rows;

    // Check minimum count
    if (dispensaries.length < MIN_DISPENSARIES_FOR_TAG_PAGE) {
      return res.status(404).render('404', {
        title: 'Not Enough Dispensaries',
        message: `There are not enough dispensaries with ${TAG_DISPLAY_NAMES[tag]} in ${county.name} County to display rankings.`
      });
    }

    // Calculate average rating
    const ratingsSum = dispensaries.reduce((sum, d) => sum + (parseFloat(d.google_rating) || 0), 0);
    const avgRating = dispensaries.length > 0 ? ratingsSum / dispensaries.length : null;

    // Get other tags available in this county
    const otherTagsResult = await db.query(`
      SELECT dt.tag, COUNT(DISTINCT d.id) as count
      FROM dispensary_tags dt
      JOIN dispensaries d ON dt.dispensary_id = d.id
      WHERE d.county_id = $1 AND d.is_active = true AND dt.tag != $2
      GROUP BY dt.tag
      HAVING COUNT(DISTINCT d.id) >= $3
      ORDER BY count DESC
    `, [county.id, tag, MIN_DISPENSARIES_FOR_TAG_PAGE]);

    const otherTags = otherTagsResult.rows
      .filter(t => TAG_DISPLAY_NAMES[t.tag])
      .map(t => ({
        slug: t.tag,
        display: TAG_DISPLAY_NAMES[t.tag],
        count: t.count,
        url: `/dispensaries/${stateSlug}/${countySlug}/best-${t.tag}`
      }));

    const tagDisplay = TAG_DISPLAY_NAMES[tag];
    const baseUrl = process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com';
    const canonicalUrl = `/dispensaries/${stateSlug}/${countySlug}/best-${tag}`;

    // Generate schema.org structured data
    const schemas = {
      itemList: {
        '@context': 'https://schema.org',
        '@type': 'ItemList',
        'name': `Best ${tagDisplay} Dispensaries in ${county.name} County, ${county.state_name}`,
        'description': `Top ${dispensaries.length} ${tagDisplay.toLowerCase()} dispensaries in ${county.name} County, ${county.state_name}`,
        'numberOfItems': dispensaries.length,
        'itemListElement': dispensaries.slice(0, 10).map((d, i) => ({
          '@type': 'ListItem',
          'position': i + 1,
          'item': {
            '@type': 'LocalBusiness',
            'name': d.name,
            'address': {
              '@type': 'PostalAddress',
              'streetAddress': d.address_street,
              'addressLocality': d.city,
              'addressRegion': d.state_abbr
            },
            ...(d.google_rating && {
              'aggregateRating': {
                '@type': 'AggregateRating',
                'ratingValue': d.google_rating,
                'reviewCount': d.google_review_count || 0
              }
            }),
            'url': `${baseUrl}/dispensary/${d.slug}`
          }
        }))
      },
      breadcrumb: SchemaGenerator.generateBreadcrumbSchema([
        { name: 'Home', url: '/' },
        { name: county.state_name, url: `/dispensaries/${stateSlug}` },
        { name: `${county.name} County`, url: `/dispensaries/${stateSlug}/${countySlug}` },
        { name: `Best ${tagDisplay}`, url: null }
      ], baseUrl)
    };

    res.render('tag-rankings', {
      title: `Best ${tagDisplay} Dispensaries in ${county.name} County, ${county.state_abbr} (2026)`,
      dispensaries,
      tagDisplay,
      tag,
      avgRating,
      otherTags,
      location: {
        stateName: county.state_name,
        stateSlug: stateSlug,
        countyName: county.name,
        countySlug: countySlug
      },
      schemas,
      baseUrl,
      canonicalUrl,
      meta: {
        description: `Find the best ${tagDisplay.toLowerCase()} dispensaries in ${county.name} County, ${county.state_name}. Top ${dispensaries.length} ranked by customer reviews and ratings.`,
        keywords: `best ${tagDisplay.toLowerCase()} dispensaries ${county.name} County, ${tagDisplay.toLowerCase()} ${county.name}, cannabis ${tagDisplay.toLowerCase()} ${county.state_abbr}`
      }
    });

  } catch (error) {
    console.error('Error loading county tag rankings page:', error);
    res.status(500).send('Error loading page');
  }
});

// State rankings page
router.get('/:state', async (req, res) => {
  try {
    const state = await State.findBySlug(req.params.state);

    if (!state) {
      return res.status(404).render('404', {
        title: 'State Not Found',
        message: 'The state you are looking for does not exist or cannabis is not legal there.'
      });
    }

    // Get counties with dispensary counts
    const counties = await State.getCounties(state.id);

    // Check if user wants to see all or just top 10
    const showAll = req.query.view === 'all';
    const limit = showAll ? 1000 : 10;

    // Get dispensaries for state
    const rankings = await Ranking.getByLocation('state', state.id, limit);

    // Get vote counts for each dispensary
    for (const ranking of rankings) {
      ranking.votes = await Vote.getVoteCounts(ranking.dispensary_id);
      ranking.recentVotes = await Vote.getRecentVotes(ranking.dispensary_id, 7);

      // Check if user can vote
      const clientIP = getClientIP(req);
      ranking.canVote = await Vote.canVote(ranking.dispensary_id, clientIP);
    }

    // Get stats
    const stats = await State.getStats(state.id);

    // Get state-specific cannabis information
    const stateDetails = stateInfo[state.name] || null;

    res.render('state', {
      title: showAll ?
        `All ${rankings.length} Dispensaries in ${state.name} (2026) | Complete Rankings` :
        `Top 10 Dispensaries in ${state.name} (2026) | Cannabis Dispensary Rankings`,
      state,
      counties,
      rankings,
      stats,
      showAll,
      stateDetails,
      MUNCHMAKERS_URL: process.env.MUNCHMAKERS_URL || 'https://munchmakers.com',
      meta: {
        description: `Find the top-rated cannabis dispensaries in ${state.name}. User-voted rankings based on Google reviews, ratings, and community feedback.`,
        keywords: `${state.name} dispensary, cannabis ${state.name}, marijuana dispensary ${state.name}, weed dispensary ${state.name}`
      }
    });
  } catch (error) {
    console.error('Error loading state page:', error);
    res.status(500).send('Error loading state page');
  }
});

// City rankings page (e.g., /dispensaries/california/city/los-angeles)
router.get('/:state/city/:city', async (req, res) => {
  try {
    const { state: stateSlug, city: citySlug } = req.params;

    // Get state
    const state = await State.findBySlug(stateSlug);
    if (!state) {
      return res.status(404).render('404', {
        title: 'State Not Found',
        message: 'The state you are looking for does not exist.'
      });
    }

    // Convert slug back to city name (e.g., "los-angeles" -> "Los Angeles")
    const cityName = citySlug
      .split('-')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');

    // Get dispensaries in this city
    const result = await db.query(`
      SELECT d.*, c.name as county_name, c.slug as county_slug,
             s.name as state_name, s.slug as state_slug, s.abbreviation as state_abbr,
             COALESCE(d.google_rating, 0) as google_rating,
             COALESCE(d.google_review_count, 0) as google_review_count
      FROM dispensaries d
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      WHERE LOWER(d.city) = LOWER($1)
        AND s.id = $2
        AND d.is_active = true
      ORDER BY d.google_review_count DESC, d.google_rating DESC
      LIMIT 100
    `, [cityName, state.id]);

    if (result.rows.length === 0) {
      return res.status(404).render('404', {
        title: 'City Not Found',
        message: `No dispensaries found in ${cityName}, ${state.name}.`
      });
    }

    const dispensaries = result.rows;
    const baseUrl = process.env.BASE_URL || 'http://localhost:3000';
    const canonicalUrl = `${baseUrl}/dispensaries/${stateSlug}/city/${citySlug}`;

    // Calculate average rating
    const avgRating = dispensaries.length > 0
      ? (dispensaries.reduce((sum, d) => sum + (d.google_rating || 0), 0) / dispensaries.length).toFixed(1)
      : 0;

    // Get other cities in this state
    const otherCitiesResult = await db.query(`
      SELECT DISTINCT d.city, COUNT(*) as count
      FROM dispensaries d
      JOIN counties c ON d.county_id = c.id
      WHERE c.state_id = $1
        AND d.is_active = true
        AND d.city IS NOT NULL
        AND d.city <> ''
        AND LOWER(d.city) <> LOWER($2)
      GROUP BY d.city
      HAVING COUNT(*) >= 3
      ORDER BY COUNT(*) DESC
      LIMIT 10
    `, [state.id, cityName]);

    // Generate schema
    const schemas = {
      itemList: {
        '@context': 'https://schema.org',
        '@type': 'ItemList',
        'name': `Best Dispensaries in ${cityName}, ${state.abbreviation}`,
        'numberOfItems': dispensaries.length,
        'itemListElement': dispensaries.slice(0, 10).map((d, i) => ({
          '@type': 'ListItem',
          'position': i + 1,
          'item': {
            '@type': 'LocalBusiness',
            'name': d.name,
            'address': {
              '@type': 'PostalAddress',
              'streetAddress': d.address_street,
              'addressLocality': d.city,
              'addressRegion': d.state_abbr
            },
            ...(d.google_rating && {
              'aggregateRating': {
                '@type': 'AggregateRating',
                'ratingValue': d.google_rating,
                'reviewCount': d.google_review_count || 0
              }
            }),
            'url': `${baseUrl}/dispensary/${d.slug}`
          }
        }))
      },
      breadcrumb: SchemaGenerator.generateBreadcrumbSchema([
        { name: 'Home', url: '/' },
        { name: state.name, url: `/dispensaries/${stateSlug}` },
        { name: cityName, url: null }
      ], baseUrl)
    };

    res.render('city-rankings', {
      title: `Best Dispensaries in ${cityName}, ${state.abbreviation} (2026) | Top ${dispensaries.length} Ranked`,
      dispensaries,
      cityName,
      citySlug,
      state,
      avgRating,
      otherCities: otherCitiesResult.rows,
      schemas,
      baseUrl,
      canonicalUrl,
      meta: {
        description: `Find the best cannabis dispensaries in ${cityName}, ${state.name}. Top ${dispensaries.length} dispensaries ranked by customer reviews, ratings, and quality. Updated 2026.`,
        keywords: `dispensaries ${cityName}, cannabis dispensary ${cityName} ${state.abbreviation}, weed dispensary ${cityName}, marijuana ${cityName}`
      }
    });

  } catch (error) {
    console.error('Error loading city page:', error);
    res.status(500).send('Error loading city page');
  }
});

// County rankings page
router.get('/:state/:county', async (req, res) => {
  try {
    const county = await County.findBySlug(req.params.state, req.params.county);

    if (!county) {
      return res.status(404).render('404', {
        title: 'County Not Found',
        message: 'The county you are looking for does not exist.'
      });
    }

    // Check if user wants to see all or just top results
    const showAll = req.query.view === 'all';
    const limit = showAll ? 1000 : 50;

    // Get top dispensaries for county
    const rankings = await Ranking.getByLocation('county', county.id, limit);

    // Get vote counts for each dispensary
    for (const ranking of rankings) {
      ranking.votes = await Vote.getVoteCounts(ranking.dispensary_id);
      ranking.recentVotes = await Vote.getRecentVotes(ranking.dispensary_id, 7);

      // Check if user can vote
      const clientIP = getClientIP(req);
      ranking.canVote = await Vote.canVote(ranking.dispensary_id, clientIP);
    }

    // Get stats
    const stats = await County.getStats(county.id);

    // Get other counties in state
    const otherCounties = await State.getCounties(county.state_id);

    // Filter out current county and pass as nearby
    const nearbyCounties = otherCounties.filter(c => c.id !== county.id);

    res.render('county', {
      title: showAll ?
        `All ${rankings.length} Dispensaries in ${county.name} County, ${county.state_abbr}` :
        `Top Dispensaries in ${county.name} County, ${county.state_abbr} (2026)`,
      county,
      rankings,
      stats,
      dispensaries: rankings,
      otherCounties,
      nearbyCounties,
      showAll,
      mapEnabled: true,
      GOOGLE_API_KEY: process.env.GOOGLE_PLACES_API_KEY,
      baseUrl: process.env.BASE_URL || 'http://localhost:3000',
      meta: {
        description: `Browse the best cannabis dispensaries in ${county.name} County, ${county.state_name}. Rankings based on user votes, Google reviews, and ratings.`,
        keywords: `${county.name} County dispensary, cannabis ${county.name} County, marijuana dispensary ${county.state_abbr}`
      }
    });
  } catch (error) {
    console.error('Error loading county page:', error);
    res.status(500).send('Error loading county page');
  }
});

module.exports = router;
 

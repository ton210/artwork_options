const express = require('express');
const router = express.Router();
const { State, County } = require('../models/State');
const Dispensary = require('../models/Dispensary');
const Ranking = require('../models/Ranking');
const Vote = require('../models/Vote');
const { getClientIP } = require('../middleware/analytics');

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

    res.render('state', {
      title: showAll ?
        `All ${rankings.length} Dispensaries in ${state.name} (2026) | Complete Rankings` :
        `Top 10 Dispensaries in ${state.name} (2026) | Cannabis Dispensary Rankings`,
      state,
      counties,
      rankings,
      stats,
      showAll,
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

    res.render('county', {
      title: showAll ?
        `All ${rankings.length} Dispensaries in ${county.name} County, ${county.state_abbr}` :
        `Top Dispensaries in ${county.name} County, ${county.state_abbr} (2026)`,
      county,
      rankings,
      stats,
      otherCounties,
      showAll,
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

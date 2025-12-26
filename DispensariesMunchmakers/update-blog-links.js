require('dotenv').config();
const db = require('./src/config/database');

// Internal link suggestions for each blog post
const linkUpdates = {
  'how-to-choose-the-right-cannabis-dispensary-for-your-needs': {
    additions: [
      { find: 'check online reviews', replace: 'check online reviews on our <a href="/" class="text-green-600 hover:underline">dispensary rankings</a>' },
      { find: 'Finding the perfect dispensary', replace: 'Finding the perfect dispensary (use our <a href="/near-me" class="text-green-600 hover:underline">Near Me tool</a>)' }
    ]
  },
  'cannabis-dispensary-etiquette-dos-and-donts-for-first-time-visitors': {
    additions: [
      { find: 'walking into a cannabis dispensary', replace: 'walking into a cannabis dispensary (find one <a href="/" class="text-green-600 hover:underline">near you</a>)' },
      { find: 'budtenders are there to help', replace: 'budtenders are there to help (read our <a href="/blog/top-10-questions-to-ask-your-budtender" class="text-green-600 hover:underline">budtender questions guide</a>)' }
    ]
  },
  'understanding-cannabis-strains-indica-vs-sativa-vs-hybrid': {
    additions: [
      { find: 'Ask your budtender', replace: 'Ask your budtender (find <a href="/" class="text-green-600 hover:underline">top-rated dispensaries</a> here)' },
      { find: 'Use our <a href="/tools" class="text-green-600 hover:underline">Strain Finder tool</a>', replace: 'Use our <a href="/tools" class="text-green-600 hover:underline">Strain Finder tool</a>' }
    ]
  }
};

async function updateBlogLinks() {
  console.log('Adding internal links to blog posts...\n');

  try {
    // Add generic links to all posts that don't have them
    const genericLinks = `
      <div class="bg-green-50 border-l-4 border-green-600 p-4 my-6">
        <p class="font-semibold mb-2">🔍 Find Top Dispensaries:</p>
        <p>Use our <a href="/" class="text-green-600 hover:underline font-medium">dispensary rankings</a> to find the best cannabis shops in your area, or try our <a href="/tools" class="text-green-600 hover:underline font-medium">interactive tools</a> to find your perfect strain and calculate dosages.</p>
      </div>
    `;

    const result = await db.query('SELECT id, slug, content FROM blog_posts');

    for (const post of result.rows) {
      let updatedContent = post.content;

      // Add generic CTA if not present
      if (!updatedContent.includes('Find Top Dispensaries')) {
        // Insert before last closing tag
        updatedContent = updatedContent.trim() + genericLinks;
      }

      // Add specific links if defined
      if (linkUpdates[post.slug]) {
        linkUpdates[post.slug].additions.forEach(link => {
          if (!updatedContent.includes(link.replace)) {
            updatedContent = updatedContent.replace(link.find, link.replace);
          }
        });
      }

      await db.query(
        'UPDATE blog_posts SET content = $1 WHERE id = $2',
        [updatedContent, post.id]
      );

      console.log(`✓ Updated: ${post.slug}`);
    }

    console.log(`\n✓ Successfully added internal links to ${result.rows.length} blog posts!`);

  } catch (error) {
    console.error('Error updating blog links:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  updateBlogLinks()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { updateBlogLinks };

require('dotenv').config();
const BlogPost = require('./src/models/BlogPost');
const db = require('./src/config/database');

const statePosts = [
  {
    title: "Best Dispensaries in California 2026: Complete State Guide",
    excerpt: "California has the largest cannabis market in America. Discover the top-rated dispensaries across the Golden State, from LA to San Francisco to San Diego.",
    category: "State Guides",
    tags: ["california", "los angeles", "san francisco", "state guide"],
    content: `
      <p>California pioneered legal cannabis and remains the largest market in the United States. With thousands of dispensaries across the state, finding the right one can be overwhelming.</p>

      <h2>Why California Leads the Cannabis Industry</h2>
      <p>As the first state to legalize medical marijuana (1996) and one of the first for recreational (2016), California has:</p>
      <ul>
        <li>The most dispensaries of any state</li>
        <li>Highest quality standards and testing requirements</li>
        <li>Competitive pricing due to market saturation</li>
        <li>Widest product selection</li>
        <li>Innovation in products and delivery methods</li>
      </ul>

      <h2>Top Cannabis Regions in California</h2>

      <h3>Los Angeles & Southern California</h3>
      <p>LA has the highest concentration of dispensaries in the world. Browse our <a href="/dispensaries/california" class="text-green-600 hover:underline font-medium">California dispensary rankings</a> to find top-rated shops in:</p>
      <ul>
        <li>Los Angeles (Hollywood, Venice Beach, Downtown)</li>
        <li>San Diego (Gaslamp, Pacific Beach)</li>
        <li>Orange County (Santa Ana, Anaheim)</li>
        <li>Inland Empire (Riverside, San Bernardino)</li>
      </ul>

      <h3>San Francisco Bay Area</h3>
      <p>The Bay Area offers premium dispensaries with cutting-edge products:</p>
      <ul>
        <li>San Francisco (Mission, SOMA, Haight-Ashbury)</li>
        <li>Oakland (cannabis culture hub)</li>
        <li>San Jose (South Bay's largest market)</li>
        <li>Berkeley (progressive cannabis scene)</li>
      </ul>

      <h3>Northern California</h3>
      <p>The Emerald Triangle and beyond:</p>
      <ul>
        <li>Sacramento (state capital, growing market)</li>
        <li>Humboldt County (legendary cannabis region)</li>
        <li>Mendocino County (craft cannabis)</li>
      </ul>

      <h2>What Makes California Dispensaries Unique</h2>
      <p><strong>Strict Testing:</strong> All products must be lab-tested for potency, pesticides, and contaminants.</p>
      <p><strong>Competitive Prices:</strong> High competition means better deals for consumers.</p>
      <p><strong>Product Variety:</strong> From budget flower to premium concentrates, California has it all.</p>
      <p><strong>Delivery Options:</strong> Many dispensaries offer same-day delivery throughout metro areas.</p>

      <h2>California Cannabis Laws</h2>
      <ul>
        <li><strong>Recreational:</strong> 21+ with valid ID</li>
        <li><strong>Medical:</strong> Available with medical card (lower taxes)</li>
        <li><strong>Purchase Limits:</strong> 28.5g flower, 8g concentrates per day</li>
        <li><strong>Consumption:</strong> Private property only (not in public)</li>
        <li><strong>Tax:</strong> ~25-35% depending on locality</li>
      </ul>

      <h2>Find California's Best Dispensaries</h2>
      <p>Use our <a href="/dispensaries/california" class="text-green-600 hover:underline font-medium">California dispensary rankings</a> to find top-rated shops based on community votes, Google reviews, and user feedback. Filter by city, product type, or services like delivery and online ordering.</p>

      <div class="bg-green-50 border-l-4 border-green-600 p-6 my-6">
        <p class="font-semibold mb-2">🔍 Quick Links:</p>
        <p><a href="/dispensaries/california" class="text-green-600 hover:underline font-medium">Browse All California Dispensaries →</a></p>
        <p><a href="/tools" class="text-green-600 hover:underline font-medium">Use Our Strain Finder Tool →</a></p>
        <p><a href="/faq/california" class="text-green-600 hover:underline font-medium">California Dispensary FAQ →</a></p>
      </div>

      <h2>Tips for California Dispensary Shopping</h2>
      <ul>
        <li>Check for first-time customer discounts (usually 10-20% off)</li>
        <li>Join loyalty programs - they're worth it in California's competitive market</li>
        <li>Compare prices between 2-3 nearby dispensaries</li>
        <li>Look for daily specials (Wax Wednesday, Flower Friday, etc.)</li>
        <li>Read reviews on our site before visiting</li>
        <li>Bring cash - many still don't accept cards</li>
      </ul>

      <p><strong>Start exploring:</strong> Check our <a href="/dispensaries/california" class="text-green-600 hover:underline font-medium">California dispensary listings</a> to find the perfect shop for your needs!</p>
    `
  },
  {
    title: "Colorado Dispensaries Guide 2026: Denver, Boulder & Beyond",
    excerpt: "Colorado was the first state to legalize recreational cannabis. Explore the best dispensaries in Denver, Boulder, Colorado Springs, and across the Rockies.",
    category: "State Guides",
    tags: ["colorado", "denver", "boulder", "state guide"],
    content: `
      <p>Colorado made history as the first state to implement legal recreational cannabis sales in 2014. Today, it boasts one of the most mature and sophisticated cannabis markets in the world.</p>

      <h2>Colorado's Cannabis Landscape</h2>
      <p>From Denver's urban dispensaries to mountain town shops, Colorado offers diverse cannabis experiences. Browse our <a href="/dispensaries/colorado" class="text-green-600 hover:underline font-medium">Colorado dispensary rankings</a> to find the best options.</p>

      <h3>Denver Metro Area</h3>
      <p>The Mile High City lives up to its name with hundreds of dispensaries:</p>
      <ul>
        <li>Downtown Denver (tourist-friendly, premium shops)</li>
        <li>Capitol Hill (trendy, local favorites)</li>
        <li>RiNo/Five Points (artsy dispensaries)</li>
        <li>South Denver (suburban convenience)</li>
      </ul>

      <h3>Boulder & Northern Colorado</h3>
      <p>Boulder offers boutique cannabis experiences:</p>
        <ul>
        <li>Pearl Street dispensaries (upscale, tourist-friendly)</li>
        <li>University Hill (student-oriented, budget options)</li>
        <li>Fort Collins (Northern Colorado hub)</li>
      </ul>

      <h3>Mountain Towns</h3>
      <p>Ski resort dispensaries for tourists:</p>
      <ul>
        <li>Breckenridge (high-altitude cannabis)</li>
        <li>Aspen (luxury dispensaries)</li>
        <li>Vail (convenient for tourists)</li>
        <li>Telluride (boutique selections)</li>
      </ul>

      <h2>Colorado Cannabis Laws</h2>
      <ul>
        <li><strong>Recreational:</strong> 21+ with valid ID (including out-of-state IDs)</li>
        <li><strong>Medical:</strong> Medical cardholders get higher limits and lower taxes</li>
        <li><strong>Purchase Limits:</strong> 28g flower for recreational, 56g for medical</li>
        <li><strong>Tax:</strong> ~20-25% total (lower than California)</li>
        <li><strong>Public Consumption:</strong> Illegal, but some lounges exist</li>
      </ul>

      <h2>What Makes Colorado Dispensaries Special</h2>
      <p><strong>Experience & Maturity:</strong> Colorado dispensaries have 10+ years of refinement.</p>
      <p><strong>Tourist-Friendly:</strong> Many cater specifically to visitors with guides and recommendations.</p>
      <p><strong>Quality Standards:</strong> Mandatory testing ensures safety and accuracy.</p>
      <p><strong>Competitive Pricing:</strong> Mature market means fair prices.</p>

      <h2>Best Dispensaries by Category</h2>
      <p>Looking for something specific? Use our <a href="/tools" class="text-green-600 hover:underline font-medium">Strain Finder tool</a> or browse by category:</p>
      <ul>
        <li>Best for tourists (convenient locations, friendly staff)</li>
        <li>Best prices (budget-friendly options)</li>
        <li>Best selection (widest product variety)</li>
        <li>Best for medical patients</li>
      </ul>

      <div class="bg-blue-50 border-l-4 border-blue-600 p-6 my-6">
        <p class="font-semibold mb-2">🏔️ Visiting Colorado?</p>
        <p>Check our <a href="/dispensaries/colorado" class="text-green-600 hover:underline font-medium">Colorado dispensary rankings</a> before your trip. Find shops near your hotel, compare prices, and read real reviews from locals and tourists.</p>
      </div>

      <h2>Pro Tips for Colorado Dispensaries</h2>
      <ul>
        <li>Altitude affects cannabis - start with lower doses than usual</li>
        <li>Don't consume and drive - DUI laws are strict</li>
        <li>Keep purchases in original packaging when traveling</li>
        <li>Can't take cannabis across state lines (even to legal states)</li>
        <li>Many dispensaries offer veteran and senior discounts</li>
      </ul>

      <p>Ready to explore? Visit our <a href="/dispensaries/colorado" class="text-green-600 hover:underline font-medium">Colorado dispensary listings</a> and <a href="/faq/colorado" class="text-green-600 hover:underline font-medium">Colorado FAQ page</a> for more information!</p>
    `
  }
];

async function seedStatePosts() {
  console.log('Creating state-specific blog posts...\n');

  try {
    for (const post of statePosts) {
      const created = await BlogPost.create(post);
      console.log(`✓ Created: "${created.title}"`);
    }

    console.log(`\n✓ Successfully created ${statePosts.length} state-specific blog posts!`);

  } catch (error) {
    console.error('Error seeding state posts:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  seedStatePosts()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { seedStatePosts };

require('dotenv').config();
const db = require('../config/database');

const imageMapping = {
  'how-to-choose-the-right-cannabis-dispensary-for-your-needs': '/images/blog/choosing-dispensary.jpg',
  'cannabis-dispensary-etiquette-dos-and-donts-for-first-time-visitors': '/images/blog/dispensary-etiquette.jpg',
  'understanding-cannabis-strains-indica-vs-sativa-vs-hybrid': '/images/blog/strains-guide.jpg',
  'cannabis-edibles-guide-dosing-effects-and-safety': '/images/blog/edibles-guide.jpg',
  'understanding-thc-and-cbd-the-key-cannabinoids-explained': '/images/blog/thc-cbd-explained.jpg',
  'cannabis-concentrates-101-wax-shatter-live-resin-and-more': '/images/blog/concentrates-guide.jpg',
  'the-complete-guide-to-cannabis-vaping-for-beginners': '/images/blog/vaping-guide.jpg',
  'cannabis-storage-guide-keep-your-products-fresh-longer': '/images/blog/storage-guide.jpg',
  'the-best-cannabis-accessories-every-user-needs': '/images/blog/accessories-guide.jpg',
  'medical-vs-recreational-dispensaries-whats-the-difference': '/images/blog/guides-category.jpg',
  'cannabis-delivery-vs-in-store-shopping-pros-and-cons': '/images/blog/guides-category.jpg',
  'how-to-save-money-at-cannabis-dispensaries': '/images/blog/tips-category.jpg',
  'top-10-questions-to-ask-your-budtender': '/images/blog/guides-category.jpg',
  'what-to-expect-on-your-first-dispensary-visit': '/images/blog/guides-category.jpg',
  'terpenes-explained-why-cannabis-smells-and-effects-matter': '/images/blog/education-category.jpg',
  '10-red-flags-when-choosing-a-cannabis-dispensary': '/images/blog/tips-category.jpg',
  'cannabis-and-drug-testing-what-you-need-to-know': '/images/blog/education-category.jpg'
};

async function updateBlogImages() {
  console.log('Updating blog post images...\n');

  try {
    for (const [slug, imagePath] of Object.entries(imageMapping)) {
      const result = await db.query(
        'UPDATE blog_posts SET featured_image = $1 WHERE slug = $2 RETURNING title',
        [imagePath, slug]
      );

      if (result.rows.length > 0) {
        console.log(`✓ Updated: ${result.rows[0].title}`);
      }
    }

    console.log(`\n=== Successfully updated ${Object.keys(imageMapping).length} blog posts with images! ===`);

  } catch (error) {
    console.error('Error updating blog images:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  updateBlogImages()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { updateBlogImages };

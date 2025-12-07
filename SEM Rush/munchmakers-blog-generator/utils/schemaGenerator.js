/**
 * Generate Schema.org structured data for SEO and LLM discovery
 */

/**
 * Generate Article schema for blog posts
 */
function generateArticleSchema(blogData, imageUrls) {
  return {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": blogData.title,
    "description": blogData.metaDescription,
    "image": [
      imageUrls.featured,
      imageUrls['inline-1'],
      imageUrls['inline-2']
    ],
    "datePublished": new Date().toISOString(),
    "dateModified": new Date().toISOString(),
    "author": {
      "@type": "Organization",
      "name": "MunchMakers",
      "url": "https://munchmakers.com"
    },
    "publisher": {
      "@type": "Organization",
      "name": "MunchMakers",
      "logo": {
        "@type": "ImageObject",
        "url": "https://munchmakers.com/logo.png"
      },
      "url": "https://munchmakers.com"
    },
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": `https://munchmakers.com/blog/${blogData.slug}/`
    }
  };
}

/**
 * Generate Breadcrumb schema
 */
function generateBreadcrumbSchema(blogData) {
  return {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://munchmakers.com"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Blog",
        "item": "https://munchmakers.com/blog/"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": blogData.title,
        "item": `https://munchmakers.com/blog/${blogData.slug}/`
      }
    ]
  };
}

/**
 * Generate Organization schema for brand
 */
function generateOrganizationSchema() {
  return {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "MunchMakers",
    "url": "https://munchmakers.com",
    "logo": "https://munchmakers.com/logo.png",
    "description": "Wholesale custom cannabis accessories manufacturer for dispensaries and smoke shops",
    "sameAs": [
      "https://www.facebook.com/munchmakers",
      "https://www.instagram.com/munchmakers"
    ]
  };
}

/**
 * Extract FAQ data from content and generate FAQ schema
 */
function generateFAQSchema(htmlContent) {
  // Extract Q&A patterns from HTML
  const faqPattern = /<p><strong>Q\d+:\s*([^<]+)<\/strong><br>\s*A\d+:\s*([^<]+)<\/p>/g;
  const matches = [...htmlContent.matchAll(faqPattern)];

  if (matches.length === 0) {
    return null;
  }

  const mainEntity = matches.map(match => ({
    "@type": "Question",
    "name": match[1].trim(),
    "acceptedAnswer": {
      "@type": "Answer",
      "text": match[2].trim()
    }
  }));

  return {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": mainEntity
  };
}

/**
 * Generate all schema markup for a blog post
 * NOTE: Only generates Article and FAQ schemas
 * Breadcrumb and Organization schemas already exist site-wide
 */
function generateAllSchemas(blogData, imageUrls, htmlContent) {
  const schemas = [];

  // Always add Article schema (content-specific)
  schemas.push(generateArticleSchema(blogData, imageUrls));

  // Add FAQ schema if FAQs detected (content-specific)
  const faqSchema = generateFAQSchema(htmlContent);
  if (faqSchema) {
    schemas.push(faqSchema);
  }

  return schemas;
}

/**
 * Convert schema objects to HTML script tags
 */
function schemasToHtml(schemas) {
  return schemas.map(schema =>
    `<script type="application/ld+json">\n${JSON.stringify(schema, null, 2)}\n</script>`
  ).join('\n\n');
}

module.exports = {
  generateArticleSchema,
  generateBreadcrumbSchema,
  generateOrganizationSchema,
  generateFAQSchema,
  generateAllSchemas,
  schemasToHtml
};

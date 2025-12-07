/**
 * Schema.org JSON-LD Generator
 * Creates structured data for SEO and rich snippets
 */

class SchemaGenerator {
  /**
   * Generate LocalBusiness schema for dispensary
   */
  static generateDispensarySchema(dispensary, baseUrl) {
    const schema = {
      "@context": "https://schema.org",
      "@type": "Store",
      "name": dispensary.name,
      "url": `${baseUrl}/dispensary/${dispensary.slug}`,
      "image": dispensary.logo_url || dispensary.photo_urls?.[0] || "",
      "telephone": dispensary.phone || "",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": dispensary.address_street,
        "addressLocality": dispensary.city,
        "addressRegion": dispensary.state_abbr || dispensary.state_name,
        "postalCode": dispensary.zip,
        "addressCountry": "US"
      }
    };

    // Add geo coordinates if available
    if (dispensary.lat && dispensary.lng) {
      schema.geo = {
        "@type": "GeoCoordinates",
        "latitude": dispensary.lat,
        "longitude": dispensary.lng
      };
    }

    // Add rating if available
    if (dispensary.google_rating) {
      schema.aggregateRating = {
        "@type": "AggregateRating",
        "ratingValue": dispensary.google_rating,
        "bestRating": "5",
        "worstRating": "1",
        "ratingCount": dispensary.google_review_count || 1
      };
    }

    // Add opening hours if available
    if (dispensary.hours) {
      schema.openingHoursSpecification = this.parseHours(dispensary.hours);
    }

    return schema;
  }

  /**
   * Generate Review schema
   */
  static generateReviewSchema(review, dispensary, baseUrl) {
    return {
      "@context": "https://schema.org",
      "@type": "Review",
      "itemReviewed": {
        "@type": "Store",
        "name": dispensary.name,
        "url": `${baseUrl}/dispensary/${dispensary.slug}`
      },
      "author": {
        "@type": "Person",
        "name": review.author_name
      },
      "reviewRating": {
        "@type": "Rating",
        "ratingValue": review.rating,
        "bestRating": "5",
        "worstRating": "1"
      },
      "reviewBody": review.review_text,
      "datePublished": review.created_at
    };
  }

  /**
   * Generate FAQ schema
   */
  static generateFAQSchema(faqs) {
    return {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": faqs.map(faq => ({
        "@type": "Question",
        "name": faq.question,
        "acceptedAnswer": {
          "@type": "Answer",
          "text": faq.answer
        }
      }))
    };
  }

  /**
   * Generate BreadcrumbList schema
   */
  static generateBreadcrumbSchema(items, baseUrl) {
    return {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": items.map((item, index) => ({
        "@type": "ListItem",
        "position": index + 1,
        "name": item.name,
        "item": item.url ? `${baseUrl}${item.url}` : undefined
      }))
    };
  }

  /**
   * Generate ItemList schema for rankings
   */
  static generateRankingSchema(dispensaries, title, baseUrl) {
    return {
      "@context": "https://schema.org",
      "@type": "ItemList",
      "name": title,
      "itemListElement": dispensaries.slice(0, 10).map((disp, index) => ({
        "@type": "ListItem",
        "position": index + 1,
        "item": {
          "@type": "Store",
          "name": disp.name,
          "url": `${baseUrl}/dispensary/${disp.slug}`,
          "aggregateRating": disp.google_rating ? {
            "@type": "AggregateRating",
            "ratingValue": disp.google_rating,
            "ratingCount": disp.google_review_count || 1
          } : undefined
        }
      }))
    };
  }

  /**
   * Parse opening hours (if available in structured format)
   */
  static parseHours(hours) {
    // This would parse hours data if we have it
    // For now, return empty array
    return [];
  }

  /**
   * Convert schema object to JSON-LD script tag
   */
  static toScriptTag(schema) {
    return `<script type="application/ld+json">\n${JSON.stringify(schema, null, 2)}\n</script>`;
  }
}

module.exports = SchemaGenerator;

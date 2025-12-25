const db = require('../config/database');
const slugify = require('slugify');

class BlogPost {
  /**
   * Create a new blog post
   */
  static async create(data) {
    const slug = slugify(data.title, { lower: true, strict: true });

    const query = `
      INSERT INTO blog_posts (
        title, slug, excerpt, content, author, featured_image,
        meta_description, meta_keywords, category, tags, is_published
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)
      RETURNING *
    `;

    const result = await db.query(query, [
      data.title,
      slug,
      data.excerpt,
      data.content,
      data.author || 'Admin',
      data.featured_image || null,
      data.meta_description || data.excerpt,
      JSON.stringify(data.meta_keywords || []),
      data.category || 'General',
      JSON.stringify(data.tags || []),
      data.is_published !== false
    ]);

    return result.rows[0];
  }

  /**
   * Find all published blog posts
   */
  static async findAll(options = {}) {
    const limit = options.limit || 10;
    const offset = options.offset || 0;
    const category = options.category;

    let query = `
      SELECT * FROM blog_posts
      WHERE is_published = true
    `;

    const params = [];
    let paramCount = 1;

    if (category) {
      query += ` AND category = $${paramCount}`;
      params.push(category);
      paramCount++;
    }

    query += ` ORDER BY created_at DESC LIMIT $${paramCount} OFFSET $${paramCount + 1}`;
    params.push(limit, offset);

    const result = await db.query(query, params);
    return result.rows;
  }

  /**
   * Find blog post by slug
   */
  static async findBySlug(slug) {
    const result = await db.query(
      'SELECT * FROM blog_posts WHERE slug = $1 AND is_published = true',
      [slug]
    );
    return result.rows[0];
  }

  /**
   * Get total count of published posts
   */
  static async getCount(category = null) {
    let query = 'SELECT COUNT(*) FROM blog_posts WHERE is_published = true';
    const params = [];

    if (category) {
      query += ' AND category = $1';
      params.push(category);
    }

    const result = await db.query(query, params);
    return parseInt(result.rows[0].count);
  }

  /**
   * Get all categories with post counts
   */
  static async getCategories() {
    const result = await db.query(`
      SELECT category, COUNT(*) as count
      FROM blog_posts
      WHERE is_published = true
      GROUP BY category
      ORDER BY count DESC
    `);
    return result.rows;
  }

  /**
   * Get related posts by tags
   */
  static async getRelatedPosts(postId, tags, limit = 3) {
    const result = await db.query(`
      SELECT * FROM blog_posts
      WHERE id != $1
        AND is_published = true
        AND tags::jsonb ?| $2
      ORDER BY created_at DESC
      LIMIT $3
    `, [postId, tags, limit]);

    return result.rows;
  }

  /**
   * Increment view count
   */
  static async incrementViews(id) {
    await db.query(
      'UPDATE blog_posts SET view_count = view_count + 1 WHERE id = $1',
      [id]
    );
  }

  /**
   * Search blog posts
   */
  static async search(query, limit = 10) {
    const result = await db.query(`
      SELECT * FROM blog_posts
      WHERE is_published = true
        AND (
          title ILIKE $1
          OR excerpt ILIKE $1
          OR content ILIKE $1
        )
      ORDER BY created_at DESC
      LIMIT $2
    `, [`%${query}%`, limit]);

    return result.rows;
  }
}

module.exports = BlogPost;

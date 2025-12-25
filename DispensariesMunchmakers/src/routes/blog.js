const express = require('express');
const router = express.Router();
const BlogPost = require('../models/BlogPost');

// Blog index page
router.get('/', async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const perPage = 12;
    const offset = (page - 1) * perPage;
    const category = req.query.category;

    const posts = await BlogPost.findAll({ limit: perPage, offset, category });
    const total = await BlogPost.getCount(category);
    const totalPages = Math.ceil(total / perPage);
    const categories = await BlogPost.getCategories();

    res.render('blog/index', {
      title: category ? `${category} - Cannabis Dispensary Blog` : 'Cannabis Dispensary Blog - Guides, Tips & Industry News',
      posts,
      categories,
      currentPage: page,
      totalPages,
      currentCategory: category || null,
      meta: {
        description: 'Expert guides, tips, and news about cannabis dispensaries. Find the best dispensaries, learn about products, and stay updated on industry trends.',
        keywords: 'cannabis dispensary blog, marijuana guides, dispensary tips, cannabis news, weed education'
      }
    });
  } catch (error) {
    console.error('Error loading blog:', error);
    res.status(500).send('Error loading blog');
  }
});

// Individual blog post
router.get('/:slug', async (req, res) => {
  try {
    const post = await BlogPost.findBySlug(req.params.slug);

    if (!post) {
      return res.status(404).render('404', {
        title: 'Blog Post Not Found'
      });
    }

    // Increment view count
    await BlogPost.incrementViews(post.id);

    // Get related posts
    const tags = typeof post.tags === 'string' ? JSON.parse(post.tags) : post.tags;
    const relatedPosts = await BlogPost.getRelatedPosts(post.id, tags, 3);

    res.render('blog/post', {
      title: `${post.title} | Cannabis Dispensary Blog`,
      post: {
        ...post,
        tags,
        meta_keywords: typeof post.meta_keywords === 'string' ? JSON.parse(post.meta_keywords) : post.meta_keywords
      },
      relatedPosts,
      baseUrl: process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com',
      meta: {
        description: post.meta_description || post.excerpt,
        keywords: (typeof post.meta_keywords === 'string' ? JSON.parse(post.meta_keywords) : post.meta_keywords).join(', ')
      }
    });
  } catch (error) {
    console.error('Error loading blog post:', error);
    res.status(500).send('Error loading blog post');
  }
});

module.exports = router;

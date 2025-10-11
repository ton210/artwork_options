<?php /* Template Name: Blog/Resources Page */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog & Resources - Algoboost | Shopify Conversion Tracking Tips & Guides</title>
    <meta name="description" content="Expert insights on Shopify conversion tracking, server-side tracking, iOS 14.5+ solutions, and advertising optimization. Learn from the Algoboost team.">
    <meta name="keywords" content="shopify tracking blog, conversion tracking tips, server-side tracking guide, facebook pixel tracking, google ads conversion tracking">

    <!-- Open Graph -->
    <meta property="og:title" content="Blog & Resources - Algoboost">
    <meta property="og:description" content="Expert Shopify conversion tracking and advertising optimization content.">
    <meta property="og:image" content="<?php echo home_url('/images/blog-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/blog'); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎯</text></svg>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --text-dark: #1a202c;
            --text-light: #718096;
            --bg-light: #f7fafc;
            --white: #ffffff;
            --success: #10b981;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --radius: 0.75rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        /* Navigation - Dashboard Style */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 4rem 2rem 3rem;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        /* Search Container */
        .search-container {
            max-width: 500px;
            margin: 2rem auto 0;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.5rem 1rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            background: white;
            color: var(--text-dark);
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .filter-title {
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            background: var(--bg-light);
            border: 2px solid transparent;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }

        .filter-tab:hover,
        .filter-tab.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Featured Article */
        .featured-section {
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text-dark);
        }

        .featured-article {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .featured-article:hover {
            transform: translateY(-5px);
        }

        .featured-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
        }

        .featured-image {
            height: 400px;
            background: linear-gradient(135deg, var(--bg-light), #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            position: relative;
        }

        .featured-badge {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .featured-content {
            padding: 3rem;
        }

        .featured-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .featured-excerpt {
            color: var(--text-light);
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.7;
        }

        .featured-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Articles Grid */
        .articles-section {
            margin-bottom: 4rem;
        }

        .articles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .articles-title {
            font-size: 2rem;
            color: var(--text-dark);
        }

        .view-toggle {
            display: flex;
            gap: 0.5rem;
            background: var(--bg-light);
            padding: 0.25rem;
            border-radius: 25px;
        }

        .view-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            background: transparent;
            cursor: pointer;
            transition: all 0.3s;
        }

        .view-btn.active {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .articles-list {
            display: none;
        }

        .articles-list.active {
            display: block;
        }

        .article-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border-color: var(--primary);
            text-decoration: none;
            color: inherit;
        }

        .article-image {
            height: 200px;
            background: linear-gradient(135deg, var(--bg-light), #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
        }

        .article-category {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--primary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .article-content {
            padding: 2rem;
        }

        .article-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .article-excerpt {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .read-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Article List View */
        .article-list-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            display: flex;
            gap: 2rem;
            align-items: center;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .article-list-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }

        .list-item-image {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--bg-light), #e2e8f0);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }

        .list-item-content {
            flex: 1;
        }

        /* Resources Section */
        .resources-section {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 4rem;
        }

        .resources-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
        }

        .resources-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .resource-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .resource-card:hover {
            transform: translateY(-5px);
        }

        .resource-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        .resource-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .resource-description {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .resource-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .resource-link:hover {
            text-decoration: underline;
        }

        /* Newsletter Section */
        .newsletter-section {
            background: linear-gradient(135deg, var(--text-dark), #2d3748);
            color: white;
            padding: 4rem 2rem;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 4rem;
        }

        .newsletter-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .newsletter-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .newsletter-form {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .newsletter-input {
            flex: 1;
            min-width: 250px;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
        }

        .newsletter-input:focus {
            outline: none;
        }

        .newsletter-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .newsletter-btn:hover {
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            background: var(--gray-900);
            color: white;
            padding: 3rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .footer-section a {
            color: #cbd5e0;
            text-decoration: none;
            display: block;
            padding: 0.25rem 0;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #4a5568;
            color: #cbd5e0;
            font-size: 0.875rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .main-content {
                padding: 2rem 1rem;
            }

            .featured-grid {
                grid-template-columns: 1fr;
            }

            .featured-image {
                height: 250px;
            }

            .featured-content {
                padding: 2rem;
            }

            .articles-grid {
                grid-template-columns: 1fr;
            }

            .resources-grid {
                grid-template-columns: 1fr;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .newsletter-input {
                min-width: 100%;
            }

            .filter-tabs {
                flex-direction: column;
                align-items: center;
            }

            .articles-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .article-list-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo home_url(); ?>" class="logo">
                <div class="logo-icon">🎯</div>
                <span>Algoboost</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo home_url(); ?>">Home</a></li>
                <li><a href="<?php echo home_url('/features'); ?>">Features</a></li>
                <li><a href="<?php echo home_url('/pricing'); ?>">Pricing</a></li>
                <li><a href="<?php echo home_url('/about'); ?>">About</a></li>
                <li><a href="<?php echo home_url('/documentation'); ?>">Docs</a></li>
                <li><a href="<?php echo home_url('/blog'); ?>" class="active">Blog</a></li>
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Expert Tracking Insights & Resources</h1>
            <p class="hero-subtitle">Learn advanced Shopify conversion tracking, server-side implementation, and advertising optimization from industry experts</p>
            
            <div class="search-container">
                <div class="search-icon">🔍</div>
                <input type="text" class="search-input" placeholder="Search tracking guides, tutorials, and resources..." id="articleSearch">
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Filter Section -->
        <section class="filter-section">
            <h3 class="filter-title">Browse by Category</h3>
            <nav class="filter-tabs">
                <button class="filter-tab active" data-category="all">All Articles</button>
                <button class="filter-tab" data-category="server-side">Server-Side Tracking</button>
                <button class="filter-tab" data-category="shopify">Shopify Guides</button>
                <button class="filter-tab" data-category="facebook">Facebook Tracking</button>
                <button class="filter-tab" data-category="google">Google Ads</button>
                <button class="filter-tab" data-category="case-studies">Case Studies</button>
                <button class="filter-tab" data-category="updates">Product Updates</button>
            </nav>
        </section>

        <!-- Featured Article -->
        <section class="featured-section">
            <h2 class="section-title">Featured Article</h2>
            <article class="featured-article">
                <div class="featured-grid">
                    <div class="featured-image">
                        📊
                        <div class="featured-badge">Featured</div>
                    </div>
                    <div class="featured-content">
                        <h2 class="featured-title">The Complete Guide to Server-Side Tracking for Shopify Stores</h2>
                        <p class="featured-excerpt">
                            Master the fundamentals of server-side tracking and learn how to bypass iOS 14.5+ restrictions. 
                            This comprehensive guide covers Facebook Conversions API, Google Enhanced Conversions, and more.
                        </p>
                        <div class="featured-meta">
                            <span>📅 Jan 15, 2025</span>
                            <span>👤 By Alex Chen</span>
                            <span>⏱ 12 min read</span>
                        </div>
                        <a href="<?php echo home_url('/blog/complete-guide-server-side-tracking-shopify/'); ?>" class="btn btn-primary">Read Article</a>
                    </div>
                </div>
            </article>
        </section>

        <!-- Articles Section -->
        <section class="articles-section">
            <div class="articles-header">
                <h2 class="articles-title">Latest Articles</h2>
                <div class="view-toggle">
                    <button class="view-btn active" onclick="switchView('grid')">Grid</button>
                    <button class="view-btn" onclick="switchView('list')">List</button>
                </div>
            </div>

            <!-- Grid View -->
            <div class="articles-grid active" id="gridView">
                <a href="<?php echo home_url('/blog/facebook-conversions-api-setup-guide/'); ?>" class="article-card" data-category="facebook">
                    <div class="article-image">
                        📘
                        <span class="article-category">Facebook</span>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">Facebook Conversions API: Complete Setup Guide for 2025</h3>
                        <p class="article-excerpt">
                            Step-by-step tutorial to implement Facebook's Conversions API and recover lost conversion data 
                            caused by iOS tracking restrictions.
                        </p>
                        <div class="article-meta">
                            <span>Jan 12, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>8 min read</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo home_url('/blog/google-ads-enhanced-conversions/'); ?>" class="article-card" data-category="google">
                    <div class="article-image">
                        🎯
                        <span class="article-category">Google Ads</span>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">Google Ads Enhanced Conversions: Boost Attribution by 40%</h3>
                        <p class="article-excerpt">
                            Learn how to implement Google's Enhanced Conversions feature to improve attribution 
                            accuracy and optimize your ad spend effectively.
                        </p>
                        <div class="article-meta">
                            <span>Jan 10, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>7 min read</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo home_url('/blog/ios-14-5-impact-case-study/'); ?>" class="article-card" data-category="case-studies">
                    <div class="article-image">
                        📱
                        <span class="article-category">Case Study</span>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">How TechStore Pro Recovered 60% Lost Attribution with Algoboost</h3>
                        <p class="article-excerpt">
                            Real results from a tech accessories store that used server-side tracking to overcome 
                            iOS 14.5+ attribution challenges and boost ROAS.
                        </p>
                        <div class="article-meta">
                            <span>Jan 8, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>6 min read</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo home_url('/blog/shopify-plus-conversion-tracking/'); ?>" class="article-card" data-category="shopify">
                    <div class="article-image">
                        🛍️
                        <span class="article-category">Shopify</span>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">Shopify Plus Conversion Tracking: Advanced Implementation Guide</h3>
                        <p class="article-excerpt">
                            Unlock advanced tracking features available to Shopify Plus stores and learn how to 
                            implement enterprise-level attribution strategies.
                        </p>
                        <div class="article-meta">
                            <span>Jan 5, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>10 min read</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo home_url('/blog/multi-platform-attribution-modeling/'); ?>" class="article-card" data-category="server-side">
                    <div class="article-image">
                        🔗
                        <span class="article-category">Attribution</span>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">Multi-Platform Attribution: Connecting the Customer Journey</h3>
                        <p class="article-excerpt">
                            Learn how to track customers across multiple advertising platforms and create 
                            unified attribution models for better decision making.
                        </p>
                        <div class="article-meta">
                            <span>Jan 3, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>9 min read</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo home_url('/blog/ai-powered-tracking-optimization/'); ?>" class="article-card" data-category="updates">
                    <div class="article-image">
                        🤖
                        <span class="article-category">AI Updates</span>
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">Introducing AI-Powered Tracking Optimization</h3>
                        <p class="article-excerpt">
                            Our latest machine learning features automatically optimize your tracking setup 
                            and provide intelligent recommendations for better performance.
                        </p>
                        <div class="article-meta">
                            <span>Dec 30, 2024</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>5 min read</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- List View -->
            <div class="articles-list" id="listView">
                <a href="<?php echo home_url('/blog/facebook-conversions-api-setup-guide/'); ?>" class="article-list-item" data-category="facebook">
                    <div class="list-item-image">📘</div>
                    <div class="list-item-content">
                        <h3 class="article-title">Facebook Conversions API: Complete Setup Guide for 2025</h3>
                        <p class="article-excerpt">
                            Step-by-step tutorial to implement Facebook's Conversions API and recover lost conversion data 
                            caused by iOS tracking restrictions.
                        </p>
                        <div class="article-meta">
                            <span>Jan 12, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>8 min read</span>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="<?php echo home_url('/blog/google-ads-enhanced-conversions/'); ?>" class="article-list-item" data-category="google">
                    <div class="list-item-image">🎯</div>
                    <div class="list-item-content">
                        <h3 class="article-title">Google Ads Enhanced Conversions: Boost Attribution by 40%</h3>
                        <p class="article-excerpt">
                            Learn how to implement Google's Enhanced Conversions feature to improve attribution 
                            accuracy and optimize your ad spend effectively.
                        </p>
                        <div class="article-meta">
                            <span>Jan 10, 2025</span>
                            <div class="read-time">
                                <span>⏱</span>
                                <span>7 min read</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Resources Section -->
        <section class="resources-section">
            <h2 class="resources-title">Free Tracking Resources</h2>
            <p class="resources-subtitle">Download our expert guides and tools to master Shopify conversion tracking</p>

            <div class="resources-grid">
                <div class="resource-card">
                    <span class="resource-icon">📖</span>
                    <h3 class="resource-title">Server-Side Tracking Playbook</h3>
                    <p class="resource-description">
                        Complete 60-page guide covering Facebook CAPI, Google Enhanced Conversions, TikTok Events API, and more platform integrations.
                    </p>
                    <a href="<?php echo home_url('/resources/server-side-playbook/'); ?>" class="resource-link">Download Free →</a>
                </div>

                <div class="resource-card">
                    <span class="resource-icon">🛠️</span>
                    <h3 class="resource-title">Tracking Setup Checklist</h3>
                    <p class="resource-description">
                        Step-by-step checklist to ensure your Shopify store has optimal conversion tracking across all major platforms.
                    </p>
                    <a href="<?php echo home_url('/resources/tracking-checklist/'); ?>" class="resource-link">Get Checklist →</a>
                </div>

                <div class="resource-card">
                    <span class="resource-icon">📊</span>
                    <h3 class="resource-title">Attribution Comparison Tool</h3>
                    <p class="resource-description">
                        Spreadsheet template to compare attribution data across platforms and identify tracking gaps in your setup.
                    </p>
                    <a href="<?php echo home_url('/resources/attribution-tool/'); ?>" class="resource-link">Download Tool →</a>
                </div>

                <div class="resource-card">
                    <span class="resource-icon">🧮</span>
                    <h3 class="resource-title">Tracking ROI Calculator</h3>
                    <p class="resource-description">
                        Interactive calculator to estimate the revenue impact of implementing proper server-side tracking.
                    </p>
                    <a href="<?php echo home_url('/resources/tracking-roi-calculator/'); ?>" class="resource-link">Use Calculator →</a>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <h2 class="newsletter-title">Stay Updated</h2>
            <p class="newsletter-subtitle">
                Get the latest tracking insights, iOS updates, platform changes, and optimization tips delivered weekly.
            </p>
            
            <form class="newsletter-form" onsubmit="handleNewsletterSignup(event)">
                <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                <button type="submit" class="newsletter-btn">Subscribe</button>
            </form>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Product</h3>
                <a href="<?php echo home_url('/features'); ?>">Features</a>
                <a href="<?php echo home_url('/pricing'); ?>">Pricing</a>
                <a href="<?php echo home_url('/documentation'); ?>">Documentation</a>
                <a href="<?php echo home_url('/security'); ?>">Security</a>
            </div>
            
            <div class="footer-section">
                <h3>Company</h3>
                <a href="<?php echo home_url('/about'); ?>">About Us</a>
                <a href="<?php echo home_url('/blog'); ?>">Blog</a>
                <a href="<?php echo home_url('/contact'); ?>">Contact</a>
            </div>
            
            <div class="footer-section">
                <h3>Support</h3>
                <a href="<?php echo home_url('/documentation'); ?>">Help Center</a>
                <a href="<?php echo home_url('/contact'); ?>">Contact Support</a>
                <a href="mailto:support@algoboost.io">Email Support</a>
            </div>
            
            <div class="footer-section">
                <h3>Legal</h3>
                <a href="<?php echo home_url('/privacy'); ?>">Privacy Policy</a>
                <a href="<?php echo home_url('/terms'); ?>">Terms of Service</a>
                <a href="<?php echo home_url('/gdpr'); ?>">GDPR</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Algoboost. All rights reserved. Made with ❤️ for Shopify merchants.</p>
            <p style="margin-top: 0.5rem;">
                A <a href="https://greenlunar.com" target="_blank">Green Lunar</a> Company | GDPR Compliant 🇪🇺
            </p>
        </div>
    </footer>

    <script>
        // Filter functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(t => {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                // Filter articles
                const category = this.dataset.category;
                const articles = document.querySelectorAll('.article-card, .article-list-item');

                articles.forEach(article => {
                    if (category === 'all' || article.dataset.category === category) {
                        article.style.display = 'block';
                        article.style.animation = 'fadeIn 0.5s ease-in-out';
                    } else {
                        article.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        document.getElementById('articleSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const articles = document.querySelectorAll('.article-card, .article-list-item');

            articles.forEach(article => {
                const title = article.querySelector('.article-title').textContent.toLowerCase();
                const excerpt = article.querySelector('.article-excerpt').textContent.toLowerCase();

                if (title.includes(searchTerm) || excerpt.includes(searchTerm)) {
                    article.style.display = 'block';
                } else {
                    article.style.display = 'none';
                }
            });
        });

        // View switching
        function switchView(view) {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const buttons = document.querySelectorAll('.view-btn');

            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Switch views
            if (view === 'grid') {
                gridView.style.display = 'grid';
                gridView.classList.add('active');
                listView.style.display = 'none';
                listView.classList.remove('active');
            } else {
                gridView.style.display = 'none';
                gridView.classList.remove('active');
                listView.style.display = 'block';
                listView.classList.add('active');
            }
        }

        // Newsletter signup
        function handleNewsletterSignup(event) {
            event.preventDefault();
            
            const email = event.target.querySelector('.newsletter-input').value;
            console.log('Newsletter signup:', email);
            
            // Show success message
            const btn = event.target.querySelector('.newsletter-btn');
            const originalText = btn.textContent;
            btn.textContent = 'Subscribed! ✓';
            btn.style.background = '#10b981';
            
            // Reset form
            event.target.reset();
            
            // Reset button after 3 seconds
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '';
            }, 3000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
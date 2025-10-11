<?php /* Template Name: Resources Hub - Algoboost */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Resources - Algoboost | Shopify Conversion Tracking Tools & Guides</title>
    <meta name="description" content="Download free Shopify tracking resources: Server-side tracking playbook, setup checklist, attribution comparison tool, and ROI calculator. Boost your store's tracking accuracy.">
    <meta name="keywords" content="shopify tracking resources, free tracking tools, server-side tracking guide, facebook pixel tools, google ads tracking, conversion optimization">

    <!-- Open Graph -->
    <meta property="og:title" content="Free Resources - Algoboost">
    <meta property="og:description" content="Professional Shopify tracking tools and guides to improve your conversion attribution.">
    <meta property="og:image" content="<?php echo home_url('/images/resources-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/resources/'); ?>">

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
            --warning: #f59e0b;
            --info: #3b82f6;
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

        /* Navigation */
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

        /* Hero Section */
        .hero {
            margin-top: 80px;
            padding: 5rem 2rem 3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: slide 20s linear infinite;
        }

        @keyframes slide {
            0% { transform: translateX(0); }
            100% { transform: translateX(50%); }
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 3rem;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .hero-stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            text-align: center;
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

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        /* Resources Grid */
        .resources-section {
            margin-bottom: 6rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .resource-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .resource-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s;
        }

        .resource-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }

        .resource-card:hover::before {
            transform: scaleX(1);
        }

        .resource-card.playbook::before {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .resource-card.checklist::before {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .resource-card.attribution::before {
            background: linear-gradient(135deg, var(--info), #1e40af);
        }

        .resource-card.calculator::before {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .resource-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            display: block;
        }

        .resource-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .resource-description {
            color: var(--text-light);
            margin-bottom: 2rem;
            line-height: 1.7;
            font-size: 1.1rem;
        }

        .resource-features {
            list-style: none;
            margin-bottom: 2.5rem;
        }

        .resource-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-light);
        }

        .resource-features li::before {
            content: '✓';
            background: var(--success);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .resource-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 10px;
        }

        .resource-type {
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .resource-type.playbook {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .resource-type.checklist {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .resource-type.attribution {
            background: linear-gradient(135deg, var(--info), #1e40af);
        }

        .resource-type.calculator {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .resource-downloads {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .resource-card .btn {
            width: 100%;
            justify-content: center;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Featured Resource */
        .featured-resource {
            background: linear-gradient(135deg, var(--text-dark), #2d3748);
            color: white;
            border-radius: 20px;
            padding: 4rem 3rem;
            margin: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .featured-resource::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .featured-content {
            position: relative;
            z-index: 1;
        }

        .featured-badge {
            background: var(--warning);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .featured-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .featured-description {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .featured-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .featured-stat {
            text-align: center;
        }

        .featured-stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .featured-stat-label {
            opacity: 0.8;
        }

        /* Newsletter Signup */
        .newsletter-section {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
            margin: 6rem 0;
        }

        .newsletter-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .newsletter-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .newsletter-form {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .newsletter-input {
            flex: 1;
            min-width: 250px;
            padding: 1rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }

        .newsletter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .newsletter-benefits {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .newsletter-benefit {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }

            .resources-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .main-content {
                padding: 2rem 1rem;
            }

            .newsletter-form {
                flex-direction: column;
                align-items: center;
            }

            .newsletter-input {
                min-width: 100%;
            }

            .featured-stats {
                grid-template-columns: repeat(2, 1fr);
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
                <li><a href="<?php echo home_url('/blog'); ?>">Blog</a></li>
                <li><a href="<?php echo home_url('/resources'); ?>" class="active">Resources</a></li>
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Free Tracking Resources Hub</h1>
            <p class="hero-subtitle">Professional-grade tools, templates, and guides to optimize your Shopify store's conversion tracking and boost attribution accuracy</p>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-number">25K+</div>
                    <div class="hero-stat-label">Downloads</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number">$12M+</div>
                    <div class="hero-stat-label">Attribution Recovered</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number">5K+</div>
                    <div class="hero-stat-label">Active Users</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Resources Section -->
        <section class="resources-section">
            <div class="section-header">
                <h2 class="section-title">Free Shopify Tracking Resources</h2>
                <p class="section-subtitle">Everything you need to implement, track, and optimize server-side conversion tracking with professional-grade tools and comprehensive guides</p>
            </div>

            <div class="resources-grid">
                <!-- Server-Side Tracking Playbook -->
                <div class="resource-card playbook">
                    <span class="resource-icon">📖</span>
                    <h3 class="resource-title">Server-Side Tracking Playbook</h3>
                    <p class="resource-description">Complete 60-page implementation guide covering Facebook Conversions API, Google Enhanced Conversions, TikTok Events API, and 15+ other platform integrations.</p>
                    
                    <div class="resource-meta">
                        <span class="resource-type playbook">PDF Guide</span>
                        <span class="resource-downloads">8,500+ downloads</span>
                    </div>
                    
                    <ul class="resource-features">
                        <li>8 comprehensive chapters</li>
                        <li>18+ platform integrations</li>
                        <li>Code examples & scripts</li>
                        <li>Real-world case studies</li>
                        <li>Troubleshooting guide</li>
                    </ul>
                    
                    <a href="<?php echo home_url('/resources/server-side-playbook/'); ?>" class="btn btn-primary">Download Free PDF</a>
                </div>

                <!-- Tracking Setup Checklist -->
                <div class="resource-card checklist">
                    <span class="resource-icon">✅</span>
                    <h3 class="resource-title">Tracking Setup Checklist</h3>
                    <p class="resource-description">Step-by-step interactive checklist to ensure your Shopify store has optimal conversion tracking across all major advertising platforms.</p>
                    
                    <div class="resource-meta">
                        <span class="resource-type checklist">Interactive PDF</span>
                        <span class="resource-downloads">12,300+ downloads</span>
                    </div>
                    
                    <ul class="resource-features">
                        <li>50+ critical checkpoints</li>
                        <li>Platform-specific setup steps</li>
                        <li>Testing & validation procedures</li>
                        <li>Privacy compliance checks</li>
                        <li>Progress tracking system</li>
                    </ul>
                    
                    <a href="<?php echo home_url('/resources/tracking-checklist/'); ?>" class="btn btn-primary">Get Free Checklist</a>
                </div>

                <!-- Attribution Comparison Tool -->
                <div class="resource-card attribution">
                    <span class="resource-icon">📊</span>
                    <h3 class="resource-title">Attribution Comparison Tool</h3>
                    <p class="resource-description">Advanced spreadsheet template to compare attribution data across advertising platforms and identify tracking gaps and discrepancies.</p>
                    
                    <div class="resource-meta">
                        <span class="resource-type attribution">Excel Template</span>
                        <span class="resource-downloads">6,200+ downloads</span>
                    </div>
                    
                    <ul class="resource-features">
                        <li>18 platform comparisons</li>
                        <li>Automated gap detection</li>
                        <li>Visual reporting charts</li>
                        <li>Trend analysis tools</li>
                        <li>Custom metrics tracking</li>
                    </ul>
                    
                    <a href="<?php echo home_url('/resources/attribution-tool/'); ?>" class="btn btn-primary">Download Free Tool</a>
                </div>

                <!-- Tracking ROI Calculator -->
                <div class="resource-card calculator">
                    <span class="resource-icon">🧮</span>
                    <h3 class="resource-title">Tracking ROI Calculator</h3>
                    <p class="resource-description">Interactive calculator to estimate the revenue impact of implementing proper server-side tracking for your Shopify store.</p>
                    
                    <div class="resource-meta">
                        <span class="resource-type calculator">Interactive Tool</span>
                        <span class="resource-downloads">4,800+ calculations</span>
                    </div>
                    
                    <ul class="resource-features">
                        <li>Real-time revenue projections</li>
                        <li>Attribution gap analysis</li>
                        <li>12-month impact forecasts</li>
                        <li>Platform-specific calculations</li>
                        <li>Investment comparison</li>
                    </ul>
                    
                    <a href="<?php echo home_url('/resources/tracking-roi-calculator/'); ?>" class="btn btn-primary">Calculate Your ROI</a>
                </div>
            </div>
        </section>

        <!-- Featured Resource -->
        <section class="featured-resource">
            <div class="featured-content">
                <span class="featured-badge">Most Popular</span>
                <h2 class="featured-title">Server-Side Tracking Playbook</h2>
                <p class="featured-description">Our comprehensive 60-page guide has helped thousands of Shopify merchants recover lost attribution data and improve their advertising performance. Get the exact implementation strategies used by successful stores.</p>
                
                <div class="featured-stats">
                    <div class="featured-stat">
                        <div class="featured-stat-number">8.5K+</div>
                        <div class="featured-stat-label">Downloads</div>
                    </div>
                    <div class="featured-stat">
                        <div class="featured-stat-number">42%</div>
                        <div class="featured-stat-label">Avg ROAS Increase</div>
                    </div>
                    <div class="featured-stat">
                        <div class="featured-stat-number">18</div>
                        <div class="featured-stat-label">Platform Guides</div>
                    </div>
                    <div class="featured-stat">
                        <div class="featured-stat-number">4.8/5</div>
                        <div class="featured-stat-label">User Rating</div>
                    </div>
                </div>
                
                <a href="<?php echo home_url('/resources/server-side-playbook/'); ?>" class="btn btn-primary" style="max-width: 300px; margin: 0 auto;">Download Free Playbook</a>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <h2 class="newsletter-title">Get New Resources First</h2>
            <p class="newsletter-subtitle">Join 5,000+ Shopify merchants getting weekly tracking tips and early access to new resources</p>
            
            <form class="newsletter-form" onsubmit="handleNewsletterSignup(event)">
                <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">Subscribe Free</button>
            </form>
            
            <div class="newsletter-benefits">
                <div class="newsletter-benefit">
                    <span>📧</span> Weekly tracking insights
                </div>
                <div class="newsletter-benefit">
                    <span>🎯</span> Exclusive resources
                </div>
                <div class="newsletter-benefit">
                    <span>📈</span> Platform updates
                </div>
                <div class="newsletter-benefit">
                    <span>🚫</span> No spam, ever
                </div>
            </div>
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
        // Newsletter signup handling
        function handleNewsletterSignup(event) {
            event.preventDefault();
            
            const email = event.target.querySelector('.newsletter-input').value;
            console.log('Newsletter signup:', email);
            
            // Show success feedback
            const button = event.target.querySelector('.btn');
            const originalText = button.textContent;
            button.textContent = 'Subscribed! ✓';
            button.style.background = '#10b981';
            
            // Reset form and button
            setTimeout(() => {
                event.target.reset();
                button.textContent = originalText;
                button.style.background = '';
                
                // Show welcome message
                alert('🎯 Welcome to the Algoboost community!\n\nCheck your email for a welcome message and your first tracking tip.');
            }, 2000);
            
            // Analytics tracking
            window.dispatchEvent(new CustomEvent('newsletterSignup', {
                detail: { email: email, source: 'resources_page' }
            }));
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe resource cards for animation
        document.querySelectorAll('.resource-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });

        // Track resource card clicks
        document.querySelectorAll('.resource-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.tagName !== 'A') {
                    const title = this.querySelector('.resource-title').textContent;
                    const link = this.querySelector('.btn').href;
                    console.log('Resource card clicked:', title);
                    
                    // Custom event for analytics
                    window.dispatchEvent(new CustomEvent('resourceCardClicked', {
                        detail: { resource: title, url: link }
                    }));
                }
            });
        });

        // Track CTA button clicks
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                const resource = this.closest('.resource-card')?.querySelector('.resource-title')?.textContent || 'Unknown';
                console.log('Resource CTA clicked:', resource, this.href);
                
                // Custom event for analytics
                window.dispatchEvent(new CustomEvent('resourceCTAClicked', {
                    detail: { 
                        resource: resource, 
                        button: this.textContent.trim(),
                        url: this.href 
                    }
                }));
            });
        });

        // Add hover effects to featured stats
        document.querySelectorAll('.featured-stat').forEach(stat => {
            stat.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            stat.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Parallax effect for hero background
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                const rate = scrolled * -0.5;
                hero.style.transform = `translateY(${rate}px)`;
            }
        });

        // Initialize AOS (Animate On Scroll) effects
        document.addEventListener('DOMContentLoaded', function() {
            // Staggered animation for hero stats
            document.querySelectorAll('.hero-stat').forEach((stat, index) => {
                stat.style.opacity = '0';
                stat.style.transform = 'translateY(20px)';
                stat.style.transition = `opacity 0.6s ease ${0.2 + index * 0.1}s, transform 0.6s ease ${0.2 + index * 0.1}s`;
                
                setTimeout(() => {
                    stat.style.opacity = '1';
                    stat.style.transform = 'translateY(0)';
                }, 500 + index * 100);
            });
        });
    </script>
</body>
</html>
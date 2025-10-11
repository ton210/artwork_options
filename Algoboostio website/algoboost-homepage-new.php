<?php /* Template Name: Algoboost Homepage */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algoboost - Advanced Shopify Meta Conversion Tracking, Google Ads Tracking, TikTok Pixel & Server-Side Tracking</title>
    <meta name="description" content="Advanced Shopify Facebook pixel tracking, Google Ads conversion tracking, TikTok pixel, Pinterest tracking, Snapchat pixel, LinkedIn conversion tracking, Twitter ads tracking, Microsoft Ads UET, Amazon DSP tracking with server-side tracking and enhanced conversions. Built for post-iOS 14.5 world.">
    <meta name="keywords" content="shopify facebook pixel tracking, shopify google ads conversion tracking, shopify tiktok pixel, shopify pinterest conversion tracking, shopify snapchat pixel tracking, shopify linkedin ads tracking, shopify twitter ads conversion tracking, shopify microsoft ads tracking, shopify amazon ads tracking, shopify server side tracking, shopify enhanced conversions, shopify ios 14.5 tracking, shopify reddit ads tracking, shopify klaviyo tracking, shopify criteo tracking">
    
    <link rel="icon" type="image/png" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎯</text></svg>">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #10b981;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --text-dark: #1a202c;
            --text-light: #718096;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --radius: 0.75rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--bg-light);
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

        /* Hero Section - Dashboard Inspired */
        .hero {
            margin-top: 80px;
            padding: 6rem 2rem;
            background: white;
            border-bottom: 1px solid var(--gray-200);
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--gray-50);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
        }

        /* Stats Section - Card Grid */
        .stats-section {
            padding: 5rem 2rem;
            background: var(--bg-light);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card-icon.primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .stat-card-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-card-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-card-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .stat-card-label {
            font-size: 0.875rem;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Platforms Section */
        .platforms-section {
            padding: 5rem 2rem;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.2rem;
            margin-bottom: 3rem;
        }

        .platforms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .platform-card {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }

        .platform-card:hover {
            border-color: var(--primary);
            background: white;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .platform-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .platform-icon img {
            width: 32px;
            height: 32px;
            filter: grayscale(50%);
            transition: filter 0.3s;
        }

        .platform-card:hover .platform-icon img {
            filter: grayscale(0%);
        }

        .platform-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .platform-category {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 0.75rem;
        }

        .platform-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 500;
        }

        .status-available {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-server {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        /* Features Section */
        .features-section {
            padding: 5rem 2rem;
            background: var(--bg-light);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .feature-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-white {
            background: white;
            color: var(--primary);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary);
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

        .footer-bottom a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-bottom a:hover {
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-features {
                flex-direction: column;
                align-items: center;
            }
            
            .platforms-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        /* SEO Content Section */
        .seo-content {
            padding: 3rem 2rem;
            background: white;
            border-top: 1px solid var(--gray-200);
        }

        .seo-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .seo-block {
            padding: 1.5rem;
            background: var(--gray-50);
            border-radius: var(--radius);
        }

        .seo-block h3 {
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .seo-block p {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.6;
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
                <li><a href="<?php echo home_url(); ?>" class="active">Home</a></li>
                <li><a href="<?php echo home_url('/features'); ?>">Features</a></li>
                <li><a href="<?php echo home_url('/pricing'); ?>">Pricing</a></li>
                <li><a href="<?php echo home_url('/about'); ?>">About</a></li>
                <li><a href="<?php echo home_url('/documentation'); ?>">Docs</a></li>
                <li><a href="<?php echo home_url('/blog'); ?>">Blog</a></li>
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <h1 class="hero-title">Advanced Multi-Platform Conversion Tracking</h1>
            <p class="hero-subtitle">
                Professional Shopify Facebook pixel tracking, Google Ads conversion tracking, TikTok pixel, Pinterest tracking, and 15+ more platforms with server-side tracking and enhanced conversions
            </p>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <span>🔒</span>
                    <span>Server-Side Tracking</span>
                </div>
                <div class="hero-feature">
                    <span>⚡</span>
                    <span>Enhanced Conversions</span>
                </div>
                <div class="hero-feature">
                    <span>📊</span>
                    <span>Real-time Analytics</span>
                </div>
                <div class="hero-feature">
                    <span>🎯</span>
                    <span>18+ Platforms</span>
                </div>
            </div>
            
            <div class="cta-buttons">
                <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Start Free Trial</a>
                <a href="<?php echo home_url('/features'); ?>" class="btn btn-outline">View Features</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon primary">📈</div>
                    <div class="stat-card-value">+42%</div>
                    <div class="stat-card-label">Average ROAS Improvement</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon success">🎯</div>
                    <div class="stat-card-value">18+</div>
                    <div class="stat-card-label">Advertising Platforms</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon warning">🏪</div>
                    <div class="stat-card-value">5,000+</div>
                    <div class="stat-card-label">Active Shopify Stores</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon primary">⭐</div>
                    <div class="stat-card-value">4.8★</div>
                    <div class="stat-card-label">App Store Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Platforms -->
    <section class="platforms-section">
        <div class="section-title">Supported Shopify Conversion Tracking Platforms</div>
        <p class="section-subtitle">Connect all your advertising platforms with one-click Shopify pixel installation</p>
        
        <div class="platforms-grid">
            <!-- Social Media Platforms -->
            <div class="platform-card">
                <div class="platform-icon" style="background: #1877f2;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/meta.svg" alt="Meta Facebook Instagram Shopify Pixel Tracking">
                </div>
                <div class="platform-name">Meta (Facebook)</div>
                <div class="platform-category">Social Media</div>
                <div class="platform-status status-server">Server-Side</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #4285F4;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/googleads.svg" alt="Google Ads Shopify Conversion Tracking">
                </div>
                <div class="platform-name">Google Ads</div>
                <div class="platform-category">Search</div>
                <div class="platform-status status-server">Enhanced Conversions</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #E37400;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/googleanalytics.svg" alt="Google Analytics Shopify Tracking">
                </div>
                <div class="platform-name">Google Analytics 4</div>
                <div class="platform-category">Analytics</div>
                <div class="platform-status status-available">GA4 + MP</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #000000;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/tiktok.svg" alt="TikTok Pixel Shopify Tracking">
                </div>
                <div class="platform-name">TikTok</div>
                <div class="platform-category">Social Media</div>
                <div class="platform-status status-server">Events API</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #BD081C;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/pinterest.svg" alt="Pinterest Shopify Conversion Tracking">
                </div>
                <div class="platform-name">Pinterest</div>
                <div class="platform-category">Social Media</div>
                <div class="platform-status status-server">Conversions API</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #FFFC00;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/snapchat.svg" alt="Snapchat Shopify Pixel Tracking">
                </div>
                <div class="platform-name">Snapchat</div>
                <div class="platform-category">Social Media</div>
                <div class="platform-status status-server">CAPI</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #0A66C2;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/linkedin.svg" alt="LinkedIn Ads Shopify Conversion Tracking">
                </div>
                <div class="platform-name">LinkedIn Ads</div>
                <div class="platform-category">B2B</div>
                <div class="platform-status status-available">Insight Tag</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #000000;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/x.svg" alt="Twitter X Ads Shopify Tracking">
                </div>
                <div class="platform-name">X (Twitter)</div>
                <div class="platform-category">Social Media</div>
                <div class="platform-status status-available">Website Tag</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #00a82d;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/microsoftbing.svg" alt="Microsoft Ads Shopify Conversion Tracking">
                </div>
                <div class="platform-name">Microsoft Ads</div>
                <div class="platform-category">Search</div>
                <div class="platform-status status-available">UET Tracking</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #FF9900;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/amazon.svg" alt="Amazon DSP Shopify Tracking">
                </div>
                <div class="platform-name">Amazon DSP</div>
                <div class="platform-category">Marketplace</div>
                <div class="platform-status status-available">Attribution</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #FF4500;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/reddit.svg" alt="Reddit Ads Shopify Conversion Tracking">
                </div>
                <div class="platform-name">Reddit Ads</div>
                <div class="platform-category">Social Media</div>
                <div class="platform-status status-available">Pixel</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #000000;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/klaviyo.svg" alt="Klaviyo Shopify Integration Tracking">
                </div>
                <div class="platform-name">Klaviyo</div>
                <div class="platform-category">Email Marketing</div>
                <div class="platform-status status-available">Native Integration</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #FFA426;">
                    <span style="color: white; font-weight: bold;">C</span>
                </div>
                <div class="platform-name">Criteo</div>
                <div class="platform-category">Retargeting</div>
                <div class="platform-status status-available">OneTag</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #0073CF;">
                    <span style="color: white; font-weight: bold;">T</span>
                </div>
                <div class="platform-name">Taboola</div>
                <div class="platform-category">Native Ads</div>
                <div class="platform-status status-available">Pixel</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #F55200;">
                    <span style="color: white; font-weight: bold;">O</span>
                </div>
                <div class="platform-name">Outbrain</div>
                <div class="platform-category">Native Ads</div>
                <div class="platform-status status-available">Amplify</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #246FDB;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/googletagmanager.svg" alt="Google Tag Manager Shopify Integration">
                </div>
                <div class="platform-name">Google Tag Manager</div>
                <div class="platform-category">Tag Management</div>
                <div class="platform-status status-available">Server-Side</div>
            </div>
            
            <div class="platform-card">
                <div class="platform-icon" style="background: #52BD94;">
                    <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/segment.svg" alt="Segment Shopify CDP Integration">
                </div>
                <div class="platform-name">Segment</div>
                <div class="platform-category">CDP</div>
                <div class="platform-status status-available">Sources</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="section-title">Advanced Shopify Conversion Tracking Features</div>
        <p class="section-subtitle">Everything you need for accurate post-iOS 14.5 tracking</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Server-Side Tracking</h3>
                <p class="feature-description">Bypass iOS 14.5+ restrictions with enterprise-grade server-side tracking. Maintain 99% tracking accuracy regardless of browser limitations or ad blockers.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">Enhanced Conversions</h3>
                <p class="feature-description">Improve match rates by up to 30% with enhanced conversion data, customer information sharing, and advanced matching algorithms across all platforms.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Unified Analytics Dashboard</h3>
                <p class="feature-description">Monitor all your Shopify advertising platforms from one dashboard. Track performance, attribution, ROAS, and conversion data across 18+ platforms.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🧪</div>
                <h3 class="feature-title">A/B Testing</h3>
                <p class="feature-description">Test different tracking configurations, conversion values, and attribution models to optimize your Shopify store's conversion tracking performance.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Custom Events & Rules</h3>
                <p class="feature-description">Create sophisticated custom events and conversion value rules. Track specific actions that matter most to your Shopify business model.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3 class="feature-title">Mobile Optimized</h3>
                <p class="feature-description">Optimized for mobile commerce with faster loading times, better user experience, and accurate mobile conversion tracking across all platforms.</p>
            </div>
        </div>
    </section>

    <!-- SEO Content Section -->
    <section class="seo-content">
        <div class="seo-grid">
            <div class="seo-block">
                <h3>Shopify Facebook Pixel & Meta Conversion Tracking</h3>
                <p>Advanced Shopify Facebook pixel tracking with Conversions API integration. Track Facebook and Instagram ad conversions with server-side tracking to bypass iOS 14.5 restrictions. Enhanced Facebook pixel setup with advanced matching and customer data sharing for improved ROAS.</p>
            </div>
            
            <div class="seo-block">
                <h3>Google Ads Shopify Conversion Tracking</h3>
                <p>Professional Google Ads conversion tracking for Shopify stores. Enhanced conversions, Google Ads remarketing, Shopping campaigns tracking, and Performance Max optimization. Integrate Google Ads conversion tracking with server-side data for maximum accuracy.</p>
            </div>
            
            <div class="seo-block">
                <h3>TikTok Pixel Shopify Integration</h3>
                <p>Complete TikTok pixel tracking for Shopify with Events API integration. Track TikTok ad conversions, optimize for TikTok Shopping campaigns, and implement TikTok server-side tracking for improved attribution and measurement accuracy.</p>
            </div>
            
            <div class="seo-block">
                <h3>Multi-Platform Shopify Attribution</h3>
                <p>Advanced attribution modeling across Pinterest ads tracking, Snapchat pixel integration, LinkedIn conversion tracking, Twitter/X ads measurement, Microsoft Ads UET tracking, Amazon DSP attribution, Reddit ads tracking, and Klaviyo integration for comprehensive Shopify store analytics.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2 class="cta-title">Ready to Optimize Your Shopify Conversion Tracking?</h2>
        <p class="cta-subtitle">Join thousands of Shopify merchants already tracking better with Algoboost</p>
        
        <div class="cta-buttons">
            <a href="https://apps.shopify.com/algoboost" class="btn btn-white">Install Free App</a>
            <a href="<?php echo home_url('/pricing'); ?>" class="btn btn-outline">View Pricing</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Product</h3>
                <a href="<?php echo home_url('/features'); ?>">Features</a>
                <a href="<?php echo home_url('/pricing'); ?>">Pricing</a>
                <a href="<?php echo home_url('/documentation'); ?>">Documentation</a>
                <a href="<?php echo home_url('/security'); ?>">Security</a>
                <a href="<?php echo home_url('/sitemap'); ?>">Sitemap</a>
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
</body>
</html>
<?php /* Template Name: Algoboost Features */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - Advanced Shopify Conversion Tracking | Algoboost</title>
    <meta name="description" content="Discover Algoboost's powerful features: server-side tracking, enhanced conversions, A/B testing, custom events, multi-platform analytics, and advanced attribution modeling for Shopify stores.">
    <meta name="keywords" content="shopify server side tracking, shopify enhanced conversions, shopify a/b testing, shopify custom events, shopify multi platform tracking, shopify attribution modeling">
    
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

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 0;
            margin-top: 80px;
        }

        .breadcrumb-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .breadcrumb a {
            color: var(--text-light);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        /* Hero Section */
        .hero {
            padding: 4rem 2rem;
            background: white;
            text-align: center;
        }

        .hero-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Section Styles */
        .section {
            padding: 4rem 0;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: var(--text-light);
            text-align: center;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        /* Feature Categories */
        .categories-section {
            padding: 4rem 2rem;
            background: var(--bg-light);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            text-align: center;
        }

        .category-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .category-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .category-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .category-description {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Detailed Features */
        .features-section {
            padding: 5rem 2rem;
            background: white;
        }

        .feature-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-bottom: 5rem;
            align-items: center;
        }

        .feature-detail:nth-child(even) .feature-content {
            order: 2;
        }

        .feature-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .feature-content p {
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .feature-benefits {
            list-style: none;
            margin-bottom: 2rem;
        }

        .feature-benefits li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .feature-benefits li::before {
            content: "✓";
            color: var(--success);
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }

        .feature-visual {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 300px;
        }

        .feature-mockup {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            width: 100%;
            max-width: 400px;
        }

        .mockup-header {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .mockup-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gray-300);
        }

        .mockup-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .mockup-bar {
            height: 8px;
            border-radius: 4px;
            background: var(--gray-200);
        }

        .mockup-bar.primary {
            background: var(--primary);
            width: 60%;
        }

        .mockup-bar.success {
            background: var(--success);
            width: 40%;
        }

        .mockup-bar.warning {
            background: var(--warning);
            width: 80%;
        }

        .mockup-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .mockup-stat-label {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .mockup-stat-value {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .mockup-status {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 1rem;
            text-align: center;
        }

        /* Platform Integration */
        .platforms-section {
            padding: 5rem 2rem;
            background: var(--bg-light);
        }

        .platforms-tabs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .platform-tab {
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--gray-200);
            background: white;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .platform-tab.active,
        .platform-tab:hover {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .platforms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .platform-card {
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }

        .platform-card:hover {
            border-color: var(--primary);
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

        .platform-features {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .platform-feature {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            background: var(--gray-100);
            border-radius: 12px;
            color: var(--text-light);
        }

        .platform-feature.server {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .platform-feature.enhanced {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        /* CTA Section */
        .cta-section {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
        }

        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
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
            margin: 0 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-white {
            background: white;
            color: var(--primary);
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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
                font-size: 2rem;
            }

            .feature-detail {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .feature-detail:nth-child(even) .feature-content {
                order: unset;
            }

            .platforms-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .categories-grid {
                grid-template-columns: 1fr;
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
                <li><a href="<?php echo home_url('/features'); ?>" class="active">Features</a></li>
                <li><a href="<?php echo home_url('/pricing'); ?>">Pricing</a></li>
                <li><a href="<?php echo home_url('/about'); ?>">About</a></li>
                <li><a href="<?php echo home_url('/documentation'); ?>">Docs</a></li>
                <li><a href="<?php echo home_url('/blog'); ?>">Blog</a></li>
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="breadcrumb-container">
            <a href="<?php echo home_url(); ?>">Home</a> / Features
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <h1 class="hero-title">Powerful Features for Modern Tracking</h1>
            <p class="hero-subtitle">Everything you need for accurate Shopify conversion tracking in the post-iOS 14.5 world</p>
        </div>
    </section>

    <!-- Feature Categories -->
    <section class="categories-section">
        <div class="container">
            <div class="categories-grid">
                <div class="category-card">
                    <div class="category-icon">🔒</div>
                    <h3 class="category-title">Server-Side Tracking</h3>
                    <p class="category-description">Bypass browser restrictions with enterprise-grade server-side tracking that maintains accuracy regardless of iOS updates, ad blockers, or browser limitations.</p>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">⚡</div>
                    <h3 class="category-title">Enhanced Conversions</h3>
                    <p class="category-description">Improve match rates by up to 30% with enhanced conversion data, advanced matching algorithms, and customer information sharing across platforms.</p>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">📊</div>
                    <h3 class="category-title">Advanced Analytics</h3>
                    <p class="category-description">Monitor performance across all platforms from one unified dashboard with real-time analytics, attribution modeling, and comprehensive ROI tracking.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Features -->
    <section class="features-section">
        <div class="container">
            <div class="feature-detail">
                <div class="feature-content">
                    <h3><span>🔒</span> Server-Side Tracking</h3>
                    <p>Overcome iOS 14.5+ restrictions and browser limitations with our enterprise-grade server-side tracking infrastructure. Send conversion data directly from our secure servers to maintain 99% tracking accuracy regardless of device restrictions.</p>
                    
                    <ul class="feature-benefits">
                        <li>Bypass iOS 14.5+ App Tracking Transparency limitations</li>
                        <li>Immune to ad blockers and browser restrictions</li>
                        <li>99.9% uptime with global server infrastructure</li>
                        <li>GDPR compliant data processing and storage</li>
                        <li>Real-time data synchronization across all platforms</li>
                        <li>Advanced data validation and deduplication</li>
                        <li>Custom server endpoints for maximum reliability</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="feature-mockup">
                        <div class="mockup-header">
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                        </div>
                        <div class="mockup-content">
                            <div class="mockup-stat">
                                <span class="mockup-stat-label">Server Status</span>
                                <span class="mockup-stat-value" style="color: var(--success);">● Online</span>
                            </div>
                            <div class="mockup-bar primary"></div>
                            <div class="mockup-bar success"></div>
                            <div class="mockup-bar warning"></div>
                            <div class="mockup-status">Processing 1,247 events/min</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feature-detail">
                <div class="feature-content">
                    <h3><span>⚡</span> Enhanced Conversions</h3>
                    <p>Improve your conversion match rates by up to 30% with enhanced conversion data that includes customer information, advanced matching signals, and detailed purchase attribution across all advertising platforms.</p>
                    
                    <ul class="feature-benefits">
                        <li>Customer email and phone number hashing</li>
                        <li>Advanced matching algorithms and signals</li>
                        <li>Purchase attribution and customer journey mapping</li>
                        <li>Cross-device tracking and identification</li>
                        <li>Customer lifetime value calculations</li>
                        <li>Behavioral data enrichment</li>
                        <li>First-party data integration</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="feature-mockup">
                        <div class="mockup-header">
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                        </div>
                        <div class="mockup-content">
                            <div class="mockup-stat">
                                <span class="mockup-stat-label">Match Rate</span>
                                <span class="mockup-stat-value" style="color: var(--success);">87%</span>
                            </div>
                            <div class="mockup-bar success" style="width: 87%;"></div>
                            <div class="mockup-stat">
                                <span class="mockup-stat-label">Data Quality</span>
                                <span class="mockup-stat-value" style="color: var(--primary);">94%</span>
                            </div>
                            <div class="mockup-bar primary" style="width: 94%;"></div>
                            <div class="mockup-status">Enhanced conversions active</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feature-detail">
                <div class="feature-content">
                    <h3><span>🧪</span> A/B Testing & Optimization</h3>
                    <p>Test different tracking configurations, conversion values, and attribution models to find what works best for your specific business and audience. Make data-driven decisions to optimize your conversion tracking performance.</p>
                    
                    <ul class="feature-benefits">
                        <li>Split test tracking configurations and setups</li>
                        <li>Conversion value and attribution optimization</li>
                        <li>Attribution model testing and comparison</li>
                        <li>Statistical significance tracking and analysis</li>
                        <li>Automated winner selection and implementation</li>
                        <li>Performance impact measurement</li>
                        <li>Custom experiment design and execution</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="feature-mockup">
                        <div class="mockup-header">
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                        </div>
                        <div class="mockup-content">
                            <div class="mockup-stat">
                                <span class="mockup-stat-label">Variant A</span>
                                <span class="mockup-stat-value" style="color: var(--success);">+12%</span>
                            </div>
                            <div class="mockup-bar success" style="width: 55%;"></div>
                            <div class="mockup-stat">
                                <span class="mockup-stat-label">Variant B</span>
                                <span class="mockup-stat-value" style="color: var(--warning);">-3%</span>
                            </div>
                            <div class="mockup-bar warning" style="width: 45%;"></div>
                            <div class="mockup-status">Winner: Variant A (95% confidence)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feature-detail">
                <div class="feature-content">
                    <h3><span>🎯</span> Custom Events & Rules</h3>
                    <p>Create sophisticated custom events and conversion value rules tailored to your business model. Track specific actions that matter most to your bottom line with advanced trigger conditions and dynamic value calculations.</p>
                    
                    <ul class="feature-benefits">
                        <li>Custom event creation and management system</li>
                        <li>Advanced trigger conditions and logic</li>
                        <li>Dynamic conversion value rules and calculations</li>
                        <li>Product-specific and category-based tracking</li>
                        <li>Customer segment targeting and personalization</li>
                        <li>Revenue optimization rules and algorithms</li>
                        <li>Behavioral event tracking and analysis</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="feature-mockup">
                        <div class="mockup-header">
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                            <div class="mockup-dot"></div>
                        </div>
                        <div class="mockup-content">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <div style="width: 8px; height: 8px; background: var(--success); border-radius: 50%;"></div>
                                <span style="font-size: 0.75rem;">High Value Cart (>$200)</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%;"></div>
                                <span style="font-size: 0.75rem;">Newsletter Signup</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                                <div style="width: 8px; height: 8px; background: var(--warning); border-radius: 50%;"></div>
                                <span style="font-size: 0.75rem;">Product Review Left</span>
                            </div>
                            <div class="mockup-status">12 custom events configured</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Integration -->
    <section class="platforms-section">
        <div class="container">
            <h2 class="section-title">Supported Platforms</h2>
            <p class="section-subtitle">Connect with every major advertising platform from one unified dashboard</p>
            
            <div class="platforms-tabs">
                <div class="platform-tab active">All Platforms</div>
                <div class="platform-tab">Social Media</div>
                <div class="platform-tab">Search & Shopping</div>
                <div class="platform-tab">Analytics & Data</div>
            </div>
            
            <div class="platforms-grid">
                <div class="platform-card">
                    <div class="platform-icon" style="background: #1877f2;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/meta.svg" alt="Meta Facebook">
                    </div>
                    <div class="platform-name">Meta (Facebook)</div>
                    <div class="platform-features">
                        <span class="platform-feature server">Server-Side</span>
                        <span class="platform-feature enhanced">CAPI</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #4285F4;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/googleads.svg" alt="Google Ads">
                    </div>
                    <div class="platform-name">Google Ads</div>
                    <div class="platform-features">
                        <span class="platform-feature enhanced">Enhanced</span>
                        <span class="platform-feature">Offline</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #E37400;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/googleanalytics.svg" alt="Google Analytics">
                    </div>
                    <div class="platform-name">Google Analytics 4</div>
                    <div class="platform-features">
                        <span class="platform-feature server">GA4 + MP</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #000000;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/tiktok.svg" alt="TikTok">
                    </div>
                    <div class="platform-name">TikTok</div>
                    <div class="platform-features">
                        <span class="platform-feature server">Events API</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #BD081C;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/pinterest.svg" alt="Pinterest">
                    </div>
                    <div class="platform-name">Pinterest</div>
                    <div class="platform-features">
                        <span class="platform-feature server">Conv. API</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #FFFC00;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/snapchat.svg" alt="Snapchat">
                    </div>
                    <div class="platform-name">Snapchat</div>
                    <div class="platform-features">
                        <span class="platform-feature server">CAPI</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #0A66C2;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/linkedin.svg" alt="LinkedIn">
                    </div>
                    <div class="platform-name">LinkedIn Ads</div>
                    <div class="platform-features">
                        <span class="platform-feature">Insight Tag</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #000000;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/x.svg" alt="X Twitter">
                    </div>
                    <div class="platform-name">X (Twitter)</div>
                    <div class="platform-features">
                        <span class="platform-feature">Website Tag</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #00a82d;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/microsoftbing.svg" alt="Microsoft Ads">
                    </div>
                    <div class="platform-name">Microsoft Ads</div>
                    <div class="platform-features">
                        <span class="platform-feature">UET</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #FF9900;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/amazon.svg" alt="Amazon">
                    </div>
                    <div class="platform-name">Amazon DSP</div>
                    <div class="platform-features">
                        <span class="platform-feature">Attribution</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #000000;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/klaviyo.svg" alt="Klaviyo">
                    </div>
                    <div class="platform-name">Klaviyo</div>
                    <div class="platform-features">
                        <span class="platform-feature enhanced">Native</span>
                    </div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-icon" style="background: #246FDB;">
                        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/googletagmanager.svg" alt="GTM">
                    </div>
                    <div class="platform-name">Google Tag Manager</div>
                    <div class="platform-features">
                        <span class="platform-feature server">Server GTM</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Track Better?</h2>
            <p class="cta-subtitle">Get started with Algoboost and see the difference advanced tracking makes for your Shopify store</p>
            <div>
                <a href="https://apps.shopify.com/algoboost" class="btn btn-white">Start Free Trial</a>
                <a href="<?php echo home_url('/pricing'); ?>" class="btn btn-outline">View Pricing</a>
            </div>
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

    <script>
        // Platform tabs functionality
        document.querySelectorAll('.platform-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active from all tabs
                document.querySelectorAll('.platform-tab').forEach(t => t.classList.remove('active'));
                // Add active to clicked tab
                this.classList.add('active');
                
                // Filter platforms (simplified for demo)
                const category = this.textContent;
                console.log('Filter by:', category);
            });
        });

        // Animation on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        // Apply animations to cards
        document.querySelectorAll('.category-card, .feature-detail, .platform-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
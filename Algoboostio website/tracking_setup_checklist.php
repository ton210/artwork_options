<?php /* Template Name: Tracking Setup Checklist */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify Tracking Setup Checklist - Algoboost | Complete Implementation Guide</title>
    <meta name="description" content="Step-by-step checklist to ensure your Shopify store has optimal conversion tracking across all major advertising platforms. Free download.">
    <meta name="keywords" content="shopify tracking checklist, conversion tracking setup, facebook pixel checklist, google ads tracking setup, shopify analytics">

    <!-- Open Graph -->
    <meta property="og:title" content="Tracking Setup Checklist - Algoboost">
    <meta property="og:description" content="Step-by-step checklist for optimal Shopify conversion tracking setup.">
    <meta property="og:image" content="<?php echo home_url('/images/checklist-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/resources/tracking-checklist'); ?>">

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
            background: linear-gradient(135deg, #10b981, #059669);
            padding: 6rem 2rem;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        .hero-features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .hero-feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        /* Resource Details */
        .resource-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 4rem;
            margin-bottom: 4rem;
        }

        .resource-main {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .resource-sidebar {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 2rem;
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .sidebar-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .download-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .download-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .download-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .download-meta {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .resource-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--gray-100);
            border-radius: 10px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        /* Checklist Preview */
        .checklist-preview {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-dark);
        }

        .checklist-sections {
            display: grid;
            gap: 2rem;
        }

        .checklist-section {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 2rem;
            border-left: 4px solid var(--success);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .section-number {
            background: var(--success);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .section-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .checklist-items {
            display: grid;
            gap: 0.75rem;
        }

        .checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: var(--transition);
        }

        .checklist-item:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }

        .checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-200);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .checkbox.checked {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }

        .item-content {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-dark);
        }

        .item-description {
            font-size: 0.9rem;
            color: var(--text-light);
            line-height: 1.4;
        }

        .progress-bar {
            background: var(--gray-200);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-fill {
            background: linear-gradient(90deg, var(--success), #34d399);
            height: 100%;
            width: 0%;
            transition: width 0.5s ease;
        }

        .progress-text {
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        /* Benefits Section */
        .benefits-section {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 4rem;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .benefit-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .benefit-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .benefit-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .benefit-description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
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

            .resource-details {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .resource-sidebar {
                position: static;
            }

            .hero-features {
                flex-direction: column;
                align-items: center;
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
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Tracking Setup Checklist</h1>
            <p class="hero-subtitle">
                Step-by-step checklist to ensure your Shopify store has optimal conversion tracking across all major advertising platforms. Never miss a critical setup step again.
            </p>
            
            <div class="hero-features">
                <div class="hero-feature">50+ Checkpoints</div>
                <div class="hero-feature">18 Platforms</div>
                <div class="hero-feature">Interactive Format</div>
                <div class="hero-feature">Free Download</div>
            </div>
            
            <a href="#download" class="btn" style="background: white; color: var(--success); font-size: 1.1rem; padding: 1rem 2rem;">Get Free Checklist</a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Resource Details -->
        <section class="resource-details">
            <div class="resource-main">
                <h2 style="font-size: 2rem; margin-bottom: 1.5rem;">Never Miss a Critical Tracking Setup Step</h2>
                
                <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem;">
                    Proper tracking setup is critical for advertising success, but it's easy to miss important 
                    configuration details. This comprehensive checklist covers every essential step for setting up 
                    tracking across all major advertising platforms.
                </p>

                <h3 style="margin-bottom: 1rem; color: var(--text-dark);">What's Covered</h3>
                <ul style="margin-bottom: 2rem; color: var(--text-light); line-height: 1.8;">
                    <li>Pre-setup preparation and account requirements</li>
                    <li>Platform-specific tracking code installation</li>
                    <li>Event configuration and custom conversions</li>
                    <li>Testing and validation procedures</li>
                    <li>Server-side tracking implementation checkpoints</li>
                    <li>Privacy compliance verification</li>
                    <li>Performance monitoring setup</li>
                    <li>Troubleshooting common issues</li>
                </ul>

                <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Interactive Format</h3>
                <p style="color: var(--text-light); margin-bottom: 2rem;">
                    This isn't just a PDF - it's an interactive checklist that tracks your progress as you 
                    complete each step. Check off items as you go and see your overall completion percentage.
                </p>

                <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 2rem; border-radius: 15px; margin-top: 2rem;">
                    <h3 style="color: var(--success); margin-bottom: 1rem;">✅ Why Use a Checklist?</h3>
                    <p style="color: var(--text-light); margin-bottom: 0;">
                        Studies show that using checklists reduces errors by up to 80% in complex procedures. 
                        Our checklist is based on reviewing 5,000+ Shopify store setups and identifying the 
                        most commonly missed steps.
                    </p>
                </div>
            </div>

            <div class="resource-sidebar" id="download">
                <div class="download-card">
                    <span class="download-icon">✅</span>
                    <h3 class="download-title">Tracking Setup Checklist</h3>
                    <div class="download-meta">
                        Interactive PDF • 15 Pages<br>
                        Updated January 2025
                    </div>
                    
                    <div class="resource-stats">
                        <div class="stat-item">
                            <div class="stat-value">50+</div>
                            <div class="stat-label">Checkpoints</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">18</div>
                            <div class="stat-label">Platforms</div>
                        </div>
                    </div>
                    
                    <form onsubmit="handleDownload(event)" style="margin-bottom: 1rem;">
                        <input type="email" placeholder="Enter your email" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Download Free Checklist
                        </button>
                    </form>
                    
                    <p style="font-size: 0.8rem; color: var(--text-light); text-align: center;">
                        No spam. Unsubscribe anytime.
                    </p>
                </div>
                
                <div class="sidebar-title">Related Resources</div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="<?php echo home_url('/resources/server-side-playbook'); ?>" style="text-decoration: none; color: var(--text-dark); padding: 1rem; background: white; border-radius: 10px; transition: var(--transition);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Server-Side Playbook</div>
                        <div style="font-size: 0.8rem; color: var(--text-light);">60-page implementation guide</div>
                    </a>
                    <a href="<?php echo home_url('/resources/tracking-roi-calculator'); ?>" style="text-decoration: none; color: var(--text-dark); padding: 1rem; background: white; border-radius: 10px; transition: var(--transition);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">ROI Calculator</div>
                        <div style="font-size: 0.8rem; color: var(--text-light);">Estimate tracking impact</div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Interactive Checklist Preview -->
        <section class="checklist-preview">
            <h2 class="section-title">Interactive Checklist Preview</h2>
            <div class="progress-text">Complete: <span id="progressText">0 of 12</span> items</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            
            <div class="checklist-sections">
                <div class="checklist-section">
                    <div class="section-header">
                        <div class="section-number">1</div>
                        <h3 class="section-name">Pre-Setup Preparation</h3>
                    </div>
                    <div class="checklist-items">
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Verify Shopify Admin Access</div>
                                <div class="item-description">Ensure you have admin access to your Shopify store and can access the Settings menu</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Document Current Tracking Setup</div>
                                <div class="item-description">Create a backup of existing tracking codes and document current configurations</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Gather All Account IDs</div>
                                <div class="item-description">Collect pixel IDs, tracking IDs, and account numbers for all advertising platforms</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checklist-section">
                    <div class="section-header">
                        <div class="section-number">2</div>
                        <h3 class="section-name">Facebook & Meta Setup</h3>
                    </div>
                    <div class="checklist-items">
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Install Facebook Pixel</div>
                                <div class="item-description">Add Facebook Pixel code to your Shopify store using the preferred method</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Configure Standard Events</div>
                                <div class="item-description">Set up PageView, ViewContent, AddToCart, InitiateCheckout, and Purchase events</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Enable Conversions API</div>
                                <div class="item-description">Implement server-side tracking through Facebook Conversions API integration</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checklist-section">
                    <div class="section-header">
                        <div class="section-number">3</div>
                        <h3 class="section-name">Google Ads & Analytics</h3>
                    </div>
                    <div class="checklist-items">
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Install Google Analytics 4</div>
                                <div class="item-description">Set up GA4 property and configure enhanced ecommerce tracking</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Configure Google Ads Conversion Tracking</div>
                                <div class="item-description">Set up conversion actions and link Google Ads to Google Analytics</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Enable Enhanced Conversions</div>
                                <div class="item-description">Implement Enhanced Conversions for Google Ads for better attribution</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checklist-section">
                    <div class="section-header">
                        <div class="section-number">4</div>
                        <h3 class="section-name">Testing & Validation</h3>
                    </div>
                    <div class="checklist-items">
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Test All Event Firing</div>
                                <div class="item-description">Use browser developer tools to verify all tracking events are firing correctly</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Validate Data in Platforms</div>
                                <div class="item-description">Check that conversion data is appearing in Facebook Ads Manager and Google Ads</div>
                            </div>
                        </div>
                        <div class="checklist-item" onclick="toggleCheckbox(this)">
                            <div class="checkbox"></div>
                            <div class="item-content">
                                <div class="item-title">Monitor for 48 Hours</div>
                                <div class="item-description">Allow 24-48 hours for data to populate and verify tracking accuracy across all platforms</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="benefits-section">
            <h2 class="section-title">Why Use Our Checklist?</h2>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">🎯</div>
                    <h3 class="benefit-title">Reduce Setup Errors</h3>
                    <p class="benefit-description">
                        Eliminate common tracking mistakes that can cost you thousands in lost attribution data and wasted ad spend.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">⚡</div>
                    <h3 class="benefit-title">Faster Implementation</h3>
                    <p class="benefit-description">
                        Save hours of setup time with our organized, step-by-step approach covering all major platforms.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📊</div>
                    <h3 class="benefit-title">Better Attribution</h3>
                    <p class="benefit-description">
                        Ensure accurate tracking across all touchpoints for complete customer journey visibility.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🔒</div>
                    <h3 class="benefit-title">Compliance Ready</h3>
                    <p class="benefit-description">
                        Built-in privacy compliance checks ensure your tracking setup meets GDPR and other regulations.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🔧</div>
                    <h3 class="benefit-title">Troubleshooting Included</h3>
                    <p class="benefit-description">
                        Common issue resolution steps help you quickly identify and fix tracking problems.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📈</div>
                    <h3 class="benefit-title">Improved ROAS</h3>
                    <p class="benefit-description">
                        Proper tracking leads to better optimization and typically 20-40% improvement in advertising returns.
                    </p>
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
        let checkedCount = 0;
        const totalItems = 12;

        function toggleCheckbox(item) {
            const checkbox = item.querySelector('.checkbox');
            const isChecked = checkbox.classList.contains('checked');
            
            if (isChecked) {
                checkbox.classList.remove('checked');
                checkbox.innerHTML = '';
                checkedCount--;
            } else {
                checkbox.classList.add('checked');
                checkbox.innerHTML = '✓';
                checkedCount++;
            }
            
            updateProgress();
        }

        function updateProgress() {
            const percentage = (checkedCount / totalItems) * 100;
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            progressFill.style.width = percentage + '%';
            progressText.textContent = `${checkedCount} of ${totalItems}`;
        }

        function handleDownload(event) {
            event.preventDefault();
            
            const email = event.target.querySelector('input[type="email"]').value;
            console.log('Checklist download requested for:', email);
            
            // Show success message
            const btn = event.target.querySelector('button');
            const originalText = btn.textContent;
            btn.textContent = 'Download Started! ✓';
            btn.style.background = '#10b981';
            
            // Simulate download
            setTimeout(() => {
                // In a real implementation, this would trigger the actual PDF download
                window.open('#', '_blank');
            }, 1000);
            
            // Reset form
            event.target.reset();
            
            // Reset button after 3 seconds
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '';
            }, 3000);
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
                
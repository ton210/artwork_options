<?php /* Template Name: Server-Side Tracking Playbook */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Side Tracking Playbook - Algoboost | Complete 60-Page Implementation Guide</title>
    <meta name="description" content="Download our comprehensive 60-page server-side tracking playbook covering Facebook CAPI, Google Enhanced Conversions, TikTok Events API, and more platform integrations.">
    <meta name="keywords" content="server-side tracking guide, facebook conversions api, google enhanced conversions, tiktok events api, shopify server tracking">

    <!-- Open Graph -->
    <meta property="og:title" content="Server-Side Tracking Playbook - Algoboost">
    <meta property="og:description" content="Complete 60-page guide covering Facebook CAPI, Google Enhanced Conversions, TikTok Events API, and more.">
    <meta property="og:image" content="<?php echo home_url('/images/playbook-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/resources/server-side-playbook'); ?>">

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
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        /* Table of Contents */
        .toc-section {
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

        .toc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .toc-chapter {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 2rem;
            border-left: 4px solid var(--primary);
        }

        .chapter-number {
            background: var(--primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .chapter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
        }

        .chapter-description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* What's Inside Section */
        .whats-inside {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 4rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .feature-description {
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

            .toc-grid {
                grid-template-columns: 1fr;
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
            <h1>Server-Side Tracking Playbook</h1>
            <p class="hero-subtitle">
                The complete 60-page implementation guide covering Facebook Conversions API, Google Enhanced Conversions, 
                TikTok Events API, and 15+ other platform integrations with step-by-step tutorials and code examples.
            </p>
            
            <div class="hero-features">
                <div class="hero-feature">60 Pages</div>
                <div class="hero-feature">18 Platforms</div>
                <div class="hero-feature">Code Examples</div>
                <div class="hero-feature">Free Download</div>
            </div>
            
            <a href="#download" class="btn" style="background: white; color: var(--primary); font-size: 1.1rem; padding: 1rem 2rem;">Download Free PDF</a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Resource Details -->
        <section class="resource-details">
            <div class="resource-main">
                <h2 style="font-size: 2rem; margin-bottom: 1.5rem;">Master Server-Side Tracking for Maximum Attribution</h2>
                
                <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem;">
                    In the post-iOS 14.5 world, client-side tracking has become unreliable. This comprehensive playbook 
                    teaches you how to implement server-side tracking across all major advertising platforms to maintain 
                    accurate attribution and optimize your advertising spend.
                </p>

                <h3 style="margin-bottom: 1rem; color: var(--text-dark);">What You'll Learn</h3>
                <ul style="margin-bottom: 2rem; color: var(--text-light); line-height: 1.8;">
                    <li>Complete Facebook Conversions API implementation with advanced matching</li>
                    <li>Google Enhanced Conversions setup for improved attribution accuracy</li>
                    <li>TikTok Events API integration for better campaign optimization</li>
                    <li>Pinterest Conversions API and Snapchat CAPI implementation</li>
                    <li>Server-side tracking architecture and best practices</li>
                    <li>Customer data hashing and privacy compliance strategies</li>
                    <li>Advanced debugging techniques and troubleshooting guides</li>
                    <li>Performance optimization and scaling considerations</li>
                </ul>

                <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Who This Is For</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍💼</div>
                        <div style="font-weight: 600;">E-commerce Managers</div>
                    </div>
                    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👩‍💻</div>
                        <div style="font-weight: 600;">Technical Marketers</div>
                    </div>
                    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏪</div>
                        <div style="font-weight: 600;">Shopify Store Owners</div>
                    </div>
                    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏢</div>
                        <div style="font-weight: 600;">Marketing Agencies</div>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, #e0f2fe, #f3e5f5); padding: 2rem; border-radius: 15px; margin-top: 2rem;">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">💡 Expert Insights Included</h3>
                    <p style="color: var(--text-light); margin-bottom: 0;">
                        This playbook includes real-world case studies, performance benchmarks, and insights from 
                        implementing server-side tracking for over 5,000 Shopify stores across various industries.
                    </p>
                </div>
            </div>

            <div class="resource-sidebar" id="download">
                <div class="download-card">
                    <span class="download-icon">📖</span>
                    <h3 class="download-title">Server-Side Tracking Playbook</h3>
                    <div class="download-meta">
                        PDF Guide • 60 Pages<br>
                        Updated January 2025
                    </div>
                    
                    <div class="resource-stats">
                        <div class="stat-item">
                            <div class="stat-value">60</div>
                            <div class="stat-label">Pages</div>
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
                            Download Free PDF
                        </button>
                    </form>
                    
                    <p style="font-size: 0.8rem; color: var(--text-light); text-align: center;">
                        No spam. Unsubscribe anytime.
                    </p>
                </div>
                
                <div class="sidebar-title">Also Available</div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="<?php echo home_url('/resources/tracking-checklist'); ?>" style="text-decoration: none; color: var(--text-dark); padding: 1rem; background: white; border-radius: 10px; transition: var(--transition);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Tracking Setup Checklist</div>
                        <div style="font-size: 0.8rem; color: var(--text-light);">Step-by-step setup guide</div>
                    </a>
                    <a href="<?php echo home_url('/resources/attribution-tool'); ?>" style="text-decoration: none; color: var(--text-dark); padding: 1rem; background: white; border-radius: 10px; transition: var(--transition);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Attribution Comparison Tool</div>
                        <div style="font-size: 0.8rem; color: var(--text-light);">Spreadsheet template</div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Table of Contents -->
        <section class="toc-section">
            <h2 class="section-title">What's Inside the Playbook</h2>
            
            <div class="toc-grid">
                <div class="toc-chapter">
                    <div class="chapter-number">1</div>
                    <h3 class="chapter-title">Server-Side Tracking Fundamentals</h3>
                    <p class="chapter-description">
                        Understanding the shift from client-side to server-side tracking, benefits, challenges, 
                        and architectural considerations for e-commerce businesses.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">2</div>
                    <h3 class="chapter-title">Facebook Conversions API</h3>
                    <p class="chapter-description">
                        Complete implementation guide including access token setup, event configuration, 
                        advanced matching, and troubleshooting common issues.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">3</div>
                    <h3 class="chapter-title">Google Enhanced Conversions</h3>
                    <p class="chapter-description">
                        Step-by-step setup for Google Ads Enhanced Conversions, customer data handling, 
                        and integration with Google Analytics 4.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">4</div>
                    <h3 class="chapter-title">TikTok Events API</h3>
                    <p class="chapter-description">
                        TikTok server-side tracking implementation, event mapping, and optimization 
                        strategies for TikTok advertising campaigns.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">5</div>
                    <h3 class="chapter-title">Pinterest & Snapchat APIs</h3>
                    <p class="chapter-description">
                        Pinterest Conversions API and Snapchat Conversions API setup with detailed 
                        implementation examples and best practices.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">6</div>
                    <h3 class="chapter-title">Multi-Platform Integration</h3>
                    <p class="chapter-description">
                        Advanced strategies for managing multiple server-side integrations, 
                        data consistency, and unified customer journey tracking.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">7</div>
                    <h3 class="chapter-title">Privacy & Compliance</h3>
                    <p class="chapter-description">
                        GDPR compliance, data hashing techniques, customer consent management, 
                        and privacy-first tracking implementations.
                    </p>
                </div>

                <div class="toc-chapter">
                    <div class="chapter-number">8</div>
                    <h3 class="chapter-title">Testing & Optimization</h3>
                    <p class="chapter-description">
                        Debugging tools, testing frameworks, performance monitoring, 
                        and continuous optimization strategies.
                    </p>
                </div>
            </div>
        </section>

        <!-- What's Inside Features -->
        <section class="whats-inside">
            <h2 class="section-title">Bonus Resources Included</h2>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">💻</div>
                    <h3 class="feature-title">Code Examples</h3>
                    <p class="feature-description">
                        Ready-to-use code snippets for all major platforms with detailed explanations 
                        and customization options.
                    </p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">🔧</div>
                    <h3 class="feature-title">Setup Scripts</h3>
                    <p class="feature-description">
                        Automated setup scripts and configuration templates to speed up your 
                        implementation process.
                    </p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Testing Checklist</h3>
                    <p class="feature-description">
                        Comprehensive testing checklist to verify your server-side tracking 
                        implementation is working correctly.
                    </p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">🚨</div>
                    <h3 class="feature-title">Troubleshooting Guide</h3>
                    <p class="feature-description">
                        Common issues and solutions for server-side tracking problems with 
                        step-by-step resolution guides.
                    </p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">📈</div>
                    <h3 class="feature-title">Case Studies</h3>
                    <p class="feature-description">
                        Real-world implementation examples with before/after performance metrics 
                        and lessons learned.
                    </p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">🔄</div>
                    <h3 class="feature-title">Update Notifications</h3>
                    <p class="feature-description">
                        Get notified when platform APIs change or new server-side tracking 
                        opportunities become available.
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
        function handleDownload(event) {
            event.preventDefault();
            
            const email = event.target.querySelector('input[type="email"]').value;
            console.log('Playbook download requested for:', email);
            
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
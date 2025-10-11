<?php /* Template Name: Algoboost Documentation */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - Algoboost Advanced Shopify Conversion Tracking</title>
    <meta name="description" content="Complete documentation for Algoboost's advanced Shopify conversion tracking, server-side tracking setup, and platform integrations.">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --text-dark: #1a202c;
            --text-light: #718096;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --radius: 0.75rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--bg-light);
        }

        /* Navigation - Same as homepage */
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header {
            background: white;
            padding: 8rem 0 2rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .page-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 600px;
        }

        .main {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 3rem;
            padding: 3rem 0;
        }

        .sidebar {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .nav-links-sidebar {
            list-style: none;
        }

        .nav-link-sidebar {
            display: block;
            padding: 0.5rem 0;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.875rem;
            transition: var(--transition);
            border-left: 2px solid transparent;
            padding-left: 1rem;
        }

        .nav-link-sidebar:hover,
        .nav-link-sidebar.active {
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .content {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 3rem;
        }

        .content h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 2rem 0 1rem;
            color: var(--text-dark);
        }

        .content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.5rem 0 0.75rem;
            color: var(--text-dark);
        }

        .content p {
            margin-bottom: 1rem;
            color: var(--text-light);
            line-height: 1.7;
        }

        .content ul,
        .content ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
            color: var(--text-light);
        }

        .content li {
            margin-bottom: 0.5rem;
        }

        /* Footer - Same as homepage */
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

        .code-block {
            background: var(--gray-100);
            padding: 1rem;
            border-radius: var(--radius);
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            margin: 1rem 0;
            border: 1px solid var(--gray-200);
        }

        .inline-code {
            background: var(--gray-100);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.875rem;
            color: var(--primary);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin: 1rem 0;
            border: 1px solid;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
            color: #1e40af;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
            color: #92400e;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
            color: #047857;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .feature-card {
            background: var(--gray-100);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .feature-card h3 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .feature-card p {
            margin: 0;
            font-size: 0.875rem;
        }

        .step-list {
            counter-reset: step-counter;
            list-style: none;
            padding-left: 0;
        }

        .step-list li {
            counter-increment: step-counter;
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 3rem;
        }

        .step-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background: var(--primary);
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table-container {
            overflow-x: auto;
            margin: 1rem 0;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: var(--gray-100);
            font-weight: 600;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .main {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .sidebar {
                position: relative;
                top: 0;
            }
            
            .content {
                padding: 2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="logo">
                <div class="logo-icon">🎯</div>
                <span>Algoboost</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/features">Features</a></li>
                <li><a href="/pricing">Pricing</a></li>
                <li><a href="/about">About</a></li>
                <li><a href="/documentation" class="active">Docs</a></li>
                <li><a href="/blog">Blog</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <div class="header">
        <div class="container">
            <h1 class="page-title">Documentation</h1>
            <p class="page-subtitle">Complete guide to advanced Shopify conversion tracking with Algoboost</p>
        </div>
    </div>

    <div class="container">
        <div class="main">
            <aside class="sidebar">
                <div class="nav-section">
                    <div class="nav-section-title">Getting Started</div>
                    <ul class="nav-links-sidebar">
                        <li><a href="#introduction" class="nav-link-sidebar active">Introduction</a></li>
                        <li><a href="#installation" class="nav-link-sidebar">Installation</a></li>
                        <li><a href="#quick-start" class="nav-link-sidebar">Quick Start</a></li>
                        <li><a href="#first-pixel" class="nav-link-sidebar">Adding Your First Pixel</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Platform Guides</div>
                    <ul class="nav-links-sidebar">
                        <li><a href="#facebook-meta" class="nav-link-sidebar">Facebook (Meta)</a></li>
                        <li><a href="#google-ads" class="nav-link-sidebar">Google Ads</a></li>
                        <li><a href="#tiktok" class="nav-link-sidebar">TikTok</a></li>
                        <li><a href="#pinterest" class="nav-link-sidebar">Pinterest</a></li>
                        <li><a href="#google-analytics" class="nav-link-sidebar">Google Analytics 4</a></li>
                        <li><a href="#linkedin-ads" class="nav-link-sidebar">LinkedIn Ads</a></li>
                        <li><a href="#snapchat" class="nav-link-sidebar">Snapchat</a></li>
                        <li><a href="#microsoft-ads" class="nav-link-sidebar">Microsoft Ads</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Advanced Features</div>
                    <ul class="nav-links-sidebar">
                        <li><a href="#server-side" class="nav-link-sidebar">Server-Side Tracking</a></li>
                        <li><a href="#enhanced-conversions" class="nav-link-sidebar">Enhanced Conversions</a></li>
                        <li><a href="#custom-events" class="nav-link-sidebar">Custom Events</a></li>
                        <li><a href="#conversion-rules" class="nav-link-sidebar">Conversion Rules</a></li>
                        <li><a href="#ab-testing" class="nav-link-sidebar">A/B Testing</a></li>
                        <li><a href="#attribution-models" class="nav-link-sidebar">Attribution Models</a></li>
                        <li><a href="#api-access" class="nav-link-sidebar">API Access</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Support</div>
                    <ul class="nav-links-sidebar">
                        <li><a href="#common-issues" class="nav-link-sidebar">Common Issues</a></li>
                        <li><a href="#testing-tracking" class="nav-link-sidebar">Testing Your Setup</a></li>
                        <li><a href="#data-discrepancies" class="nav-link-sidebar">Data Discrepancies</a></li>
                        <li><a href="#gdpr-compliance" class="nav-link-sidebar">GDPR Compliance</a></li>
                        <li><a href="#performance-optimization" class="nav-link-sidebar">Performance Optimization</a></li>
                    </ul>
                </div>
            </aside>

            <main class="content">
                <section id="introduction">
                    <h1>Introduction to Algoboost</h1>
                    <p>Algoboost is a comprehensive conversion tracking platform designed specifically for Shopify stores. It addresses the challenges created by iOS 14.5+ privacy updates by providing server-side tracking, enhanced conversions, and advanced attribution modeling.</p>

                    <div class="alert alert-info">
                        <strong>New to conversion tracking?</strong> Start with our <a href="#quick-start">Quick Start Guide</a> to get up and running in under 10 minutes.
                    </div>

                    <h2>Why Algoboost?</h2>
                    <p>Traditional browser-based tracking has become increasingly unreliable due to:</p>
                    <ul>
                        <li>iOS 14.5+ App Tracking Transparency restrictions</li>
                        <li>Browser privacy updates and ad blocker interference</li>
                        <li>Cookie deprecation and third-party tracking limitations</li>
                        <li>Cross-device attribution challenges</li>
                        <li>Intelligent Tracking Prevention (ITP) blocking</li>
                        <li>Consent management complications</li>
                    </ul>

                    <p>Algoboost solves these problems with:</p>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>🔒 Server-Side Tracking</h3>
                            <p>Bypass browser restrictions with direct server-to-server data transmission. Maintain 99% tracking accuracy regardless of privacy settings.</p>
                        </div>
                        <div class="feature-card">
                            <h3>⚡ Enhanced Conversions</h3>
                            <p>Improve match rates with first-party customer data and advanced matching algorithms across all supported platforms.</p>
                        </div>
                        <div class="feature-card">
                            <h3>📊 Multi-Platform Support</h3>
                            <p>Track across 18+ platforms with unified analytics and attribution. Single dashboard for all your advertising efforts.</p>
                        </div>
                        <div class="feature-card">
                            <h3>🧪 A/B Testing</h3>
                            <p>Optimize tracking configurations and conversion strategies with built-in A/B testing tools.</p>
                        </div>
                        <div class="feature-card">
                            <h3>📈 Real-time Analytics</h3>
                            <p>Monitor performance in real-time with custom dashboards and automated reports.</p>
                        </div>
                        <div class="feature-card">
                            <h3>🔐 GDPR Compliant</h3>
                            <p>Built-in privacy compliance with automatic consent management and data retention controls.</p>
                        </div>
                    </div>

                    <h2>Supported Platforms</h2>
                    <p>Algoboost integrates with all major advertising and analytics platforms:</p>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Server-Side</th>
                                    <th>Enhanced Conversions</th>
                                    <th>Custom Events</th>
                                    <th>Attribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Facebook (Meta)</td>
                                    <td>Conversions API</td>
                                    <td>Advanced Matching</td>
                                    <td>Custom Parameters</td>
                                    <td>Cross-device</td>
                                </tr>
                                <tr>
                                    <td>Google Ads</td>
                                    <td>Enhanced Conversions</td>
                                    <td>Customer Match</td>
                                    <td>Custom Conversions</td>
                                    <td>Data-driven</td>
                                </tr>
                                <tr>
                                    <td>TikTok</td>
                                    <td>Events API</td>
                                    <td>Advanced Matching</td>
                                    <td>Custom Events</td>
                                    <td>Attribution models</td>
                                </tr>
                                <tr>
                                    <td>Google Analytics 4</td>
                                    <td>Measurement Protocol</td>
                                    <td>Enhanced Ecommerce</td>
                                    <td>Custom Dimensions</td>
                                    <td>Multi-touch</td>
                                </tr>
                                <tr>
                                    <td>Pinterest</td>
                                    <td>Conversions API</td>
                                    <td>Enhanced Match</td>
                                    <td>Custom Events</td>
                                    <td>Cross-platform</td>
                                </tr>
                                <tr>
                                    <td>LinkedIn Ads</td>
                                    <td>Insight Tag</td>
                                    <td>Enhanced Matching</td>
                                    <td>Custom Events</td>
                                    <td>B2B Attribution</td>
                                </tr>
                                <tr>
                                    <td>Snapchat Ads</td>
                                    <td>Conversions API</td>
                                    <td>Customer Lists</td>
                                    <td>Custom Events</td>
                                    <td>Mobile-first</td>
                                </tr>
                                <tr>
                                    <td>Microsoft Ads</td>
                                    <td>UET Tracking</td>
                                    <td>Customer Match</td>
                                    <td>Custom Goals</td>
                                    <td>Cross-platform</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2>Key Benefits</h2>
                    <ul>
                        <li><strong>Increase ROAS by up to 42%</strong> with accurate attribution</li>
                        <li><strong>Reduce tracking loss</strong> from 60% to under 5%</li>
                        <li><strong>Save 10+ hours per week</strong> on manual tracking setup</li>
                        <li><strong>Unified reporting</strong> across all advertising platforms</li>
                        <li><strong>Future-proof tracking</strong> against privacy changes</li>
                    </ul>
                </section>

                <section id="installation">
                    <h1>Installation</h1>
                    <p>Installing Algoboost on your Shopify store takes less than 5 minutes and requires no coding knowledge.</p>

                    <div class="alert alert-warning">
                        <strong>Before you start:</strong> Make sure you have admin access to your Shopify store and the advertising accounts you want to connect.
                    </div>

                    <h2>Step-by-Step Installation</h2>

                    <ol class="step-list">
                        <li>
                            <strong>Install from Shopify App Store</strong>
                            <p>Visit the <a href="https://apps.shopify.com/algoboost" target="_blank">Algoboost app page</a> in the Shopify App Store and click "Add app" to install it on your store.</p>
                            <div class="code-block">
Direct link: https://apps.shopify.com/algoboost
                            </div>
                        </li>
                        
                        <li>
                            <strong>Grant Permissions</strong>
                            <p>Algoboost requires the following permissions to function properly:</p>
                            <ul>
                                <li><code>read_products</code> - To access product information for conversion tracking</li>
                                <li><code>read_orders</code> - To track purchase conversions and revenue</li>
                                <li><code>write_script_tags</code> - To install the tracking script on your store</li>
                                <li><code>read_customers</code> - For enhanced conversion matching (optional)</li>
                                <li><code>read_analytics</code> - For performance reporting (optional)</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Complete Setup Wizard</strong>
                            <p>The setup wizard will guide you through:</p>
                            <ul>
                                <li>Selecting your plan (start with Free to test)</li>
                                <li>Configuring basic tracking settings</li>
                                <li>Installing the base tracking script</li>
                                <li>Testing the installation</li>
                                <li>Setting up your first pixel (optional)</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Verify Installation</strong>
                            <p>After setup, verify that tracking is working:</p>
                            <div class="code-block">
// Open browser console on any page of your store (F12)
// Look for this confirmation message:
"Algoboost tracking initialized successfully"

// You can also check the network tab for requests to:
// your-store.myshopify.com/apps/algoboost/tracking/
                            </div>
                        </li>
                    </ol>

                    <div class="alert alert-success">
                        <strong>Installation Complete!</strong> Your base tracking is now active. Continue to <a href="#first-pixel">Adding Your First Pixel</a> to start tracking conversions.
                    </div>

                    <h2>Troubleshooting Installation</h2>
                    <h3>Common Issues</h3>
                    <ul>
                        <li><strong>Permission denied:</strong> Ensure you're logged in as the store owner or have admin permissions</li>
                        <li><strong>App not loading:</strong> Try disabling browser extensions and clearing cache</li>
                        <li><strong>Script not installing:</strong> Check that your theme allows script tags</li>
                    </ul>

                    <h3>Manual Verification</h3>
                    <p>If automatic installation fails, you can manually verify:</p>
                    <div class="code-block">
1. Go to Online Store → Themes → Actions → Edit Code
2. Look for script tags containing "algoboost" in theme.liquid
3. Should see: &lt;script src="//your-app-url/tracking/base.js"&gt;&lt;/script&gt;
                    </div>
                </section>

                <section id="quick-start">
                    <h1>Quick Start Guide</h1>
                    <p>Get your first conversion tracking pixel up and running in under 10 minutes.</p>

                    <div class="alert alert-info">
                        <strong>Prerequisites:</strong> Algoboost app installed and base script active on your store.
                    </div>

                    <ol class="step-list">
                        <li>
                            <strong>Choose Your Platform</strong>
                            <p>Start with your primary advertising platform. We recommend Facebook or Google Ads for beginners.</p>
                        </li>
                        
                        <li>
                            <strong>Gather Required Information</strong>
                            <p>For Facebook:</p>
                            <ul>
                                <li>Pixel ID (from Facebook Events Manager)</li>
                                <li>Access Token (for server-side tracking - Growth plan)</li>
                            </ul>
                            <p>For Google Ads:</p>
                            <ul>
                                <li>Conversion ID (from Google Ads)</li>
                                <li>Conversion Label (for specific actions)</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Add Pixel in Algoboost</strong>
                            <p>In your Algoboost dashboard:</p>
                            <ul>
                                <li>Click "Add Pixel"</li>
                                <li>Select your platform</li>
                                <li>Enter the required information</li>
                                <li>Choose your tracking events</li>
                                <li>Save configuration</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Test Your Setup</strong>
                            <p>Verify tracking is working:</p>
                            <ul>
                                <li>Visit your store and make a test purchase</li>
                                <li>Check the Algoboost dashboard for events</li>
                                <li>Verify in your advertising platform (may take 15-30 minutes)</li>
                            </ul>
                        </li>
                    </ol>

                    <div class="alert alert-success">
                        <strong>Congratulations!</strong> Your conversion tracking is now live. Check your advertising dashboard in 24-48 hours for optimization data.
                    </div>

                    <h2>Next Steps</h2>
                    <p>Once your basic tracking is working:</p>
                    <ul>
                        <li>Set up additional platforms for comprehensive tracking</li>
                        <li>Configure custom conversion values</li>
                        <li>Enable server-side tracking (Growth plan)</li>
                        <li>Set up automated reports</li>
                        <li>Create custom events for specific actions</li>
                    </ul>
                </section>

                <section id="facebook-meta">
                    <h1>Facebook (Meta) Integration</h1>
                    <p>Complete guide to setting up Facebook and Instagram conversion tracking with Algoboost.</p>

                    <h2>Prerequisites</h2>
                    <ul>
                        <li>Facebook Business Manager account</li>
                        <li>Facebook Pixel created in Events Manager</li>
                        <li>Admin access to your Facebook ad account</li>
                        <li>Algoboost Growth+ plan (for server-side tracking)</li>
                    </ul>

                    <h2>Basic Facebook Pixel Setup</h2>

                    <ol class="step-list">
                        <li>
                            <strong>Get Your Pixel ID</strong>
                            <div class="code-block">
1. Go to Facebook Events Manager (business.facebook.com)
2. Select your pixel from the left sidebar
3. Click "Settings" tab
4. Copy the Pixel ID (15-16 digits)
Example: 123456789012345
                            </div>
                        </li>
                        
                        <li>
                            <strong>Add to Algoboost</strong>
                            <p>In Algoboost dashboard:</p>
                            <ul>
                                <li>Click "Add Pixel"</li>
                                <li>Select "Facebook (Meta)"</li>
                                <li>Enter Pixel ID: <code>123456789012345</code></li>
                                <li>Name: <code>Main Facebook Pixel</code></li>
                                <li>Choose events to track</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Configure Events</strong>
                            <p>Select which events to track:</p>
                            <ul>
                                <li><strong>Purchase</strong> - Completed orders (recommended)</li>
                                <li><strong>AddToCart</strong> - Items added to cart</li>
                                <li><strong>ViewContent</strong> - Product page views</li>
                                <li><strong>InitiateCheckout</strong> - Checkout started</li>
                                <li><strong>Lead</strong> - Newsletter signups, contact forms</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Test Basic Tracking</strong>
                            <p>Install Facebook Pixel Helper extension and verify events are firing on your store pages.</p>
                            <div class="code-block">
Chrome Extension: "Facebook Pixel Helper"
- Visit your store pages
- Green checkmark = working
- Red X = issues detected
                            </div>
                        </li>
                    </ol>

                    <h2>Server-Side Tracking (Conversions API)</h2>
                    <p>Requires Growth+ plan. Server-side tracking sends events directly from our servers to Facebook, bypassing browser limitations.</p>

                    <h3>Setup Process</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Generate Access Token</strong>
                            <div class="code-block">
1. Go to Facebook Events Manager
2. Select your pixel → Settings → Conversions API
3. Click "Generate Access Token"
4. Copy the token (starts with EAA...)
5. Keep this token secure - treat it like a password
                            </div>
                        </li>
                        
                        <li>
                            <strong>Configure in Algoboost</strong>
                            <p>In your pixel settings:</p>
                            <ul>
                                <li>Enable "Server-Side Tracking"</li>
                                <li>Paste your access token</li>
                                <li>Choose deduplication method</li>
                                <li>Test connection</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Advanced Matching</strong>
                            <p>Improve match rates by sending customer data:</p>
                            <ul>
                                <li>Email addresses (hashed)</li>
                                <li>Phone numbers (hashed)</li>
                                <li>Names (hashed)</li>
                                <li>Addresses (hashed)</li>
                            </ul>
                        </li>
                    </ol>

                    <h2>Custom Events & Parameters</h2>
                    <p>Track specific business actions with custom events:</p>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>High-Value Customers</h3>
                            <p>Track customers who make large purchases</p>
                            <div class="code-block">
Event: Purchase
Condition: Order value > $200
Parameter: customer_tier = "high_value"
                            </div>
                        </div>
                        <div class="feature-card">
                            <h3>Product Categories</h3>
                            <p>Track performance by product type</p>
                            <div class="code-block">
Event: ViewContent
Parameter: content_category = "Electronics"
Parameter: content_name = "iPhone 15"
                            </div>
                        </div>
                    </div>

                    <h2>Optimization Tips</h2>
                    <ul>
                        <li><strong>Use Purchase events</strong> for conversion campaigns</li>
                        <li><strong>Set up ViewContent</strong> for retargeting</li>
                        <li><strong>Track AddToCart</strong> for abandoned cart campaigns</li>
                        <li><strong>Enable server-side tracking</strong> for maximum accuracy</li>
                        <li><strong>Use custom audiences</strong> based on tracked events</li>
                    </ul>

                    <h2>Common Facebook Issues</h2>
                    <div class="alert alert-warning">
                        <strong>Events Not Showing:</strong> Check pixel helper, verify domain verification, ensure events have required parameters
                    </div>

                    <div class="alert alert-info">
                        <strong>Low Match Rates:</strong> Enable advanced matching, add more customer data points, verify email formatting
                    </div>
                </section>

                <section id="google-ads">
                    <h1>Google Ads Integration</h1>
                    <p>Set up comprehensive Google Ads conversion tracking with enhanced conversions and customer match.</p>

                    <h2>Prerequisites</h2>
                    <ul>
                        <li>Active Google Ads account</li>
                        <li>Admin access to Google Ads</li>
                        <li>Google Analytics 4 property (recommended)</li>
                        <li>Algoboost Starter+ plan (for enhanced conversions)</li>
                    </ul>

                    <h2>Basic Google Ads Setup</h2>

                    <ol class="step-list">
                        <li>
                            <strong>Create Conversion Action</strong>
                            <div class="code-block">
1. Go to Google Ads → Tools → Conversions
2. Click "+" to create new conversion
3. Select "Website"
4. Enter your website URL
5. Choose "Purchase" category
6. Set value and attribution settings
                            </div>
                        </li>
                        
                        <li>
                            <strong>Get Conversion ID & Label</strong>
                            <p>After creating the conversion action:</p>
                            <div class="code-block">
Conversion ID: AW-123456789 (from the tag)
Conversion Label: AbCdEfGhIj_KlMnOpQr (varies by action)

Full tag looks like:
gtag('event', 'conversion', {
  'send_to': 'AW-123456789/AbCdEfGhIj_KlMnOpQr',
  'value': 1.0,
  'currency': 'USD'
});
                            </div>
                        </li>
                        
                        <li>
                            <strong>Configure in Algoboost</strong>
                            <ul>
                                <li>Click "Add Pixel" → "Google Ads"</li>
                                <li>Enter Conversion ID: <code>AW-123456789</code></li>
                                <li>Enter Conversion Label: <code>AbCdEfGhIj_KlMnOpQr</code></li>
                                <li>Set conversion value rules</li>
                                <li>Choose currency</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Test Conversion Tracking</strong>
                            <div class="code-block">
1. Make a test purchase on your store
2. Check Google Ads → Tools → Conversions
3. Look for "Recent conversions" (may take 3 hours)
4. Verify conversion value and attribution
                            </div>
                        </li>
                    </ol>

                    <h2>Enhanced Conversions</h2>
                    <p>Send first-party customer data to improve match rates and attribution accuracy.</p>

                    <h3>Setup Enhanced Conversions</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Enable in Google Ads</strong>
                            <div class="code-block">
1. Go to Conversions → Select your conversion action
2. Click "Settings" 
3. Find "Enhanced conversions" section
4. Turn on "Enhanced conversions for web"
5. Select "Google Tag Manager" or "Google tag"
                            </div>
                        </li>
                        
                        <li>
                            <strong>Configure Data Sharing</strong>
                            <p>In Algoboost pixel settings:</p>
                            <ul>
                                <li>Enable "Enhanced Conversions"</li>
                                <li>Select customer data to share:
                                    <ul>
                                        <li>Email address (hashed)</li>
                                        <li>Phone number (hashed)</li>
                                        <li>Name and address (hashed)</li>
                                    </ul>
                                </li>
                                <li>Configure data retention settings</li>
                            </ul>
                        </li>
                    </ol>

                    <h2>Shopping Campaign Setup</h2>
                    <p>Track Google Shopping and Performance Max campaigns:</p>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>Product-Level Tracking</h3>
                            <div class="code-block">
Event: Purchase
Parameters:
- item_id: "SKU123"
- item_name: "Red T-Shirt"
- category: "Apparel"
- value: 29.99
                            </div>
                        </div>
                        <div class="feature-card">
                            <h3>Performance Max Optimization</h3>
                            <div class="code-block">
Custom conversion goals:
- New customer acquisition
- High-value orders (>$100)
- Repeat purchases
- Cross-sell events
                            </div>
                        </div>
                    </div>

                    <h2>Attribution Models</h2>
                    <p>Choose the right attribution for your business:</p>
                    <ul>
                        <li><strong>Data-driven:</strong> Uses machine learning (recommended)</li>
                        <li><strong>Last click:</strong> Credits the final click</li>
                        <li><strong>First click:</strong> Credits the first interaction</li>
                        <li><strong>Linear:</strong> Equal credit to all touchpoints</li>
                        <li><strong>Time decay:</strong> More credit to recent interactions</li>
                        <li><strong>Position-based:</strong> 40% first, 40% last, 20% middle</li>
                    </ul>

                    <h2>Advanced Google Ads Features</h2>
                    <h3>Customer Match Lists</h3>
                    <div class="code-block">
1. Upload customer email lists
2. Create lookalike audiences
3. Target existing customers differently
4. Exclude existing customers from acquisition campaigns
                    </div>

                    <h3>Offline Conversions</h3>
                    <p>Track phone orders and in-store purchases:</p>
                    <ul>
                        <li>Import offline conversion data</li>
                        <li>Match phone orders to online clicks</li>
                        <li>Track store visits from online ads</li>
                    </ul>
                </section>

                <section id="server-side">
                    <h1>Server-Side Tracking</h1>
                    <p>Bypass browser limitations and tracking blockers with enterprise-grade server-side tracking.</p>

                    <div class="alert alert-info">
                        <strong>Requires Growth+ Plan:</strong> Server-side tracking is available on Growth and Enterprise plans.
                    </div>

                    <h2>What is Server-Side Tracking?</h2>
                    <p>Instead of relying on browser cookies and JavaScript, server-side tracking sends conversion data directly from our servers to advertising platforms. This provides:</p>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>🔒 Higher Accuracy</h3>
                            <p>99%+ tracking accuracy vs 40-60% with browser-only tracking</p>
                        </div>
                        <div class="feature-card">
                            <h3>🚫 Ad Blocker Resistant</h3>
                            <p>Works even when browsers block tracking scripts</p>
                        </div>
                        <div class="feature-card">
                            <h3>📱 iOS 14.5+ Compatible</h3>
                            <p>Unaffected by App Tracking Transparency restrictions</p>
                        </div>
                        <div class="feature-card">
                            <h3>⚡ Better Performance</h3>
                            <p>Faster page loads without heavy tracking scripts</p>
                        </div>
                    </div>

                    <h2>Supported Platforms</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Server API</th>
                                    <th>Deduplication</th>
                                    <th>Real-time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Facebook (Meta)</td>
                                    <td>Conversions API</td>
                                    <td>Event ID matching</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>Google Ads</td>
                                    <td>Enhanced Conversions</td>
                                    <td>Customer data matching</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>TikTok</td>
                                    <td>Events API</td>
                                    <td>Event ID matching</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>Pinterest</td>
                                    <td>Conversions API</td>
                                    <td>Event ID matching</td>
                                    <td>Yes</td>
                                </tr>
                                <tr>
                                    <td>Snapchat</td>
                                    <td>Conversions API</td>
                                    <td>Event ID matching</td>
                                    <td>Yes</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2>How It Works</h2>
                    <ol class="step-list">
                        <li>
                            <strong>Customer Action</strong>
                            <p>Customer visits your store and makes a purchase</p>
                        </li>
                        
                        <li>
                            <strong>Shopify Webhook</strong>
                            <p>Shopify sends order data to Algoboost servers</p>
                        </li>
                        
                        <li>
                            <strong>Data Processing</strong>
                            <p>We enrich the data with customer information and attribution</p>
                        </li>
                        
                        <li>
                            <strong>Server-to-Server</strong>
                            <p>Conversion data sent directly to advertising platforms</p>
                        </li>
                    </ol>

                    <h2>Setup Requirements</h2>
                    <p>To enable server-side tracking, you'll need:</p>
                    <ul>
                        <li>Growth+ plan subscription</li>
                        <li>Platform access tokens (Facebook, TikTok, etc.)</li>
                        <li>Customer data sharing consent (GDPR compliance)</li>
                        <li>Proper deduplication setup</li>
                    </ul>

                    <h2>Configuration Steps</h2>
                    <ol class="step-list">
                        <li>
                            <strong>Generate Access Tokens</strong>
                            <p>For each platform you want to track:</p>
                            <div class="code-block">
Facebook: Events Manager → Settings → Generate Token
TikTok: Events Manager → Web Events → Access Token  
Pinterest: Ads Manager → Conversions → Access Token
                            </div>
                        </li>
                        
                        <li>
                            <strong>Configure in Algoboost</strong>
                            <p>In your pixel settings:</p>
                            <ul>
                                <li>Enable "Server-Side Tracking"</li>
                                <li>Add access tokens for each platform</li>
                                <li>Configure customer data sharing</li>
                                <li>Set up deduplication rules</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Test Configuration</strong>
                            <div class="code-block">
1. Make a test purchase
2. Check server-side event delivery in dashboard  
3. Verify events appear in platform (Facebook Events Manager, etc.)
4. Confirm no duplicate events
                            </div>
                        </li>
                    </ol>

                    <h2>Advanced Features</h2>
                    <h3>Customer Data Enhancement</h3>
                    <p>We can enrich server-side events with:</p>
                    <ul>
                        <li>Customer lifetime value</li>
                        <li>Purchase history</li>
                        <li>Demographic data</li>
                        <li>Custom audience segments</li>
                    </ul>

                    <h3>Real-time Monitoring</h3>
                    <p>Track server-side performance with:</p>
                    <ul>
                        <li>Event delivery rates</li>
                        <li>API response times</li>
                        <li>Error monitoring</li>
                        <li>Queue status</li>
                    </ul>

                    <h2>Best Practices</h2>
                    <ul>
                        <li><strong>Enable deduplication</strong> to avoid double-counting events</li>
                        <li><strong>Use consistent customer IDs</strong> across platforms</li>
                        <li><strong>Monitor error rates</strong> and fix integration issues quickly</li>
                        <li><strong>Test regularly</strong> to ensure data flows correctly</li>
                        <li><strong>Respect privacy</strong> settings and consent management</li>
                    </ul>
                </section>

                <section id="custom-events">
                    <h1>Custom Events</h1>
                    <p>Track specific business actions and user behaviors with custom event tracking.</p>

                    <div class="alert alert-info">
                        <strong>Plan Requirements:</strong> Custom events are available on Starter+ plans (3 events), Growth plans (10 events), and Enterprise (unlimited).
                    </div>

                    <h2>What are Custom Events?</h2>
                    <p>Custom events let you track specific actions that matter to your business beyond standard ecommerce events like purchases and page views.</p>

                    <h2>Custom Event Categories</h2>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>📧 Engagement Events</h3>
                            <p>Newsletter signups, social shares, video views, content downloads</p>
                        </div>
                        <div class="feature-card">
                            <h3>🛒 Shopping Behavior</h3>
                            <p>Wishlist additions, size guide views, product comparisons</p>
                        </div>
                        <div class="feature-card">
                            <h3>💬 Support Interactions</h3>
                            <p>Live chat starts, help article views, contact form submissions</p>
                        </div>
                        <div class="feature-card">
                            <h3>🎯 Conversion Assists</h3>
                            <p>Review reads, FAQ visits, coupon applications</p>
                        </div>
                    </div>

                    <h2>Setup Custom Events</h2>
                    <ol class="step-list">
                        <li>
                            <strong>Define Your Event</strong>
                            <p>Identify what action you want to track:</p>
                            <ul>
                                <li>What triggers the event?</li>
                                <li>What data should be captured?</li>
                                <li>How will you use this data?</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Choose Trigger Type</strong>
                            <p>Select how the event should be triggered:</p>
                            <div class="code-block">
Click-based: Button clicks, link clicks
Form-based: Form submissions, input changes  
Time-based: Time on page, scroll depth
Custom: JavaScript triggers, API calls
                            </div>
                        </li>
                        
                        <li>
                            <strong>Configure in Dashboard</strong>
                            <p>In Algoboost dashboard:</p>
                            <ul>
                                <li>Go to "Custom Events"</li>
                                <li>Click "Add Custom Event"</li>
                                <li>Enter event name and description</li>
                                <li>Configure trigger conditions</li>
                                <li>Set event parameters</li>
                            </ul>
                        </li>
                    </ol>

                    <h2>Popular Custom Event Examples</h2>
                    
                    <h3>High-Value Add to Cart</h3>
                    <div class="code-block">
Event Name: high_value_add_to_cart
Trigger: Add to cart button click
Condition: Product price > $100
Parameters:
- product_id: {product.id}
- product_price: {product.price}
- customer_segment: "high_intent"
                    </div>

                    <h3>Newsletter Signup</h3>
                    <div class="code-block">
Event Name: newsletter_signup  
Trigger: Form submission
Selector: #newsletter-form, .email-signup
Parameters:
- signup_source: {page_title}
- customer_email: {email} (hashed)
- signup_incentive: {discount_code}
                    </div>

                    <h3>Product Video Engagement</h3>
                    <div class="code-block">
Event Name: product_video_view
Trigger: Video play (25% threshold)
Selector: .product-video, [data-product-video]  
Parameters:
- video_duration: {video.duration}
- product_id: {product.id}
- engagement_level: "high"
                    </div>

                    <h3>Size Guide Interaction</h3>
                    <div class="code-block">
Event Name: size_guide_view
Trigger: Modal open / Link click
Selector: .size-guide, [data-size-guide]
Parameters:
- product_category: {product.type}
- customer_intent: "sizing_help"
                    </div>

                    <h2>Advanced Custom Events</h2>
                    <h3>JavaScript API Integration</h3>
                    <p>For complex tracking needs, use our JavaScript API:</p>
                    <div class="code-block">
// Track custom event programmatically
window.Algoboost.track('custom_event_name', {
  parameter1: 'value1',
  parameter2: 'value2',
  customer_id: 'customer_123',
  event_value: 25.99
});

// Track with conditional logic
if (customerTier === 'VIP') {
  window.Algoboost.track('vip_interaction', {
    action: 'premium_content_view',
    content_id: contentId,
    customer_tier: 'VIP'
  });
}
                    </div>

                    <h3>Server-Side Custom Events</h3>
                    <p>Track backend actions via webhook:</p>
                    <div class="code-block">
POST /api/events/custom
{
  "event_name": "subscription_renewal",
  "customer_id": "cust_12345", 
  "parameters": {
    "subscription_tier": "premium",
    "renewal_method": "auto",
    "subscription_value": 99.99
  },
  "timestamp": "2024-01-15T10:30:00Z"
}
                    </div>

                    <h2>Event Parameters</h2>
                    <p>Enrich your events with relevant data:</p>
                    <ul>
                        <li><strong>Standard Parameters:</strong> customer_id, product_id, value, currency</li>
                        <li><strong>Behavioral:</strong> time_on_page, scroll_depth, click_position</li>
                        <li><strong>Product Data:</strong> category, brand, price, inventory_status</li>
                        <li><strong>Customer Data:</strong> segment, lifetime_value, purchase_history</li>
                        <li><strong>Context:</strong> traffic_source, device_type, location</li>
                    </ul>

                    <h2>Using Custom Events for Optimization</h2>
                    <h3>Facebook Custom Audiences</h3>
                    <div class="code-block">
Create audiences based on custom events:
- Users who viewed size guides (intent)
- Newsletter subscribers (engagement)
- Video viewers (brand awareness)
- High-value cart additions (conversion likelihood)
                    </div>

                    <h3>Google Ads Conversion Goals</h3>
                    <div class="code-block">
Set up micro-conversions:
- Newsletter signup = $5 value
- Video engagement = $2 value  
- Size guide view = $3 value
- Product comparison = $4 value
                    </div>

                    <h2>Event Testing & Validation</h2>
                    <ol class="step-list">
                        <li>
                            <strong>Test Locally</strong>
                            <p>Verify events fire correctly in browser console</p>
                        </li>
                        
                        <li>
                            <strong>Check Dashboard</strong>
                            <p>Confirm events appear in Algoboost real-time dashboard</p>
                        </li>
                        
                        <li>
                            <strong>Platform Verification</strong>
                            <p>Check that events reach advertising platforms</p>
                        </li>
                        
                        <li>
                            <strong>A/B Test</strong>
                            <p>Test different event configurations for optimal performance</p>
                        </li>
                    </ol>
                </section>

                <section id="common-issues">
                    <h1>Common Issues & Solutions</h1>
                    <p>Solutions to the most frequently encountered problems with Algoboost setup and tracking.</p>

                    <h2>Installation Issues</h2>

                    <div class="alert alert-warning">
                        <strong>Problem:</strong> "Script not loading" or "Algoboost not found" errors
                    </div>
                    
                    <p><strong>Solutions:</strong></p>
                    <ol>
                        <li><strong>Check app permissions:</strong> Ensure the app has script tag write permissions</li>
                        <li><strong>Ad blocker interference:</strong> Test in incognito mode to rule out browser extensions</li>
                        <li><strong>Theme conflicts:</strong> Some themes modify script loading - check theme.liquid file</li>
                        <li><strong>Clear cache:</strong> Clear browser cache and CDN cache if using one</li>
                        <li><strong>Check script placement:</strong> Script should be in the <code>&lt;head&gt;</code> section</li>
                    </ol>

                    <div class="code-block">
// Debug in browser console (F12):
console.log('Algoboost loaded:', typeof window.Algoboost !== 'undefined');
console.log('Base script:', window.Algoboost?.version);

// Check network tab for requests to:
// your-app-domain/tracking/base.js
// Should return 200 status code
                    </div>

                    <h2>Tracking Issues</h2>

                    <div class="alert alert-warning">
                        <strong>Problem:</strong> Events not firing or appearing in platforms
                    </div>

                    <h3>Facebook Pixel Issues</h3>
                    <ul>
                        <li><strong>Domain verification:</strong> Verify your domain in Facebook Business Manager</li>
                        <li><strong>iOS 14.5+ limitations:</strong> Enable server-side tracking for full coverage</li>
                        <li><strong>Parameter formatting:</strong> Ensure currency codes are correct (USD, EUR, etc.)</li>
                        <li><strong>Content IDs:</strong> Use consistent product IDs across events</li>
                    </ul>

                    <div class="code-block">
// Check Facebook Pixel Helper
// Green = Working correctly  
// Yellow = Warning (check parameters)
// Red = Error (check pixel ID and setup)

// Manual event testing:
fbq('track', 'Purchase', {
  value: 10.00,
  currency: 'USD'
});
                    </div>

                    <h3>Google Ads Issues</h3>
                    <ul>
                        <li><strong>Conversion delay:</strong> Google Ads may take 3-24 hours to show conversions</li>
                        <li><strong>Attribution mismatch:</strong> Check attribution window settings</li>
                        <li><strong>Enhanced conversions:</strong> Ensure customer data is properly hashed</li>
                        <li><strong>Cross-domain tracking:</strong> Configure for checkout on different domains</li>
                    </ul>

                    <h2>Data Discrepancy Issues</h2>

                    <div class="alert alert-info">
                        <strong>Why numbers don't match:</strong> Different platforms use different attribution windows and counting methods
                    </div>

                    <h3>Common Discrepancy Causes</h3>
                    <ul>
                        <li><strong>Attribution windows:</strong> Facebook (1 day view, 7 day click) vs Google Ads (30 day)</li>
                        <li><strong>Time zones:</strong> Ensure all platforms use the same timezone</li>
                        <li><strong>Deduplication:</strong> Some events may be counted multiple times</li>
                        <li><strong>Currency conversion:</strong> Exchange rates can cause small differences</li>
                        <li><strong>Return/refund handling:</strong> Platforms handle refunds differently</li>
                    </ul>

                    <h3>Minimizing Discrepancies</h3>
                    <ol>
                        <li><strong>Standardize settings:</strong> Use same attribution window across platforms</li>
                        <li><strong>Enable deduplication:</strong> Use event IDs to prevent double counting</li>
                        <li><strong>Sync time zones:</strong> Set all platforms to your business timezone</li>
                        <li><strong>Handle refunds:</strong> Configure refund event tracking</li>
                        <li><strong>Document changes:</strong> Track when you modify tracking setup</li>
                    </ol>

                    <h2>Performance Issues</h2>

                    <div class="alert alert-warning">
                        <strong>Problem:</strong> Slow page loading or tracking delays
                    </div>

                    <h3>Performance Solutions</h3>
                    <ul>
                        <li><strong>Async loading:</strong> Our scripts load asynchronously by default</li>
                        <li><strong>CDN delivery:</strong> Scripts served from global CDN for faster loading</li>
                        <li><strong>Lazy loading:</strong> Non-critical events loaded after page render</li>
                        <li><strong>Batch processing:</strong> Multiple events sent in single requests</li>
                        <li><strong>Local caching:</strong> Scripts cached in browser for repeat visits</li>
                    </ul>

                    <div class="code-block">
// Monitor performance impact
console.time('Algoboost Load');
// ... tracking code executes ...
console.timeEnd('Algoboost Load');

// Should be under 100ms for initialization
                    </div>

                    <h2>Server-Side Tracking Issues</h2>

                    <div class="alert alert-warning">
                        <strong>Problem:</strong> Server-side events not delivering or high error rates
                    </div>

                    <h3>Common Server-Side Issues</h3>
                    <ul>
                        <li><strong>Invalid access tokens:</strong> Tokens expired or incorrect permissions</li>
                        <li><strong>API rate limits:</strong> Too many requests sent too quickly</li>
                        <li><strong>Data formatting:</strong> Incorrect parameter formats or missing required fields</li>
                        <li><strong>Network timeouts:</strong> Slow API responses causing failures</li>
                    </ul>

                    <h3>Troubleshooting Steps</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Check Token Validity</strong>
                            <div class="code-block">
Facebook: Go to Events Manager → Settings → Test Access Token
Google: Check OAuth token expiration in Google Ads
TikTok: Verify token in Events Manager → Access Token section
                            </div>
                        </li>
                        
                        <li>
                            <strong>Monitor Error Logs</strong>
                            <p>In Algoboost dashboard, check:</p>
                            <ul>
                                <li>Server-side delivery status</li>
                                <li>Error messages and codes</li>
                                <li>Retry attempt history</li>
                                <li>API response times</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Test Individual Platforms</strong>
                            <p>Isolate issues by testing each platform separately</p>
                        </li>
                    </ol>

                    <h2>Mobile Tracking Issues</h2>

                    <div class="alert alert-info">
                        <strong>Mobile Specific:</strong> iOS and Android browsers have different tracking limitations
                    </div>

                    <h3>iOS Challenges</h3>
                    <ul>
                        <li><strong>ITP (Intelligent Tracking Prevention):</strong> Safari blocks many tracking attempts</li>
                        <li><strong>7-day cookie limit:</strong> Cookies expire after 7 days of inactivity</li>
                        <li><strong>Cross-site restrictions:</strong> Limited cross-domain tracking</li>
                        <li><strong>App Tracking Transparency:</strong> Users can opt out of tracking</li>
                    </ul>

                    <h3>Mobile Optimization</h3>
                    <ul>
                        <li><strong>Server-side priority:</strong> Use server-side tracking for mobile traffic</li>
                        <li><strong>First-party data:</strong> Focus on logged-in user tracking</li>
                        <li><strong>Progressive web app:</strong> PWA installations improve tracking</li>
                        <li><strong>App deep linking:</strong> Better attribution for mobile app traffic</li>
                    </ul>

                    <h2>GDPR & Privacy Issues</h2>

                    <div class="alert alert-warning">
                        <strong>Problem:</strong> Compliance issues or consent management conflicts
                    </div>

                    <h3>Common Privacy Challenges</h3>
                    <ul>
                        <li><strong>Consent requirements:</strong> Tracking blocked until user consent</li>
                        <li><strong>Data retention limits:</strong> Must delete data after specified period</li>
                        <li><strong>Right to be forgotten:</strong> Users can request data deletion</li>
                        <li><strong>Cross-border transfers:</strong> Restrictions on data sent outside EU</li>
                    </ul>

                    <h3>Privacy Solutions</h3>
                    <ul>
                        <li><strong>Consent integration:</strong> Works with popular consent management platforms</li>
                        <li><strong>Data anonymization:</strong> Hash personal data before sending</li>
                        <li><strong>Retention controls:</strong> Automatic data deletion after set periods</li>
                        <li><strong>User controls:</strong> Allow users to opt-out or delete their data</li>
                    </ul>

                    <h2>Getting Help</h2>
                    <p>If you're still experiencing issues after trying these solutions:</p>
                    
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>Email Support</h3>
                            <p>Email us at <strong>support@algoboost.io</strong> with:</p>
                            <ul>
                                <li>Store URL</li>
                                <li>Pixel configuration details</li>
                                <li>Specific error messages</li>
                                <li>Screenshots if applicable</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>Live Chat</h3>
                            <p>Available in your Algoboost dashboard</p>
                            <ul>
                                <li>Growth+ plans: Priority support</li>
                                <li>Enterprise: Dedicated success manager</li>
                                <li>Free plans: Community support</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>Knowledge Base</h3>
                            <p>Comprehensive guides and tutorials</p>
                            <ul>
                                <li>Platform-specific guides</li>
                                <li>Video tutorials</li>
                                <li>Troubleshooting checklists</li>
                                <li>Best practices</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>Community Forum</h3>
                            <p>Connect with other merchants</p>
                            <ul>
                                <li>Share best practices</li>
                                <li>Get peer support</li>
                                <li>Feature requests</li>
                                <li>Success stories</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <strong>Pro Tip:</strong> Include your store URL, current plan, and specific error messages when contacting support for faster resolution.
                    </div>
                </section>

                <section id="testing-tracking">
                    <h1>Testing Your Tracking Setup</h1>
                    <p>Comprehensive guide to testing and validating your conversion tracking implementation.</p>

                    <h2>Pre-Launch Testing Checklist</h2>
                    <div class="code-block">
□ Base script loads correctly
□ All pixels fire on appropriate pages  
□ Event parameters include required data
□ Server-side events deliver successfully
□ No duplicate events detected
□ Cross-device tracking works
□ Mobile tracking functional
□ GDPR compliance active
                    </div>

                    <h2>Browser-Based Testing</h2>
                    <h3>Facebook Pixel Testing</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Install Facebook Pixel Helper</strong>
                            <p>Chrome extension that shows pixel activity in real-time</p>
                            <div class="code-block">
Chrome Web Store → Search "Facebook Pixel Helper"
Green checkmark = Pixel firing correctly
Yellow warning = Issues detected  
Red X = Pixel not working
                            </div>
                        </li>
                        
                        <li>
                            <strong>Test Each Event Type</strong>
                            <ul>
                                <li><strong>PageView:</strong> Visit any store page</li>
                                <li><strong>ViewContent:</strong> Visit product pages</li>
                                <li><strong>AddToCart:</strong> Add items to cart</li>
                                <li><strong>InitiateCheckout:</strong> Start checkout process</li>
                                <li><strong>Purchase:</strong> Complete a test order</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Verify Event Parameters</strong>
                            <div class="code-block">
Purchase event should include:
- value: Order total
- currency: Three-letter code (USD, EUR)
- content_ids: Product SKUs
- content_type: "product"
- num_items: Quantity
                            </div>
                        </li>
                    </ol>

                    <h3>Google Ads Testing</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Google Tag Assistant</strong>
                            <p>Chrome extension for Google tracking validation</p>
                        </li>
                        
                        <li>
                            <strong>Test Conversion Events</strong>
                            <div class="code-block">
1. Complete test purchase
2. Wait 30 minutes  
3. Check Google Ads → Tools → Conversions
4. Look for "Recent conversions" table
5. Verify conversion value and attribution
                            </div>
                        </li>
                        
                        <li>
                            <strong>Enhanced Conversions Test</strong>
                            <p>Verify customer data is being sent:</p>
                            <ul>
                                <li>Email address (hashed)</li>
                                <li>Phone number (if available)</li>
                                <li>Name and address data</li>
                            </ul>
                        </li>
                    </ol>

                    <h2>Platform-Specific Testing</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Testing Tool</th>
                                    <th>Test Location</th>
                                    <th>Typical Delay</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Facebook</td>
                                    <td>Pixel Helper, Events Manager</td>
                                    <td>Events Manager → Test Events</td>
                                    <td>Real-time</td>
                                </tr>
                                <tr>
                                    <td>Google Ads</td>
                                    <td>Tag Assistant, Google Ads</td>
                                    <td>Google Ads → Conversions</td>
                                    <td>30 minutes - 3 hours</td>
                                </tr>
                                <tr>
                                    <td>TikTok</td>
                                    <td>TikTok Pixel Helper</td>
                                    <td>TikTok Events Manager</td>
                                    <td>15-30 minutes</td>
                                </tr>
                                <tr>
                                    <td>Pinterest</td>
                                    <td>Pinterest Tag Helper</td>
                                    <td>Pinterest Ads Manager</td>
                                    <td>30 minutes - 2 hours</td>
                                </tr>
                                <tr>
                                    <td>Google Analytics</td>
                                    <td>GA Debugger, Real-time reports</td>
                                    <td>GA4 → Reports → Real-time</td>
                                    <td>Real-time</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2>Server-Side Testing</h2>
                    <h3>Test Server-Side Delivery</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Check Algoboost Dashboard</strong>
                            <p>Monitor server-side event delivery status:</p>
                            <ul>
                                <li>Events sent successfully</li>
                                <li>API response codes</li>
                                <li>Retry attempts</li>
                                <li>Error messages</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Platform Verification</strong>
                            <div class="code-block">
Facebook: Events Manager → Data Sources → Server Events
Google: Enhanced Conversions reporting  
TikTok: Events Manager → Web Events → Server Events
Pinterest: Ads Manager → Conversions → API Events
                            </div>
                        </li>
                        
                        <li>
                            <strong>Test Deduplication</strong>
                            <p>Ensure browser and server events aren't double-counted:</p>
                            <ul>
                                <li>Make test purchase</li>
                                <li>Check that only one purchase event is recorded</li>
                                <li>Verify event IDs match between browser and server</li>
                            </ul>
                        </li>
                    </ol>

                    <h2>Advanced Testing Scenarios</h2>
                    <h3>Cross-Device Testing</h3>
                    <ul>
                        <li><strong>Desktop → Mobile:</strong> Start on desktop, complete on mobile</li>
                        <li><strong>Mobile → Desktop:</strong> Browse on mobile, purchase on desktop</li>
                        <li><strong>Multiple sessions:</strong> Test attribution across multiple visits</li>
                        <li><strong>Different browsers:</strong> Test Safari, Chrome, Firefox, Edge</li>
                    </ul>

                    <h3>Privacy Mode Testing</h3>
                    <ul>
                        <li><strong>Incognito/Private browsing:</strong> Test tracking without cookies</li>
                        <li><strong>Ad blockers enabled:</strong> Verify server-side still works</li>
                        <li><strong>Cookie consent rejected:</strong> Ensure compliance mode works</li>
                        <li><strong>iOS Safari:</strong> Test with ITP restrictions</li>
                    </ul>

                    <h2>Automated Testing</h2>
                    <h3>Monitoring Setup</h3>
                    <p>Set up automated monitoring for ongoing validation:</p>
                    <ul>
                        <li><strong>Uptime monitoring:</strong> Check script availability</li>
                        <li><strong>Event validation:</strong> Automated event firing tests</li>
                        <li><strong>Performance monitoring:</strong> Track script load times</li>
                        <li><strong>Error alerting:</strong> Notifications for tracking issues</li>
                    </ul>

                    <h2>Testing Documentation</h2>
                    <p>Document your testing process:</p>
                    <div class="code-block">
Testing Checklist Template:

Date: ___________
Tester: ___________
Store: ___________

□ Facebook Pixel - PageView: Pass/Fail
□ Facebook Pixel - Purchase: Pass/Fail  
□ Google Ads - Conversion: Pass/Fail
□ Server-side delivery: Pass/Fail
□ Mobile testing: Pass/Fail
□ Cross-browser testing: Pass/Fail

Issues Found:
- 
-
-

Resolution:
-
-
-
                    </div>
                </section>

                <section id="gdpr-compliance">
                    <h1>GDPR Compliance</h1>
                    <p>Ensure your conversion tracking meets GDPR, CCPA, and other privacy regulations.</p>

                    <div class="alert alert-info">
                        <strong>Built-in Compliance:</strong> Algoboost includes automatic privacy compliance features for GDPR, CCPA, and other regulations.
                    </div>

                    <h2>Privacy Regulations Overview</h2>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>GDPR (EU)</h3>
                            <p>General Data Protection Regulation</p>
                            <ul>
                                <li>Explicit consent required</li>
                                <li>Right to be forgotten</li>
                                <li>Data portability</li>
                                <li>Privacy by design</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>CCPA (California)</h3>
                            <p>California Consumer Privacy Act</p>
                            <ul>
                                <li>Right to know data use</li>
                                <li>Right to delete data</li>
                                <li>Right to opt-out</li>
                                <li>Non-discrimination</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>LGPD (Brazil)</h3>
                            <p>Lei Geral de Proteção de Dados</p>
                            <ul>
                                <li>Consent requirements</li>
                                <li>Data minimization</li>
                                <li>Purpose limitation</li>
                                <li>Data subject rights</li>
                            </ul>
                        </div>
                    </div>

                    <h2>Algoboost Compliance Features</h2>
                    <h3>Automatic Data Protection</h3>
                    <ul>
                        <li><strong>Data hashing:</strong> Personal data automatically hashed before transmission</li>
                        <li><strong>Consent integration:</strong> Works with popular consent management platforms</li>
                        <li><strong>Data retention controls:</strong> Automatic deletion after specified periods</li>
                        <li><strong>Opt-out mechanisms:</strong> Easy user opt-out and data deletion</li>
                        <li><strong>Audit logging:</strong> Complete audit trail of data processing</li>
                    </ul>

                    <h2>Setting Up GDPR Compliance</h2>
                    <ol class="step-list">
                        <li>
                            <strong>Enable Compliance Mode</strong>
                            <p>In Algoboost settings:</p>
                            <ul>
                                <li>Go to Settings → Privacy</li>
                                <li>Enable "GDPR Compliance Mode"</li>
                                <li>Configure data retention periods</li>
                                <li>Set up consent integration</li>
                            </ul>
                        </li>
                        
                        <li>
                            <strong>Configure Consent Management</strong>
                            <p>Integrate with your consent platform:</p>
                            <div class="code-block">
Supported platforms:
- OneTrust
- Cookiebot  
- TrustArc
- Civic UK Cookie Control
- Custom consent solutions
                            </div>
                        </li>
                        
                        <li>
                            <strong>Set Data Retention Periods</strong>
                            <div class="code-block">
Recommended retention periods:
- Event data: 365 days
- Customer data: 30-90 days  
- Report data: 90 days
- PII data: 30 days (required by GDPR)
                            </div>
                        </li>
                        
                        <li>
                            <strong>Configure Data Processing</strong>
                            <ul>
                                <li>Hash email addresses automatically</li>
                                <li>Anonymize IP addresses</li>
                                <li>Remove personal identifiers</li>
                                <li>Implement data minimization</li>
                            </ul>
                        </li>
                    </ol>

                    <h2>Consent Management Integration</h2>
                    <h3>How It Works</h3>
                    <p>Algoboost respects user consent choices:</p>
                    <ul>
                        <li><strong>No consent:</strong> Only essential tracking (no personal data)</li>
                        <li><strong>Marketing consent:</strong> Full tracking with personal data</li>
                        <li><strong>Analytics consent:</strong> Anonymous tracking only</li>
                        <li><strong>Consent withdrawn:</strong> Stop tracking, delete stored data</li>
                    </ul>

                    <h3>Implementation</h3>
                    <div class="code-block">
// Example consent integration
window.Algoboost.setConsent({
  marketing: true,    // Allow marketing tracking
  analytics: true,    // Allow analytics tracking
  personalization: false // Disable personalization
});

// Update consent when user changes preferences
window.addEventListener('consent-changed', function(event) {
  window.Algoboost.setConsent(event.detail.consent);
});
                    </div>

                    <h2>Data Subject Rights</h2>
                    <h3>Right to Access</h3>
                    <p>Users can request to see what data we have:</p>
                    <ul>
                        <li>Event history</li>
                        <li>Customer profile data</li>
                        <li>Tracking preferences</li>
                        <li>Data sharing records</li>
                    </ul>

                    <h3>Right to be Forgotten</h3>
                    <p>Complete data deletion process:</p>
                    <ol>
                        <li>User requests data deletion</li>
                        <li>Algoboost removes all stored data</li>
                        <li>Data deletion sent to connected platforms</li>
                        <li>Confirmation provided to user</li>
                    </ol>

                    <h3>Right to Data Portability</h3>
                    <p>Export user data in machine-readable format:</p>
                    <ul>
                        <li>JSON export of all user data</li>
                        <li>CSV export for analysis</li>
                        <li>API access for automated export</li>
                    </ul>

                    <h2>Cross-Border Data Transfers</h2>
                    <h3>Data Processing Locations</h3>
                    <p>Algoboost processes data in compliant jurisdictions:</p>
                    <ul>
                        <li><strong>EU data:</strong> Processed within EU/EEA</li>
                        <li><strong>US data:</strong> Processed under Privacy Shield successor</li>
                        <li><strong>Global:</strong> Standard Contractual Clauses (SCCs)</li>
                    </ul>

                    <h2>Privacy Policy Requirements</h2>
                    <p>Update your privacy policy to include:</p>
                    <div class="code-block">
Required disclosures:
- What data is collected
- How data is used  
- Who data is shared with
- How long data is stored
- User rights and controls
- Contact information for privacy requests
                    </div>

                    <h3>Sample Privacy Policy Language</h3>
                    <div class="code-block">
"We use Algoboost to track website interactions and conversions 
for advertising optimization. This may include:

- Page views and user interactions
- Purchase data and order information  
- Hashed email addresses for advertising matching
- Device and browser information

You can opt-out of this tracking by [provide opt-out method].
Data is automatically deleted after [retention period].

For data access, deletion, or questions, contact: privacy@yourstore.com"
                    </div>

                    <h2>Compliance Monitoring</h2>
                    <h3>Regular Compliance Checks</h3>
                    <ul>
                        <li><strong>Monthly:</strong> Review data retention compliance</li>
                        <li><strong>Quarterly:</strong> Audit consent mechanisms</li>
                        <li><strong>Annually:</strong> Full privacy impact assessment</li>
                        <li><strong>As needed:</strong> Respond to data subject requests</li>
                    </ul>

                    <div class="alert alert-success">
                        <strong>Peace of Mind:</strong> Algoboost handles most compliance requirements automatically, but work with your legal team to ensure complete compliance for your specific situation.
                    </div>
                </section>

                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center; color: var(--text-light);">
                    <p>Need more help? <a href="/contact">Contact Support</a> or <a href="/faq">Browse FAQ</a></p>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Product</h3>
                <a href="/features">Features</a>
                <a href="/pricing">Pricing</a>
                <a href="/documentation">Documentation</a>
                <a href="/security">Security</a>
            </div>
            
            <div class="footer-section">
                <h3>Company</h3>
                <a href="/about">About Us</a>
                <a href="/blog">Blog</a>
                <a href="/contact">Contact</a>
            </div>
            
            <div class="footer-section">
                <h3>Support</h3>
                <a href="/documentation">Help Center</a>
                <a href="/contact">Contact Support</a>
                <a href="mailto:support@algoboost.io">Email Support</a>
            </div>
            
            <div class="footer-section">
                <h3>Legal</h3>
                <a href="/privacy">Privacy Policy</a>
                <a href="/terms">Terms of Service</a>
                <a href="/gdpr">GDPR</a>
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
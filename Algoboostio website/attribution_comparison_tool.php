<?php /* Template Name: Attribution Comparison Tool */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attribution Comparison Tool - Algoboost | Compare Tracking Data Across Platforms</title>
    <meta name="description" content="Free spreadsheet template to compare attribution data across advertising platforms and identify tracking gaps in your Shopify store setup.">
    <meta name="keywords" content="attribution comparison, tracking data analysis, advertising attribution, shopify analytics comparison, conversion attribution tool">

    <!-- Open Graph -->
    <meta property="og:title" content="Attribution Comparison Tool - Algoboost">
    <meta property="og:description" content="Spreadsheet template to compare attribution data and identify tracking gaps.">
    <meta property="og:image" content="<?php echo home_url('/images/attribution-tool-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/resources/attribution-tool'); ?>">

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
            background: linear-gradient(135deg, var(--info), #1e40af);
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
            color: var(--info);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        /* Tool Features */
        .tool-preview {
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

        .preview-table {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .comparison-table th {
            background: var(--gray-900);
            color: white;
            padding: 1rem 0.75rem;
            text-align: left;
            font-weight: 600;
        }

        .comparison-table th:first-child {
            border-radius: 8px 0 0 0;
        }

        .comparison-table th:last-child {
            border-radius: 0 8px 0 0;
        }

        .comparison-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
            background: white;
        }

        .comparison-table tr:last-child td:first-child {
            border-radius: 0 0 0 8px;
        }

        .comparison-table tr:last-child td:last-child {
            border-radius: 0 0 8px 0;
        }

        .metric-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .metric-value {
            text-align: center;
            font-weight: 500;
        }

        .value-high {
            color: var(--success);
            background: rgba(16, 185, 129, 0.1);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
        }

        .value-medium {
            color: var(--warning);
            background: rgba(245, 158, 11, 0.1);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
        }

        .value-low {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
        }

        .discrepancy {
            background: rgba(239, 68, 68, 0.1);
            border-left: 3px solid #ef4444;
        }

        /* How It Works */
        .how-it-works {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 4rem;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .step-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
        }

        .step-number {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--info);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .step-icon {
            font-size: 2.5rem;
            margin: 1rem 0 1.5rem;
        }

        .step-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .step-description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Benefits Section */
        .benefits-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 4rem;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .benefit-item {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            background: var(--bg-light);
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

            .preview-table {
                padding: 1rem;
            }

            .comparison-table {
                font-size: 0.8rem;
            }

            .comparison-table th,
            .comparison-table td {
                padding: 0.5rem 0.25rem;
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
            <h1>Attribution Comparison Tool</h1>
            <p class="hero-subtitle">
                Advanced spreadsheet template to compare attribution data across advertising platforms and identify tracking gaps, discrepancies, and optimization opportunities in your Shopify store.
            </p>
            
            <div class="hero-features">
                <div class="hero-feature">18 Platforms</div>
                <div class="hero-feature">Auto Calculations</div>
                <div class="hero-feature">Gap Detection</div>
                <div class="hero-feature">Free Download</div>
            </div>
            
            <a href="#download" class="btn" style="background: white; color: var(--info); font-size: 1.1rem; padding: 1rem 2rem;">Get Free Tool</a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Resource Details -->
        <section class="resource-details">
            <div class="resource-main">
                <h2 style="font-size: 2rem; margin-bottom: 1.5rem;">Identify Attribution Gaps Across All Platforms</h2>
                
                <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem;">
                    Different advertising platforms often report different conversion numbers for the same campaigns. 
                    Our attribution comparison tool helps you identify these discrepancies, understand their causes, 
                    and optimize your tracking setup for better data consistency.
                </p>

                <h3 style="margin-bottom: 1rem; color: var(--text-dark);">What the Tool Includes</h3>
                <ul style="margin-bottom: 2rem; color: var(--text-light); line-height: 1.8;">
                    <li>Pre-built comparison tables for 18+ advertising platforms</li>
                    <li>Automated discrepancy detection and flagging</li>
                    <li>Attribution gap analysis with impact calculations</li>
                    <li>Trend analysis charts and visualizations</li>
                    <li>Custom metrics tracking for your specific KPIs</li>
                    <li>Platform-specific troubleshooting suggestions</li>
                    <li>Monthly and quarterly comparison templates</li>
                    <li>Export-ready reports for stakeholder presentations</li>
                </ul>

                <h3 style="margin-bottom: 1rem; color: var(--text-dark);">Common Attribution Issues This Tool Helps Identify</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: rgba(239, 68, 68, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center; border-left: 3px solid #ef4444;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🚨</div>
                        <div style="font-weight: 600; color: #dc2626;">Missing Conversions</div>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center; border-left: 3px solid var(--warning);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">⚠️</div>
                        <div style="font-weight: 600; color: #d97706;">Double Counting</div>
                    </div>
                    <div style="background: rgba(59, 130, 246, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center; border-left: 3px solid var(--info);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🔄</div>
                        <div style="font-weight: 600; color: #2563eb;">Attribution Windows</div>
                    </div>
                    <div style="background: rgba(139, 92, 246, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center; border-left: 3px solid var(--secondary);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🎯</div>
                        <div style="font-weight: 600; color: #7c3aed;">Cross-Platform Issues</div>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 2rem; border-radius: 15px; margin-top: 2rem;">
                    <h3 style="color: var(--info); margin-bottom: 1rem;">💡 Based on Real Data Analysis</h3>
                    <p style="color: var(--text-light); margin-bottom: 0;">
                        This template is based on analyzing attribution discrepancies across thousands of Shopify stores. 
                        We've identified the most common gaps and built automatic detection for issues that typically 
                        cost merchants 15-30% in attribution accuracy.
                    </p>
                </div>
            </div>

            <div class="resource-sidebar" id="download">
                <div class="download-card">
                    <span class="download-icon">📊</span>
                    <h3 class="download-title">Attribution Comparison Tool</h3>
                    <div class="download-meta">
                        Excel/Google Sheets Template<br>
                        Updated January 2025
                    </div>
                    
                    <div class="resource-stats">
                        <div class="stat-item">
                            <div class="stat-value">18</div>
                            <div class="stat-label">Platforms</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">25+</div>
                            <div class="stat-label">Metrics</div>
                        </div>
                    </div>
                    
                    <form onsubmit="handleDownload(event)" style="margin-bottom: 1rem;">
                        <input type="email" placeholder="Enter your email" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Download Free Tool
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
                        <div style="font-size: 0.8rem; color: var(--text-light);">Implementation guide</div>
                    </a>
                    <a href="<?php echo home_url('/resources/tracking-roi-calculator'); ?>" style="text-decoration: none; color: var(--text-dark); padding: 1rem; background: white; border-radius: 10px; transition: var(--transition);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">ROI Calculator</div>
                        <div style="font-size: 0.8rem; color: var(--text-light);">Calculate tracking impact</div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Tool Preview -->
        <section class="tool-preview">
            <h2 class="section-title">Tool Preview</h2>
            
            <div class="preview-table">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Facebook Ads</th>
                            <th>Google Ads</th>
                            <th>TikTok Ads</th>
                            <th>Shopify Analytics</th>
                            <th>Gap Analysis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="metric-name">Total Conversions</td>
                            <td class="metric-value"><span class="value-high">247</span></td>
                            <td class="metric-value"><span class="value-high">251</span></td>
                            <td class="metric-value"><span class="value-medium">189</span></td>
                            <td class="metric-value"><span class="value-high">284</span></td>
                            <td class="metric-value">✅ Normal variance</td>
                        </tr>
                        <tr class="discrepancy">
                            <td class="metric-name">Purchase Value</td>
                            <td class="metric-value"><span class="value-high">$12,450</span></td>
                            <td class="metric-value"><span class="value-high">$11,890</span></td>
                            <td class="metric-value"><span class="value-low">$8,230</span></td>
                            <td class="metric-value"><span class="value-high">$13,100</span></td>
                            <td class="metric-value">⚠️ TikTok under-tracking</td>
                        </tr>
                        <tr>
                            <td class="metric-name">ROAS</td>
                            <td class="metric-value"><span class="value-high">3.2x</span></td>
                            <td class="metric-value"><span class="value-high">2.8x</span></td>
                            <td class="metric-value"><span class="value-medium">2.1x</span></td>
                            <td class="metric-value">-</td>
                            <td class="metric-value">📊 Review attribution</td>
                        </tr>
                        <tr>
                            <td class="metric-name">Cost Per Acquisition</td>
                            <td class="metric-value"><span class="value-high">$18.50</span></td>
                            <td class="metric-value"><span class="value-medium">$24.30</span></td>
                            <td class="metric-value"><span class="value-low">$31.80</span></td>
                            <td class="metric-value">-</td>
                            <td class="metric-value">🔍 Check tracking setup</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <p style="text-align: center; color: var(--text-light); font-style: italic;">
                Example data showing how the tool identifies discrepancies and provides actionable insights
            </p>
        </section>

        <!-- How It Works -->
        <section class="how-it-works">
            <h2 class="section-title">How to Use the Attribution Comparison Tool</h2>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">📥</div>
                    <h3 class="step-title">Import Your Data</h3>
                    <p class="step-description">
                        Export conversion data from each advertising platform and paste it into the 
                        designated sheets in the template.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">🔧</div>
                    <h3 class="step-title">Configure Settings</h3>
                    <p class="step-description">
                        Set your attribution windows, conversion values, and platform-specific 
                        settings to match your tracking setup.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">🔍</div>
                    <h3 class="step-title">Analyze Discrepancies</h3>
                    <p class="step-description">
                        The tool automatically identifies gaps, flags unusual variances, and 
                        highlights potential tracking issues across platforms.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon">📊</div>
                    <h3 class="step-title">Generate Reports</h3>
                    <p class="step-description">
                        Create visual reports and actionable recommendations to improve your 
                        attribution accuracy and tracking setup.
                    </p>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="benefits-section">
            <h2 class="section-title">What You'll Discover</h2>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">🎯</div>
                    <h3 class="benefit-title">Attribution Gaps</h3>
                    <p class="benefit-description">
                        Identify where conversions are being lost or double-counted across your advertising platforms.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">💰</div>
                    <h3 class="benefit-title">Budget Misallocation</h3>
                    <p class="benefit-description">
                        Discover if you're over or under-investing in platforms due to tracking inaccuracies.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📈</div>
                    <h3 class="benefit-title">True Performance</h3>
                    <p class="benefit-description">
                        Get a clearer picture of which platforms are actually driving the most valuable conversions.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🔧</div>
                    <h3 class="benefit-title">Setup Issues</h3>
                    <p class="benefit-description">
                        Pinpoint specific tracking configuration problems that need immediate attention.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📊</div>
                    <h3 class="benefit-title">Data Trends</h3>
                    <p class="benefit-description">
                        Track attribution accuracy over time and monitor the impact of tracking improvements.
                    </p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🎨</div>
                    <h3 class="benefit-title">Visual Reports</h3>
                    <p class="benefit-description">
                        Generate professional charts and graphs to share findings with your team or stakeholders.
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
            console.log('Attribution tool download requested for:', email);
            
            // Show success message
            const btn = event.target.querySelector('button');
            const originalText = btn.textContent;
            btn.textContent = 'Download Started! ✓';
            btn.style.background = '#10b981';
            
            // Simulate download
            setTimeout(() => {
                // In a real implementation, this would trigger the actual file download
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
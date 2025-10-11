<?php /* Template Name: Tracking ROI Calculator */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking ROI Calculator - Algoboost | Calculate Server-Side Tracking Revenue Impact</title>
    <meta name="description" content="Interactive calculator to estimate the revenue impact of implementing proper server-side tracking for your Shopify store. Free tool with detailed analysis.">
    <meta name="keywords" content="tracking roi calculator, server-side tracking roi, shopify tracking revenue impact, conversion tracking calculator, attribution value calculator">

    <!-- Open Graph -->
    <meta property="og:title" content="Tracking ROI Calculator - Algoboost">
    <meta property="og:description" content="Calculate the revenue impact of implementing proper server-side tracking.">
    <meta property="og:image" content="<?php echo home_url('/images/calculator-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/resources/tracking-roi-calculator'); ?>">

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
            background: linear-gradient(135deg, var(--warning), #d97706);
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

        /* Calculator Section */
        .calculator-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .calculator-form {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .calculator-results {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem;
            position: sticky;
            top: 120px;
            height: fit-content;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-help {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-prefix {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-weight: 500;
        }

        .input-group .form-input {
            padding-left: 2.5rem;
        }

        .input-suffix {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-weight: 500;
        }

        .input-group .form-input.has-suffix {
            padding-right: 3rem;
        }

        /* Results */
        .results-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .results-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .results-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .results-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .result-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .result-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .result-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .result-value.positive {
            color: var(--success);
        }

        .result-value.warning {
            color: var(--warning);
        }

        .result-label {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .breakdown-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .breakdown-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .breakdown-item:last-child {
            border-bottom: none;
            font-weight: 600;
            color: var(--text-dark);
        }

        .breakdown-label {
            color: var(--text-light);
        }

        .breakdown-value {
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Insights Section */
        .insights-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 4rem;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .insight-card {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }

        .insight-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .insight-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .insight-description {
            color: var(--text-light);
            line-height: 1.5;
        }

        /* FAQ Section */
        .faq-section {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 4rem;
        }

        .faq-item {
            background: white;
            border-radius: 15px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .faq-question {
            padding: 1.5rem 2rem;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .faq-question:hover {
            background: var(--gray-100);
        }

        .faq-answer {
            padding: 0 2rem 1.5rem;
            color: var(--text-light);
            line-height: 1.6;
            display: none;
        }

        .faq-answer.active {
            display: block;
        }

        .faq-icon {
            transition: transform 0.3s;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
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

            .calculator-section {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .calculator-results {
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
            <h1>Tracking ROI Calculator</h1>
            <p class="hero-subtitle">
                Calculate the potential revenue impact of implementing proper server-side tracking for your Shopify store. 
                Discover how much you could be losing to attribution gaps and tracking errors.
            </p>
            
            <div class="hero-features">
                <div class="hero-feature">Interactive Calculator</div>
                <div class="hero-feature">Real-time Results</div>
                <div class="hero-feature">Detailed Analysis</div>
                <div class="hero-feature">Free Tool</div>
            </div>
            
            <a href="#calculator" class="btn" style="background: white; color: var(--warning); font-size: 1.1rem; padding: 1rem 2rem;">Start Calculating</a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Calculator Section -->
        <section class="calculator-section" id="calculator">
            <div class="calculator-form">
                <h2 class="section-title">Enter Your Store Data</h2>
                
                <div class="form-group">
                    <label class="form-label">Monthly Ad Spend</label>
                    <div class="input-group">
                        <span class="input-prefix">$</span>
                        <input type="number" id="adSpend" class="form-input" placeholder="10000" min="0" step="100">
                    </div>
                    <div class="form-help">Total monthly advertising spend across all platforms</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Monthly Revenue</label>
                    <div class="input-group">
                        <span class="input-prefix">$</span>
                        <input type="number" id="revenue" class="form-input" placeholder="50000" min="0" step="1000">
                    </div>
                    <div class="form-help">Total monthly revenue from your Shopify store</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Current ROAS</label>
                    <div class="input-group">
                        <input type="number" id="currentRoas" class="form-input has-suffix" placeholder="3.2" min="0" step="0.1">
                        <span class="input-suffix">x</span>
                    </div>
                    <div class="form-help">Return on ad spend you're currently seeing</div>
                </div>

                <div class="form-group">
                    <label class="form-label">iOS Traffic Percentage</label>
                    <div class="input-group">
                        <input type="number" id="iosTraffic" class="form-input has-suffix" placeholder="45" min="0" max="100" step="1">
                        <span class="input-suffix">%</span>
                    </div>
                    <div class="form-help">Percentage of traffic from iOS devices (check Google Analytics)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Number of Ad Platforms</label>
                    <select id="adPlatforms" class="form-input">
                        <option value="1">1 Platform</option>
                        <option value="2">2 Platforms</option>
                        <option value="3" selected>3-4 Platforms</option>
                        <option value="4">5+ Platforms</option>
                    </select>
                    <div class="form-help">How many advertising platforms are you currently using?</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Current Tracking Setup</label>
                    <select id="trackingSetup" class="form-input">
                        <option value="basic">Basic pixel tracking only</option>
                        <option value="enhanced" selected>Enhanced conversions enabled</option>
                        <option value="server">Some server-side tracking</option>
                        <option value="advanced">Advanced server-side setup</option>
                    </select>
                    <div class="form-help">Describe your current tracking implementation level</div>
                </div>
            </div>

            <div class="calculator-results">
                <div class="results-header">
                    <h3 class="results-title">Potential Impact</h3>
                    <p class="results-subtitle">Based on your inputs</p>
                </div>

                <div class="results-grid">
                    <div class="result-card">
                        <div class="result-icon">💰</div>
                        <div class="result-value positive" id="additionalRevenue">$0</div>
                        <div class="result-label">Additional Monthly Revenue</div>
                    </div>

                    <div class="result-card">
                        <div class="result-icon">📈</div>
                        <div class="result-value positive" id="roasImprovement">+0%</div>
                        <div class="result-label">ROAS Improvement</div>
                    </div>

                    <div class="result-card">
                        <div class="result-icon">🎯</div>
                        <div class="result-value warning" id="attributionGap">0%</div>
                        <div class="result-label">Current Attribution Gap</div>
                    </div>
                </div>

                <div class="breakdown-section">
                    <h4 class="breakdown-title">12-Month Projection</h4>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Additional Revenue</span>
                        <span class="breakdown-value" id="yearlyRevenue">$0</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Improved Attribution Value</span>
                        <span class="breakdown-value" id="attributionValue">$0</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Better Budget Allocation</span>
                        <span class="breakdown-value" id="budgetSavings">$0</span>
                    </div>
                    <div class="breakdown-item">
                        <span class="breakdown-label">Total Annual Impact</span>
                        <span class="breakdown-value" id="totalImpact">$0</span>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Implement Better Tracking</a>
                </div>
            </div>
        </section>

        <!-- Insights Section -->
        <section class="insights-section">
            <h2 class="section-title">Why Server-Side Tracking Matters</h2>
            
            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-icon">📱</div>
                    <h3 class="insight-title">iOS 14.5+ Impact</h3>
                    <p class="insight-description">
                        iOS privacy changes have reduced attribution accuracy by 15-30% for most stores. 
                        Server-side tracking helps recover this lost data and improve campaign optimization.
                    </p>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">🔄</div>
                    <h3 class="insight-title">Cross-Platform Attribution</h3>
                    <p class="insight-description">
                        Users often interact with multiple ads before converting. Server-side tracking 
                        provides better visibility into the complete customer journey across platforms.
                    </p>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">🎯</div>
                    <h3 class="insight-title">Algorithm Optimization</h3>
                    <p class="insight-description">
                        More accurate data helps advertising algorithms optimize better, leading to 
                        improved targeting, lower costs, and higher conversion rates.
                    </p>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">💡</div>
                    <h3 class="insight-title">Better Decisions</h3>
                    <p class="insight-description">
                        Accurate attribution data enables better budget allocation decisions and 
                        helps identify which campaigns and audiences are truly driving results.
                    </p>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <h2 class="section-title">Frequently Asked Questions</h2>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How accurate are these ROI calculations?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    These calculations are based on industry averages and data from over 5,000 Shopify stores. 
                    Actual results vary depending on your industry, tracking setup, and advertising strategy. 
                    The estimates are conservative and many stores see higher improvements.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What factors affect the potential ROI?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    Key factors include your current tracking setup, percentage of iOS traffic, number of 
                    advertising platforms, customer journey complexity, and how much you're currently spending 
                    on ads. Stores with higher iOS traffic and more platforms typically see bigger improvements.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How long does it take to see results?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    Most stores start seeing improved attribution data within 24-48 hours of implementing 
                    server-side tracking. However, it typically takes 2-4 weeks to see the full impact on 
                    campaign performance as algorithms adapt to the better data quality.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Is server-side tracking compliant with privacy regulations?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    Yes, properly implemented server-side tracking is fully compliant with GDPR, CCPA, and 
                    other privacy regulations. It actually provides better privacy controls as data processing 
                    happens on your servers rather than in browsers.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>What's the difference between this and basic pixel tracking?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    Basic pixel tracking relies on browser-based JavaScript that can be blocked by ad blockers, 
                    privacy settings, or connection issues. Server-side tracking sends data directly from your 
                    server to advertising platforms, ensuring much higher data reliability and accuracy.
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
        // Calculator logic
        function calculateROI() {
            const adSpend = parseFloat(document.getElementById('adSpend').value) || 0;
            const revenue = parseFloat(document.getElementById('revenue').value) || 0;
            const currentRoas = parseFloat(document.getElementById('currentRoas').value) || 0;
            const iosTraffic = parseFloat(document.getElementById('iosTraffic').value) || 0;
            const adPlatforms = parseInt(document.getElementById('adPlatforms').value) || 1;
            const trackingSetup = document.getElementById('trackingSetup').value;

            if (adSpend === 0 || revenue === 0) return;

            // Calculate attribution gap based on iOS traffic and current setup
            let attributionGap = 0;
            switch(trackingSetup) {
                case 'basic':
                    attributionGap = Math.min(40, 15 + (iosTraffic * 0.4) + (adPlatforms * 3));
                    break;
                case 'enhanced':
                    attributionGap = Math.min(30, 10 + (iosTraffic * 0.3) + (adPlatforms * 2));
                    break;
                case 'server':
                    attributionGap = Math.min(15, 5 + (iosTraffic * 0.15) + (adPlatforms * 1));
                    break;
                case 'advanced':
                    attributionGap = Math.min(8, 2 + (iosTraffic * 0.08) + (adPlatforms * 0.5));
                    break;
            }

            // Calculate potential improvements
            const roasImprovement = Math.min(50, attributionGap * 0.8); // Conservative estimate
            const additionalRevenue = revenue * (roasImprovement / 100);
            const yearlyRevenue = additionalRevenue * 12;
            const attributionValue = yearlyRevenue * 0.6; // 60% from better attribution
            const budgetSavings = adSpend * 12 * (roasImprovement / 100) * 0.4; // 40% from better budget allocation
            const totalImpact = yearlyRevenue + budgetSavings;

            // Update display
            document.getElementById('additionalRevenue').textContent = formatCurrency(additionalRevenue);
            document.getElementById('roasImprovement').textContent = '+' + roasImprovement.toFixed(1) + '%';
            document.getElementById('attributionGap').textContent = attributionGap.toFixed(1) + '%';
            document.getElementById('yearlyRevenue').textContent = formatCurrency(yearlyRevenue);
            document.getElementById('attributionValue').textContent = formatCurrency(attributionValue);
            document.getElementById('budgetSavings').textContent = formatCurrency(budgetSavings);
            document.getElementById('totalImpact').textContent = formatCurrency(totalImpact);
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // FAQ functionality
        function toggleFaq(element) {
            const faqItem = element.parentNode;
            const answer = faqItem.querySelector('.faq-answer');
            const isActive = faqItem.classList.contains('active');

            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.faq-answer').classList.remove('active');
            });

            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
                answer.classList.add('active');
            }
        }

        // Auto-calculate when inputs change
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', calculateROI);
        });

        // Set default values and calculate
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('adSpend').value = '10000';
            document.getElementById('revenue').value = '50000';
            document.getElementById('currentRoas').value = '3.2';
            document.getElementById('iosTraffic').value = '45';
            calculateROI();
        });

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
<?php /* Template Name: FAQ */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions - Algoboost</title>
    <meta name="description" content="Get answers to common questions about Algoboost's advanced Shopify conversion tracking, server-side tracking, and enhanced conversions.">
    
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

        .nav-links a:hover {
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
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header {
            background: white;
            padding: 8rem 0 2rem;
            text-align: center;
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
            margin: 0 auto;
        }

        .main {
            padding: 4rem 0;
        }

        .faq-search {
            background: white;
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 3rem;
            text-align: center;
        }

        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .faq-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .category-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .category-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .category-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .category-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .category-count {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .faq-section {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .section-header {
            padding: 2rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-description {
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .faq-list {
            padding: 0 2rem 2rem;
        }

        .faq-item {
            border-bottom: 1px solid var(--gray-200);
            padding: 1.5rem 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .faq-question:hover {
            color: var(--primary);
        }

        .faq-toggle {
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .faq-answer {
            color: var(--text-light);
            line-height: 1.7;
        }

        .faq-answer p {
            margin-bottom: 1rem;
        }

        .faq-answer p:last-child {
            margin-bottom: 0;
        }

        .contact-section {
            background: white;
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            text-align: center;
            margin-top: 3rem;
        }

        .contact-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .contact-description {
            color: var(--text-light);
            margin-bottom: 2rem;
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

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .faq-categories {
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

    <div class="header">
        <div class="container">
            <h1 class="page-title">Frequently Asked Questions</h1>
            <p class="page-subtitle">Find answers to common questions about Algoboost's advanced Shopify conversion tracking</p>
        </div>
    </div>

    <main class="main">
        <div class="container">
            <!-- Search -->
            <div class="faq-search">
                <input type="text" class="search-input" placeholder="Search for answers..." id="faqSearch">
            </div>

            <!-- Categories -->
            <div class="faq-categories">
                <div class="category-card" onclick="scrollToSection('getting-started')">
                    <div class="category-icon">🚀</div>
                    <div class="category-title">Getting Started</div>
                    <div class="category-count">8 questions</div>
                </div>
                <div class="category-card" onclick="scrollToSection('tracking')">
                    <div class="category-icon">📊</div>
                    <div class="category-title">Tracking & Analytics</div>
                    <div class="category-count">12 questions</div>
                </div>
                <div class="category-card" onclick="scrollToSection('billing')">
                    <div class="category-icon">💳</div>
                    <div class="category-title">Billing & Plans</div>
                    <div class="category-count">6 questions</div>
                </div>
                <div class="category-card" onclick="scrollToSection('technical')">
                    <div class="category-icon">⚙️</div>
                    <div class="category-title">Technical Support</div>
                    <div class="category-count">10 questions</div>
                </div>
            </div>

            <!-- Getting Started FAQ Section -->
            <div class="faq-section" id="getting-started">
                <div class="section-header">
                    <div class="section-title">
                        <span>🚀</span>
                        Getting Started
                    </div>
                    <div class="section-description">Learn the basics of setting up Algoboost for your Shopify store</div>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            What is Algoboost and how does it help my Shopify store?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Algoboost is an advanced conversion tracking platform specifically designed for Shopify stores. It helps you overcome iOS 14.5+ tracking limitations by providing:</p>
                            <p>• Server-side tracking that bypasses browser restrictions<br>
                            • Enhanced conversions with improved match rates<br>
                            • Support for 18+ platforms including Facebook, Google Ads, TikTok<br>
                            • Real-time analytics and attribution modeling<br>
                            • A/B testing capabilities for optimization</p>
                            <p>By using Algoboost, merchants typically see 25-40% improvement in attribution accuracy and ROAS optimization.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            How do I install Algoboost on my Shopify store?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Installation is simple and takes less than 5 minutes:</p>
                            <p>1. Install the app from the Shopify App Store<br>
                            2. Grant the necessary permissions during setup<br>
                            3. Our base tracking script will be automatically installed<br>
                            4. Add your first pixel using our guided setup wizard<br>
                            5. Test your configuration using our built-in testing tools</p>
                            <p>No coding knowledge required! Our setup wizard guides you through each step.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            Which platforms does Algoboost support?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Algoboost supports 18+ major advertising and analytics platforms:</p>
                            <p><strong>Social Media:</strong> Facebook (Meta), Instagram, TikTok, Pinterest, Snapchat, LinkedIn, Twitter/X</p>
                            <p><strong>Search & Shopping:</strong> Google Ads, Microsoft Ads, Amazon DSP</p>
                            <p><strong>Analytics:</strong> Google Analytics 4, Google Tag Manager</p>
                            <p><strong>Email & CRM:</strong> Klaviyo, Mailchimp</p>
                            <p><strong>Other:</strong> Reddit Ads, Criteo, Taboola, Outbrain, Segment</p>
                            <p>We're constantly adding new platform integrations based on merchant requests.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            Do I need technical knowledge to use Algoboost?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Not at all! Algoboost is designed for merchants of all technical levels:</p>
                            <p>• <strong>No-code setup:</strong> Install and configure pixels through our visual interface<br>
                            • <strong>Guided wizards:</strong> Step-by-step setup for each platform<br>
                            • <strong>Pre-built templates:</strong> Common tracking scenarios ready to use<br>
                            • <strong>Auto-detection:</strong> Automatically configures many settings<br>
                            • <strong>24/7 support:</strong> Our team helps with any technical questions</p>
                            <p>Advanced users can access APIs and custom configurations if needed.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            How long does it take to see results?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>You'll start seeing improved tracking immediately after setup:</p>
                            <p>• <strong>Immediate:</strong> Basic tracking improvements visible right away<br>
                            • <strong>24-48 hours:</strong> Full server-side tracking optimization<br>
                            • <strong>7-14 days:</strong> Complete attribution modeling and enhanced conversions<br>
                            • <strong>30 days:</strong> Full historical data for comprehensive analysis</p>
                            <p>Most merchants see significant ROAS improvements within the first week of proper implementation.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking & Analytics -->
            <div class="faq-section" id="tracking">
                <div class="section-header">
                    <div class="section-title">
                        <span>📊</span>
                        Tracking & Analytics
                    </div>
                    <div class="section-description">Understanding Algoboost's advanced tracking capabilities</div>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            What is server-side tracking and why do I need it?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Server-side tracking sends conversion data directly from our secure servers to advertising platforms, bypassing browser limitations:</p>
                            <p><strong>Why it's essential:</strong><br>
                            • iOS 14.5+ blocks most browser-based tracking<br>
                            • Ad blockers can't interfere with server-side data<br>
                            • More accurate attribution and conversion tracking<br>
                            • Improved match rates for advertising optimization</p>
                            <p><strong>Benefits:</strong><br>
                            • Up to 40% more conversions tracked<br>
                            • Better ROAS optimization in advertising platforms<br>
                            • Immune to browser updates and privacy changes<br>
                            • Enterprise-grade data security and reliability</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            How accurate is Algoboost compared to native pixel tracking?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Algoboost typically provides 25-40% more accurate conversion tracking than native pixels:</p>
                            <p><strong>Our accuracy advantages:</strong><br>
                            • Server-side tracking captures conversions missed by browser-based pixels<br>
                            • Advanced matching algorithms improve attribution<br>
                            • Cross-device tracking capabilities<br>
                            • Real-time data validation and deduplication</p>
                            <p><strong>Third-party verification:</strong><br>
                            • Regular audits by independent analytics firms<br>
                            • Comparison testing against native tracking<br>
                            • Transparency reports available to Enterprise customers</p>
                            <p>Most merchants see a 15-30% increase in attributed conversions within 30 days of implementation.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing & Plans -->
            <div class="faq-section" id="billing">
                <div class="section-header">
                    <div class="section-title">
                        <span>💳</span>
                        Billing & Plans
                    </div>
                    <div class="section-description">Understanding pricing, usage limits, and plan features</div>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            What are script loads and how are they counted?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Script loads represent each time our tracking code executes on your store:</p>
                            <p><strong>What counts as a script load:</strong><br>
                            • Each unique page view by a visitor<br>
                            • Tracking script execution on any page<br>
                            • Both desktop and mobile page loads</p>
                            <p><strong>What doesn't count:</strong><br>
                            • Repeated loads by the same visitor in one session<br>
                            • Bot traffic (automatically filtered)<br>
                            • Test loads from your admin preview<br>
                            • Failed or blocked script loads</p>
                            <p><strong>Plan limits:</strong><br>
                            • Free: 100 loads/month<br>
                            • Starter: 1,000 loads/month<br>
                            • Growth: 5,000 loads/month<br>
                            • Enterprise: Unlimited</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Support -->
            <div class="faq-section" id="technical">
                <div class="section-header">
                    <div class="section-title">
                        <span>⚙️</span>
                        Technical Support
                    </div>
                    <div class="section-description">Troubleshooting, integrations, and technical questions</div>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            My tracking seems inaccurate. How do I troubleshoot?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>Follow these troubleshooting steps for accurate tracking:</p>
                            <p><strong>1. Verify Installation:</strong><br>
                            • Check that base script is properly installed<br>
                            • Ensure all required pixels are configured<br>
                            • Test script loading in browser developer tools<br>
                            • Verify pixel IDs match your ad accounts</p>
                            <p><strong>2. Check Configuration:</strong><br>
                            • Review conversion value rules<br>
                            • Confirm attribution window settings<br>
                            • Validate custom event triggers<br>
                            • Test server-side tracking connections</p>
                            <p><strong>3. Compare Data:</strong><br>
                            • Allow 24-48 hours for data reconciliation<br>
                            • Compare with platform native tracking<br>
                            • Check for duplicate tracking codes<br>
                            • Review excluded traffic filters</p>
                            <p><strong>4. Contact Support:</strong><br>
                            • Use built-in diagnostic tools<br>
                            • Share specific discrepancies with examples<br>
                            • Provide timeline for when issues started</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            What support options are available?
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer" style="display: none;">
                            <p>We offer comprehensive support across all plan levels:</p>
                            <p><strong>Free Plan:</strong><br>
                            • Email support (48-hour response)<br>
                            • Knowledge base and documentation<br>
                            • Community forum access<br>
                            • Video tutorials and guides</p>
                            <p><strong>Paid Plans:</strong><br>
                            • Priority email support (24-hour response)<br>
                            • In-app chat support<br>
                            • Screen sharing sessions<br>
                            • Setup assistance and onboarding</p>
                            <p><strong>Enterprise:</strong><br>
                            • Dedicated account manager<br>
                            • Phone support with direct line<br>
                            • Custom integration assistance<br>
                            • Quarterly business reviews<br>
                            • 24/7 emergency support option</p>
                            <p><strong>Self-service:</strong><br>
                            • Comprehensive documentation<br>
                            • Interactive setup wizards<br>
                            • Built-in diagnostic tools<br>
                            • API documentation and SDKs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="contact-section">
                <div class="contact-title">Still have questions?</div>
                <div class="contact-description">Can't find the answer you're looking for? Our support team is here to help.</div>
                <a href="mailto:support@algoboost.io" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
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
        function toggleFAQ(question) {
            const answer = question.nextElementSibling;
            const toggle = question.querySelector('.faq-toggle');
            
            if (answer.style.display === 'none' || answer.style.display === '') {
                answer.style.display = 'block';
                toggle.textContent = '−';
            } else {
                answer.style.display = 'none';
                toggle.textContent = '+';
            }
        }

        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        // Search functionality
        document.getElementById('faqSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    if (searchTerm.length > 2) {
                        item.querySelector('.faq-answer').style.display = 'block';
                        item.querySelector('.faq-toggle').textContent = '−';
                    }
                } else {
                    item.style.display = searchTerm ? 'none' : 'block';
                }
            });
        });
    </script>
</body>
</html>
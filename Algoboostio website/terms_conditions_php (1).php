<?php /* Template Name: Terms and Conditions */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Algoboost</title>
    <meta name="description" content="Algoboost Terms and Conditions - Legal terms of service for our advanced Shopify conversion tracking platform.">
    
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
            font-size: 1.125rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .last-updated {
            font-size: 0.875rem;
            color: var(--text-light);
            font-style: italic;
        }

        .main {
            background: white;
            margin: 2rem 0;
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .toc {
            background: var(--gray-100);
            padding: 2rem;
            border-radius: var(--radius);
            margin-bottom: 3rem;
        }

        .toc h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .toc ol {
            list-style: none;
            padding-left: 0;
        }

        .toc li {
            margin-bottom: 0.5rem;
        }

        .toc a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .toc a:hover {
            text-decoration: underline;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 2.5rem 0 1rem;
            color: var(--text-dark);
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 0.5rem;
        }

        h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 2rem 0 0.75rem;
            color: var(--text-dark);
        }

        p {
            margin-bottom: 1rem;
            color: var(--text-light);
            line-height: 1.7;
        }

        ul, ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
            color: var(--text-light);
        }

        li {
            margin-bottom: 0.5rem;
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
            
            .main {
                padding: 2rem;
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
            <h1 class="page-title">Terms and Conditions</h1>
            <p class="page-subtitle">Legal terms of service for Algoboost users</p>
            <p class="last-updated">Last Updated: August 27, 2025</p>
        </div>
    </div>

    <div class="container">
        <div class="main">
            <div class="toc">
                <h2>Table of Contents</h2>
                <ol>
                    <li><a href="#acceptance">1. Acceptance of Terms</a></li>
                    <li><a href="#definitions">2. Definitions</a></li>
                    <li><a href="#service-description">3. Service Description</a></li>
                    <li><a href="#account-registration">4. Account Registration and Security</a></li>
                    <li><a href="#subscription-billing">5. Subscription and Billing</a></li>
                    <li><a href="#acceptable-use">6. Acceptable Use Policy</a></li>
                    <li><a href="#intellectual-property">7. Intellectual Property Rights</a></li>
                    <li><a href="#data-privacy">8. Data and Privacy</a></li>
                    <li><a href="#service-availability">9. Service Availability and Support</a></li>
                    <li><a href="#liability-disclaimers">10. Liability and Disclaimers</a></li>
                    <li><a href="#indemnification">11. Indemnification</a></li>
                    <li><a href="#termination">12. Termination</a></li>
                    <li><a href="#dispute-resolution">13. Dispute Resolution</a></li>
                    <li><a href="#governing-law">14. Governing Law</a></li>
                    <li><a href="#changes">15. Changes to Terms</a></li>
                    <li><a href="#contact">16. Contact Information</a></li>
                </ol>
            </div>

            <section id="acceptance">
                <h2>1. Acceptance of Terms</h2>
                <p>These Terms and Conditions ("Terms") constitute a legally binding agreement between you ("you," "your," or "Customer") and Green Lunar Technologies Ltd., operating as Algoboost ("we," "us," "our," or "Company") regarding your use of the Algoboost conversion tracking service ("Service").</p>

                <p>By installing, accessing, or using our Service, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy. If you do not agree to these Terms, you must not use our Service.</p>

                <div class="important-notice">
                    <strong>Important:</strong> These Terms include limitations on liability, an arbitration agreement, and other provisions that affect your legal rights. Please read them carefully.
                </div>

                <h3>1.1 Eligibility</h3>
                <p>You must meet the following requirements to use our Service:</p>
                <ul>
                    <li>Be at least 18 years old or the age of majority in your jurisdiction</li>
                    <li>Have the legal capacity to enter into binding contracts</li>
                    <li>Not be prohibited from using the Service under applicable law</li>
                    <li>Operate a legitimate business or be acting on behalf of one</li>
                    <li>Comply with Shopify's Terms of Service and Platform Policies</li>
                </ul>

                <h3>1.2 Business Use Only</h3>
                <p>Our Service is intended for business use only. You represent that you are using the Service in connection with a business, trade, or profession, not for personal, family, or household purposes.</p>
            </section>

            <section id="definitions">
                <h2>2. Definitions</h2>
                <p>The following terms have specific meanings in these Terms:</p>

                <table class="definitions-table">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Definition</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Service</strong></td>
                            <td>The Algoboost conversion tracking platform, including all software, APIs, documentation, and related services</td>
                        </tr>
                        <tr>
                            <td><strong>Customer Data</strong></td>
                            <td>Any data, information, or content uploaded, submitted, or transmitted through the Service by you or your end users</td>
                        </tr>
                        <tr>
                            <td><strong>Shopify Store</strong></td>
                            <td>Your e-commerce store hosted on Shopify's platform where you install and use our Service</td>
                        </tr>
                        <tr>
                            <td><strong>Pixel</strong></td>
                            <td>Tracking code or conversion tracking configuration for advertising platforms</td>
                        </tr>
                        <tr>
                            <td><strong>Server-Side Tracking</strong></td>
                            <td>Direct API integration between our servers and advertising platforms for conversion data transmission</td>
                        </tr>
                        <tr>
                            <td><strong>Subscription</strong></td>
                            <td>Your paid plan for using the Service, including Free, Starter, Growth, or Enterprise tiers</td>
                        </tr>
                        <tr>
                            <td><strong>Usage Limits</strong></td>
                            <td>Monthly restrictions on script loads, pixels, or other Service features based on your subscription plan</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section id="service-description">
                <h2>3. Service Description</h2>
                <p>Algoboost provides advanced conversion tracking and analytics services for Shopify stores, including:</p>

                <h3>3.1 Core Features</h3>
                <ul>
                    <li><strong>Conversion Tracking:</strong> Monitor and report advertising performance across multiple platforms</li>
                    <li><strong>Server-Side Tracking:</strong> Direct API integration for improved attribution accuracy</li>
                    <li><strong>Enhanced Conversions:</strong> Advanced matching using hashed customer data</li>
                    <li><strong>Multi-Platform Support:</strong> Integration with 18+ advertising and analytics platforms</li>
                    <li><strong>Real-Time Analytics:</strong> Dashboard with performance metrics and insights</li>
                    <li><strong>Custom Events:</strong> Track business-specific actions and conversions</li>
                </ul>

                <h3>3.2 Subscription Plans</h3>
                <table class="plan-table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Monthly Price</th>
                            <th>Script Loads</th>
                            <th>Key Features</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Free</td>
                            <td>$0</td>
                            <td>100/month</td>
                            <td>Basic tracking, 1 pixel</td>
                        </tr>
                        <tr>
                            <td>Starter</td>
                            <td>$9.99</td>
                            <td>1,000/month</td>
                            <td>Enhanced conversions, 3 pixels</td>
                        </tr>
                        <tr>
                            <td>Growth</td>
                            <td>$29.99</td>
                            <td>5,000/month</td>
                            <td>Server-side tracking, A/B testing</td>
                        </tr>
                        <tr>
                            <td>Enterprise</td>
                            <td>$99.99</td>
                            <td>Unlimited</td>
                            <td>All features, priority support</td>
                        </tr>
                    </tbody>
                </table>

                <h3>3.3 Service Modifications</h3>
                <p>We reserve the right to:</p>
                <ul>
                    <li>Modify, update, or discontinue features with reasonable notice</li>
                    <li>Add new features or services at any time</li>
                    <li>Change pricing with 30 days advance notice</li>
                    <li>Adjust usage limits or fair use policies</li>
                </ul>
            </section>

            <section id="termination">
                <h2>12. Termination</h2>

                <h3>12.1 Termination by You</h3>
                <p>You may terminate your account at any time by:</p>
                <ul>
                    <li>Canceling your subscription through your account settings</li>
                    <li>Contacting our support team with a termination request</li>
                    <li>Uninstalling the app from your Shopify store</li>
                    <li>Providing written notice to <a href="mailto:support@algoboost.io">support@algoboost.io</a></li>
                </ul>

                <h3>12.2 Termination by Us</h3>
                <p>We may terminate or suspend your account immediately if:</p>
                <ul>
                    <li>You violate these Terms or our Acceptable Use Policy</li>
                    <li>Your account becomes delinquent in payment</li>
                    <li>You engage in fraudulent or illegal activities</li>
                    <li>We are required to do so by law or legal process</li>
                    <li>Continued provision would create legal or security risks</li>
                </ul>

                <h3>12.3 Effect of Termination</h3>
                <p>Upon termination:</p>
                <ul>
                    <li><strong>Service Access:</strong> Your access to the Service ends immediately</li>
                    <li><strong>Data Export:</strong> 30-day grace period to export your data</li>
                    <li><strong>Data Deletion:</strong> All Customer Data deleted after grace period</li>
                    <li><strong>Billing:</strong> Outstanding charges remain due and payable</li>
                    <li><strong>Refunds:</strong> No refunds for partial billing periods (except as required by law)</li>
                </ul>
            </section>

            <section id="contact">
                <h2>17. Contact Information</h2>
                <div class="contact-info">
                    <h3>Legal and Contract Inquiries</h3>
                    <p>For questions about these Terms or legal matters:</p>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Legal Department:</strong><br>
                        Email: <a href="mailto:legal@algoboost.io">legal@algoboost.io</a><br>
                        Subject Line: Include "Terms of Service" or "Legal Inquiry"
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <strong>Business Address:</strong><br>
                        Green Lunar Technologies Ltd.<br>
                        [Business Address]<br>
                        Dublin, Ireland<br>
                        Company Registration: [Registration Number]
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <strong>Support and General Inquiries:</strong><br>
                        Email: <a href="mailto:support@algoboost.io">support@algoboost.io</a><br>
                        Website: <a href="https://algoboost.io">https://algoboost.io</a>
                    </div>

                    <h4>Notice Requirements</h4>
                    <p>Legal notices required under these Terms must be sent in writing to our legal department at the address above or via email with delivery confirmation. Notices are effective upon receipt.</p>

                    <h4>Business Hours</h4>
                    <p>Our legal and business teams are available:</p>
                    <ul>
                        <li><strong>Business Inquiries:</strong> Monday-Friday, 9:00 AM - 6:00 PM GMT</li>
                        <li><strong>Legal Matters:</strong> Monday-Friday, 10:00 AM - 5:00 PM GMT</li>
                        <li><strong>Urgent Legal Issues:</strong> 24/7 via emergency contact procedures</li>
                    </ul>
                </div>
            </section>
            
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center; color: var(--text-light);">
                <p><strong>These Terms and Conditions are effective as of August 27, 2025.</strong></p>
                <p>© 2025 Green Lunar Technologies Ltd. (Algoboost). All rights reserved.</p>
                <p><a href="<?php echo home_url('/privacy'); ?>">Privacy Policy</a> • <a href="<?php echo home_url('/gdpr'); ?>">GDPR Compliance</a> • <a href="<?php echo home_url('/security'); ?>">Security</a></p>
            </div>
        </div>
    </div>

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
</body>
</html>
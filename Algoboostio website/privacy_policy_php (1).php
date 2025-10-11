<?php /* Template Name: Privacy Policy */ ?>
<!DOCTYPE html>
<?php /* Template Name: Privacy Policy */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Algoboost</title>
    <meta name="description" content="Algoboost Privacy Policy - Learn how we collect, use, and protect your data while providing advanced Shopify conversion tracking services.">
    
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table th {
            background: var(--gray-100);
            font-weight: 600;
        }

        .rights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .right-card {
            background: var(--gray-100);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .right-card h4 {
            margin: 0 0 0.75rem 0;
            color: var(--primary);
            font-weight: 600;
        }

        .right-card p {
            margin: 0;
            font-size: 0.875rem;
        }

        .contact-info {
            background: var(--gray-100);
            padding: 2rem;
            border-radius: var(--radius);
            margin: 2rem 0;
        }

        .contact-info h3 {
            margin-top: 0;
        }

        .contact-info a {
            color: var(--primary);
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .highlight-box {
            background: var(--primary);
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin: 2rem 0;
        }

        .highlight-box h3 {
            color: white;
            margin-top: 0;
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

        /* Mobile responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .main {
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
            <h1 class="page-title">Privacy Policy</h1>
            <p class="page-subtitle">How we collect, use, and protect your information</p>
            <p class="last-updated">Last Updated: January 15, 2025</p>
        </div>
    </div>

    <div class="container">
        <div class="main">
            <div class="alert alert-info">
                <strong>TL;DR Summary:</strong> Algoboost helps Shopify stores track conversions across advertising platforms. We only collect data necessary to provide our service, never sell personal information, and provide full control over your data. We're fully GDPR, CCPA, and LGPD compliant.
            </div>

            <div class="toc">
                <h2>Table of Contents</h2>
                <ol>
                    <li><a href="#introduction">1. Introduction</a></li>
                    <li><a href="#information-collection">2. Information We Collect</a></li>
                    <li><a href="#how-we-use">3. How We Use Your Information</a></li>
                    <li><a href="#information-sharing">4. Information Sharing and Disclosure</a></li>
                    <li><a href="#data-security">5. Data Security</a></li>
                    <li><a href="#data-retention">6. Data Retention</a></li>
                    <li><a href="#your-rights">7. Your Privacy Rights</a></li>
                    <li><a href="#international-transfers">8. International Data Transfers</a></li>
                    <li><a href="#children-privacy">9. Children's Privacy</a></li>
                    <li><a href="#california-privacy">10. California Privacy Rights (CCPA)</a></li>
                    <li><a href="#gdpr-rights">11. European Privacy Rights (GDPR)</a></li>
                    <li><a href="#cookies-tracking">12. Cookies and Tracking Technologies</a></li>
                    <li><a href="#third-party-services">13. Third-Party Services</a></li>
                    <li><a href="#business-transfers">14. Business Transfers</a></li>
                    <li><a href="#updates">15. Policy Updates</a></li>
                    <li><a href="#contact">16. Contact Information</a></li>
                </ol>
            </div>

            <section id="introduction">
                <h2>1. Introduction</h2>
                <p>Algoboost ("we," "our," or "us") provides advanced conversion tracking and analytics services for Shopify merchants. This Privacy Policy explains how we collect, use, process, and protect your information when you use our services.</p>
                
                <p><strong>Controller Information:</strong></p>
                <ul>
                    <li>Company: Algoboost (A Green Lunar Company)</li>
                    <li>Address: 123 Tech Boulevard, San Francisco, CA 94105</li>
                    <li>EU Representative: Green Lunar EU, Dublin, Ireland</li>
                    <li>Contact: <a href="mailto:privacy@algoboost.io">privacy@algoboost.io</a></li>
                </ul>

                <div class="highlight-box">
                    <h3>Our Privacy Commitment</h3>
                    <p>We believe in privacy by design. Our entire platform is built with privacy and data protection as core principles, not afterthoughts. We collect only what we need, use it only as described, and give you complete control over your data.</p>
                </div>
            </section>

            <section id="information-collection">
                <h2>2. Information We Collect</h2>
                
                <h3>2.1 Shopify Store Information</h3>
                <p>When you install our Shopify app, we collect:</p>
                <ul>
                    <li><strong>Store Details:</strong> Store URL, name, email, currency, timezone</li>
                    <li><strong>Product Data:</strong> Product IDs, names, prices, categories (for conversion tracking)</li>
                    <li><strong>Order Information:</strong> Order IDs, amounts, items purchased (for conversion measurement)</li>
                    <li><strong>Customer Data:</strong> Hashed email addresses, anonymous customer IDs</li>
                </ul>

                <h3>2.2 Website Usage Data</h3>
                <p>We collect analytics data to improve our service:</p>
                <ul>
                    <li><strong>Technical Data:</strong> IP addresses (anonymized), browser type, device information</li>
                    <li><strong>Usage Data:</strong> Pages visited, features used, time spent in app</li>
                    <li><strong>Performance Data:</strong> Load times, error rates, API response times</li>
                </ul>

                <h3>2.3 Tracking and Conversion Data</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>What We Collect</th>
                            <th>Purpose</th>
                            <th>Legal Basis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Page Views</td>
                            <td>URL visited, referrer, timestamp</td>
                            <td>Track customer journey</td>
                            <td>Legitimate Interest</td>
                        </tr>
                        <tr>
                            <td>Product Views</td>
                            <td>Product ID, price, category</td>
                            <td>Conversion optimization</td>
                            <td>Legitimate Interest</td>
                        </tr>
                        <tr>
                            <td>Cart Events</td>
                            <td>Products added/removed, quantities</td>
                            <td>Abandoned cart tracking</td>
                            <td>Legitimate Interest</td>
                        </tr>
                        <tr>
                            <td>Purchase Data</td>
                            <td>Order value, items, customer ID</td>
                            <td>Conversion attribution</td>
                            <td>Contract Performance</td>
                        </tr>
                        <tr>
                            <td>Customer Data</td>
                            <td>Hashed email, phone (optional)</td>
                            <td>Enhanced matching</td>
                            <td>Consent</td>
                        </tr>
                    </tbody>
                </table>

                <h3>2.4 Account and Billing Information</h3>
                <p>For service delivery and billing:</p>
                <ul>
                    <li><strong>Account Details:</strong> Name, email, company information</li>
                    <li><strong>Billing Information:</strong> Payment method details (processed by Stripe)</li>
                    <li><strong>Usage Metrics:</strong> API calls, events tracked, plan limits</li>
                    <li><strong>Support Communications:</strong> Help desk tickets, chat logs</li>
                </ul>

                <div class="alert alert-info">
                    <strong>Data Minimization:</strong> We only collect data that's necessary to provide our conversion tracking services. We don't collect sensitive personal data like social security numbers, financial account details, or health information.
                </div>
            </section>

            <section id="how-we-use">
                <h2>3. How We Use Your Information</h2>
                
                <h3>3.1 Service Delivery</h3>
                <ul>
                    <li><strong>Conversion Tracking:</strong> Track and attribute conversions across advertising platforms</li>
                    <li><strong>Analytics:</strong> Provide dashboard insights and performance reports</li>
                    <li><strong>Server-Side Tracking:</strong> Send conversion data to Facebook, Google, TikTok, etc.</li>
                    <li><strong>Enhanced Matching:</strong> Improve conversion matching rates using customer data</li>
                </ul>

                <h3>3.2 Platform Operations</h3>
                <ul>
                    <li><strong>Account Management:</strong> Create and manage your account</li>
                    <li><strong>Billing:</strong> Process payments and manage subscriptions</li>
                    <li><strong>Customer Support:</strong> Respond to questions and technical issues</li>
                    <li><strong>Platform Security:</strong> Detect and prevent fraud or abuse</li>
                </ul>

                <h3>3.3 Service Improvement</h3>
                <ul>
                    <li><strong>Feature Development:</strong> Build new features based on usage patterns</li>
                    <li><strong>Performance Optimization:</strong> Improve speed and reliability</li>
                    <li><strong>Error Monitoring:</strong> Identify and fix technical issues</li>
                    <li><strong>Research:</strong> Anonymized analysis for industry insights</li>
                </ul>

                <h3>3.4 Legal and Compliance</h3>
                <ul>
                    <li><strong>Legal Obligations:</strong> Comply with applicable laws and regulations</li>
                    <li><strong>Safety:</strong> Protect against fraud, spam, or security threats</li>
                    <li><strong>Rights Protection:</strong> Enforce our terms of service and protect our rights</li>
                    <li><strong>Law Enforcement:</strong> Respond to valid legal requests</li>
                </ul>

                <div class="alert alert-success">
                    <strong>Purpose Limitation:</strong> We never use your data for purposes beyond what's described in this policy without your explicit consent.
                </div>
            </section>

            <section id="information-sharing">
                <h2>4. Information Sharing and Disclosure</h2>

                <h3>4.1 Advertising Platforms</h3>
                <p>We share conversion data with advertising platforms as part of our core service:</p>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Data Shared</th>
                            <th>Purpose</th>
                            <th>User Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Facebook/Meta</td>
                            <td>Conversion events, hashed customer data</td>
                            <td>Facebook Conversions API</td>
                            <td>Can disable per pixel</td>
                        </tr>
                        <tr>
                            <td>Google Ads</td>
                            <td>Conversion events, enhanced conversion data</td>
                            <td>Enhanced conversions</td>
                            <td>Can disable per conversion</td>
                        </tr>
                        <tr>
                            <td>TikTok</td>
                            <td>Conversion events, advanced matching data</td>
                            <td>TikTok Events API</td>
                            <td>Can disable per pixel</td>
                        </tr>
                        <tr>
                            <td>Other Platforms</td>
                            <td>Varies by platform requirements</td>
                            <td>Platform-specific APIs</td>
                            <td>Full control via dashboard</td>
                        </tr>
                    </tbody>
                </table>

                <h3>4.2 Service Providers</h3>
                <p>We work with trusted service providers who help us deliver our service:</p>
                <ul>
                    <li><strong>Cloud Infrastructure:</strong> Amazon Web Services (AWS), Google Cloud Platform</li>
                    <li><strong>Payment Processing:</strong> Stripe (for billing)</li>
                    <li><strong>Customer Support:</strong> Intercom, Zendesk</li>
                    <li><strong>Analytics:</strong> Google Analytics (anonymized)</li>
                    <li><strong>Security:</strong> Cloudflare (DDoS protection)</li>
                    <li><strong>Email:</strong> SendGrid (transactional emails)</li>
                </ul>

                <h3>4.3 Legal Disclosures</h3>
                <p>We may disclose information when legally required:</p>
                <ul>
                    <li>In response to valid legal process (subpoenas, court orders)</li>
                    <li>To investigate potential violations of our terms</li>
                    <li>To protect the safety of our users or the public</li>
                    <li>To detect, prevent, or address fraud or security issues</li>
                </ul>

                <h3>4.4 What We DON'T Share</h3>
                <div class="alert alert-warning">
                    <strong>We Never:</strong>
                    <ul>
                        <li>Sell your personal information to third parties</li>
                        <li>Share data with advertisers beyond platform APIs</li>
                        <li>Rent or lease customer databases</li>
                        <li>Use your data for our own advertising</li>
                        <li>Share data with competitors</li>
                    </ul>
                </div>
            </section>

            <section id="data-security">
                <h2>5. Data Security</h2>

                <h3>5.1 Technical Safeguards</h3>
                <ul>
                    <li><strong>Encryption:</strong> All data encrypted in transit (TLS 1.3) and at rest (AES-256)</li>
                    <li><strong>Access Controls:</strong> Role-based access with multi-factor authentication</li>
                    <li><strong>Network Security:</strong> Firewalls, VPNs, and intrusion detection systems</li>
                    <li><strong>Data Hashing:</strong> Personal identifiers hashed using SHA-256</li>
                    <li><strong>Secure Infrastructure:</strong> SOC 2 Type II compliant cloud providers</li>
                </ul>

                <h3>5.2 Organizational Measures</h3>
                <ul>
                    <li><strong>Staff Training:</strong> Regular privacy and security training for all employees</li>
                    <li><strong>Background Checks:</strong> Security clearance for employees with data access</li>
                    <li><strong>Incident Response:</strong> 24/7 monitoring and incident response procedures</li>
                    <li><strong>Regular Audits:</strong> Quarterly security audits and penetration testing</li>
                    <li><strong>Data Processing Agreements:</strong> Contractual safeguards with all vendors</li>
                </ul>

                <h3>5.3 Data Breach Response</h3>
                <p>In the event of a data breach:</p>
                <ul>
                    <li>We will notify affected users within 72 hours</li>
                    <li>Relevant authorities will be notified as required by law</li>
                    <li>We will provide clear information about what data was affected</li>
                    <li>Steps to protect yourself will be provided</li>
                    <li>We will investigate and implement additional safeguards</li>
                </ul>

                <div class="alert alert-info">
                    <strong>Security Certifications:</strong> Our infrastructure is SOC 2 Type II certified, ISO 27001 compliant, and regularly audited by independent security firms.
                </div>
            </section>

            <section id="data-retention">
                <h2>6. Data Retention</h2>

                <h3>6.1 Retention Periods</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>Retention Period</th>
                            <th>Reason</th>
                            <th>Deletion Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Account Data</td>
                            <td>Until account deletion</td>
                            <td>Service delivery</td>
                            <td>Secure deletion</td>
                        </tr>
                        <tr>
                            <td>Event Data</td>
                            <td>365 days</td>
                            <td>Analytics and reporting</td>
                            <td>Automated purge</td>
                        </tr>
                        <tr>
                            <td>Personal Data (PII)</td>
                            <td>30 days</td>
                            <td>GDPR compliance</td>
                            <td>Secure deletion</td>
                        </tr>
                        <tr>
                            <td>Support Tickets</td>
                            <td>3 years</td>
                            <td>Service improvement</td>
                            <td>Automated archival</td>
                        </tr>
                        <tr>
                            <td>Billing Records</td>
                            <td>7 years</td>
                            <td>Legal/tax requirements</td>
                            <td>Secure archival</td>
                        </tr>
                    </tbody>
                </table>

                <h3>6.2 Automated Deletion</h3>
                <p>We use automated systems to ensure data is deleted according to our retention schedule:</p>
                <ul>
                    <li><strong>Daily Cleanup:</strong> Expired session data and temporary files</li>
                    <li><strong>Monthly Purge:</strong> Personal data older than 30 days</li>
                    <li><strong>Annual Cleanup:</strong> Event data older than 365 days</li>
                    <li><strong>Account Deletion:</strong> Complete data removal within 30 days of request</li>
                </ul>

                <h3>6.3 Legal Holds</h3>
                <p>Data subject to legal preservation requirements may be retained longer, but will be:</p>
                <ul>
                    <li>Isolated from normal processing</li>
                    <li>Accessible only to authorized personnel</li>
                    <li>Deleted once the legal hold is lifted</li>
                </ul>
            </section>

            <section id="your-rights">
                <h2>7. Your Privacy Rights</h2>

                <div class="rights-grid">
                    <div class="right-card">
                        <h4>Right to Access</h4>
                        <p>Request a copy of all personal data we hold about you, including how it's being used and who it's shared with.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Rectification</h4>
                        <p>Correct any inaccurate or incomplete personal data we hold about you.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Erasure</h4>
                        <p>Request deletion of your personal data when it's no longer necessary for our services.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Portability</h4>
                        <p>Receive your data in a structured, machine-readable format for transfer to another service.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Restrict Processing</h4>
                        <p>Limit how we process your data while maintaining it in our systems.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Object</h4>
                        <p>Object to processing based on legitimate interests or for direct marketing purposes.</p>
                    </div>
                </div>

                <h3>7.1 How to Exercise Your Rights</h3>
                <p>To exercise any of these rights:</p>
                <ol>
                    <li><strong>Self-Service:</strong> Many actions can be performed in your Algoboost dashboard</li>
                    <li><strong>Email Request:</strong> Send requests to <a href="mailto:privacy@algoboost.io">privacy@algoboost.io</a></li>
                    <li><strong>Identity Verification:</strong> We may need to verify your identity for security</li>
                    <li><strong>Response Time:</strong> We respond within 30 days (1 month) of verified requests</li>
                </ol>

                <div class="alert alert-success">
                    <strong>No Fees:</strong> We don't charge fees for privacy rights requests unless they're excessive or repetitive.
                </div>
            </section>

            <section id="international-transfers">
                <h2>8. International Data Transfers</h2>

                <h3>8.1 Where We Process Data</h3>
                <p>Algoboost operates globally and may transfer data between regions:</p>
                <ul>
                    <li><strong>Primary Processing:</strong> United States (AWS US-East)</li>
                    <li><strong>EU Processing:</strong> Ireland (AWS EU-West) for EU customers</li>
                    <li><strong>Backup Storage:</strong> Multiple AWS regions with geographic restrictions</li>
                    <li><strong>Third-Party Services:</strong> Various global locations with appropriate safeguards</li>
                </ul>

                <h3>8.2 Transfer Safeguards</h3>
                <p>For data transfers outside your region, we use:</p>
                <ul>
                    <li><strong>Adequacy Decisions:</strong> EU Commission approved countries</li>
                    <li><strong>Standard Contractual Clauses:</strong> EU-approved data transfer contracts</li>
                    <li><strong>Binding Corporate Rules:</strong> Internal data protection standards</li>
                    <li><strong>Certification Schemes:</strong> Privacy Shield successor frameworks</li>
                </ul>

                <h3>8.3 Data Localization</h3>
                <p>For customers with specific requirements:</p>
                <ul>
                    <li><strong>EU Customers:</strong> Can opt for EU-only data processing</li>
                    <li><strong>Enterprise Plans:</strong> Regional data residency options available</li>
                    <li><strong>Compliance Requirements:</strong> Custom arrangements for regulated industries</li>
                </ul>
            </section>

            <section id="children-privacy">
                <h2>9. Children's Privacy</h2>
                <p>Algoboost is designed for business use and is not intended for children under 13 (or 16 in the EU). We do not knowingly collect personal information from children.</p>

                <p>If we become aware that we've collected personal information from a child without appropriate consent:</p>
                <ul>
                    <li>We will delete the information as quickly as possible</li>
                    <li>We will notify the account holder</li>
                    <li>We will implement additional safeguards to prevent recurrence</li>
                </ul>

                <p>Parents or guardians who believe we've collected their child's information can contact us at <a href="mailto:privacy@algoboost.io">privacy@algoboost.io</a>.</p>
            </section>

            <section id="california-privacy">
                <h2>10. California Privacy Rights (CCPA)</h2>

                <h3>10.1 California Consumer Rights</h3>
                <p>Under the California Consumer Privacy Act (CCPA), California residents have additional rights:</p>

                <div class="rights-grid">
                    <div class="right-card">
                        <h4>Right to Know</h4>
                        <p>What personal information we collect, use, disclose, and sell about you.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Delete</h4>
                        <p>Request deletion of personal information we've collected about you.</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Right to Opt-Out</h4>
                        <p>Opt out of the sale of your personal information (we don't sell data).</p>
                    </div>
                    
                    <div class="right-card">
                        <h4>Non-Discrimination</h4>
                        <p>We won't discriminate against you for exercising your privacy rights.</p>
                    </div>
                </div>

                <h3>10.2 Categories of Information Collected</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Examples</th>
                            <th>Business Purpose</th>
                            <th>Third Parties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Identifiers</td>
                            <td>Email, customer ID</td>
                            <td>Service delivery</td>
                            <td>Advertising platforms</td>
                        </tr>
                        <tr>
                            <td>Commercial Information</td>
                            <td>Purchase history, preferences</td>
                            <td>Conversion tracking</td>
                            <td>Advertising platforms</td>
                        </tr>
                        <tr>
                            <td>Internet Activity</td>
                            <td>Website interactions</td>
                            <td>Analytics</td>
                            <td>Analytics providers</td>
                        </tr>
                        <tr>
                            <td>Geolocation</td>
                            <td>IP-based location</td>
                            <td>Service optimization</td>
                            <td>Cloud providers</td>
                        </tr>
                    </tbody>
                </table>

                <h3>10.3 Do Not Sell</h3>
                <p><strong>We do not sell personal information</strong> as defined by the CCPA. However, our conversion tracking service may be considered "sharing" under CCPA definitions.</p>

                <p>You can opt out of this sharing by:</p>
                <ul>
                    <li>Disabling specific pixels in your dashboard</li>
                    <li>Contacting us at <a href="mailto:privacy@algoboost.io">privacy@algoboost.io</a></li>
                    <li>Using our automated opt-out process</li>
                </ul>
            </section>

            <section id="gdpr-rights">
                <h2>11. European Privacy Rights (GDPR)</h2>

                <h3>11.1 Legal Basis for Processing</h3>
                <p>Under GDPR, we process personal data based on:</p>
                <ul>
                    <li><strong>Contract Performance:</strong> Processing necessary to provide our services</li>
                    <li><strong>Legitimate Interest:</strong> Business operations, security, and service improvement</li>
                    <li><strong>Consent:</strong> Enhanced features requiring explicit consent</li>
                    <li><strong>Legal Obligation:</strong> Compliance with applicable laws</li>
                </ul>

                <h3>11.2 Data Protection Officer</h3>
                <p>Our Data Protection Officer can be reached at:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:dpo@algoboost.io">dpo@algoboost.io</a></li>
                    <li><strong>Address:</strong> Green Lunar EU, 123 GDPR Street, Dublin, Ireland</li>
                </ul>

                <h3>11.3 Supervisory Authority</h3>
                <p>EU residents have the right to lodge a complaint with their local supervisory authority. You can find your authority at <a href="https://edpb.europa.eu/about-edpb/board/members_en" target="_blank">edpb.europa.eu</a>.</p>

                <h3>11.4 Automated Decision Making</h3>
                <p>We do not engage in automated decision-making or profiling that significantly affects individuals.</p>
            </section>

            <section id="cookies-tracking">
                <h2>12. Cookies and Tracking Technologies</h2>

                <h3>12.1 Types of Cookies We Use</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                            <th>Third Party</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Essential</td>
                            <td>Core app functionality</td>
                            <td>Session</td>
                            <td>No</td>
                        </tr>
                        <tr>
                            <td>Analytics</td>
                            <td>Usage measurement</td>
                            <td>2 years</td>
                            <td>Google Analytics</td>
                        </tr>
                        <tr>
                            <td>Functional</td>
                            <td>User preferences</td>
                            <td>1 year</td>
                            <td>No</td>
                        </tr>
                        <tr>
                            <td>Tracking</td>
                            <td>Conversion measurement</td>
                            <td>30 days</td>
                            <td>Various platforms</td>
                        </tr>
                    </tbody>
                </table>

                <h3>12.2 Managing Cookies</h3>
                <p>You can control cookies through:</p>
                <ul>
                    <li><strong>Browser Settings:</strong> Most browsers allow cookie blocking</li>
                    <li><strong>Consent Management:</strong> Our cookie consent tool</li>
                    <li><strong>Opt-Out Links:</strong> Third-party opt-out mechanisms</li>
                    <li><strong>Privacy Settings:</strong> Account-level privacy controls</li>
                </ul>

                <h3>12.3 Do Not Track</h3>
                <p>We respect Do Not Track signals and browser privacy settings. When detected:</p>
                <ul>
                    <li>Only essential tracking is performed</li>
                    <li>Analytics cookies are disabled</li>
                    <li>Third-party tracking is limited</li>
                    <li>Server-side tracking may still occur for service delivery</li>
                </ul>
            </section>

            <section id="third-party-services">
                <h2>13. Third-Party Services</h2>

                <h3>13.1 Service Provider Categories</h3>
                <p>We work with various third-party services, each with specific data access:</p>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Provider Examples</th>
                            <th>Data Access</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cloud Infrastructure</td>
                            <td>AWS, Google Cloud</td>
                            <td>All data (encrypted)</td>
                            <td>Service hosting</td>
                        </tr>
                        <tr>
                            <td>Analytics</td>
                            <td>Google Analytics</td>
                            <td>Anonymized usage data</td>
                            <td>Service improvement</td>
                        </tr>
                        <tr>
                            <td>Communication</td>
                            <td>SendGrid, Intercom</td>
                            <td>Contact information</td>
                            <td>User communication</td>
                        </tr>
                        <tr>
                            <td>Payment</td>
                            <td>Stripe</td>
                            <td>Billing information</td>
                            <td>Payment processing</td>
                        </tr>
                        <tr>
                            <td>Security</td>
                            <td>Cloudflare</td>
                            <td>IP addresses, requests</td>
                            <td>DDoS protection</td>
                        </tr>
                    </tbody>
                </table>

                <h3>13.2 Vendor Management</h3>
                <p>All our vendors must:</p>
                <ul>
                    <li>Sign data processing agreements (DPAs)</li>
                    <li>Implement appropriate security measures</li>
                    <li>Comply with applicable privacy laws</li>
                    <li>Undergo regular security assessments</li>
                    <li>Report data breaches promptly</li>
                </ul>

                <h3>13.3 Advertising Platforms</h3>
                <p>When you configure pixels for advertising platforms, data is shared according to:</p>
                <ul>
                    <li>Your explicit configuration in our dashboard</li>
                    <li>The platform's terms of service and privacy policy</li>
                    <li>Applicable data processing agreements</li>
                    <li>Your consent settings and preferences</li>
                </ul>
            </section>

            <section id="business-transfers">
                <h2>14. Business Transfers</h2>

                <h3>14.1 Merger or Acquisition</h3>
                <p>If Algoboost is involved in a merger, acquisition, or sale of assets:</p>
                <ul>
                    <li>We will notify users before personal data is transferred</li>
                    <li>The new entity must honor this privacy policy</li>
                    <li>Users will have the option to delete their data</li>
                    <li>Additional consent may be required for new processing purposes</li>
                </ul>

                <h3>14.2 Bankruptcy or Dissolution</h3>
                <p>If Algoboost ceases operations:</p>
                <ul>
                    <li>Customer data will be securely deleted within 30 days</li>
                    <li>Users will be notified via email and dashboard notices</li>
                    <li>Data export tools will remain available during the notice period</li>
                    <li>No customer data will be sold as part of bankruptcy proceedings</li>
                </ul>
            </section>

            <section id="updates">
                <h2>15. Policy Updates</h2>

                <h3>15.1 How We Update This Policy</h3>
                <p>We may update this privacy policy to reflect:</p>
                <ul>
                    <li>Changes in our services or business practices</li>
                    <li>New legal or regulatory requirements</li>
                    <li>Industry best practices and security improvements</li>
                    <li>User feedback and suggestions</li>
                </ul>

                <h3>15.2 Notification Process</h3>
                <p>When we make material changes:</p>
                <ul>
                    <li><strong>30 days advance notice</strong> via email to registered users</li>
                    <li><strong>Dashboard notification</strong> prominently displayed</li>
                    <li><strong>Website banner</strong> alerting visitors to changes</li>
                    <li><strong>Version history</strong> showing what changed</li>
                </ul>

                <h3>15.3 Your Options</h3>
                <p>If you disagree with policy changes:</p>
                <ul>
                    <li>You can delete your account before changes take effect</li>
                    <li>You can export your data during the notice period</li>
                    <li>Continued use constitutes acceptance of the new policy</li>
                    <li>You can contact us with concerns or questions</li>
                </ul>

                <div class="alert alert-info">
                    <strong>Version History:</strong> Previous versions of this policy are available upon request for transparency and comparison purposes.
                </div>
            </section>

            <section id="contact">
                <h2>16. Contact Information</h2>

                <div class="contact-info">
                    <h3>Privacy Questions and Requests</h3>
                    <p><strong>Primary Contact:</strong></p>
                    <ul>
                        <li><strong>Email:</strong> <a href="mailto:privacy@algoboost.io">privacy@algoboost.io</a></li>
                        <li><strong>Response Time:</strong> Within 48 hours for privacy requests</li>
                    </ul>

                    <p><strong>Data Protection Officer (EU):</strong></p>
                    <ul>
                        <li><strong>Email:</strong> <a href="mailto:dpo@algoboost.io">dpo@algoboost.io</a></li>
                        <li><strong>Address:</strong> Green Lunar EU, 123 GDPR Street, Dublin, Ireland</li>
                    </ul>

                    <p><strong>General Support:</strong></p>
                    <ul>
                        <li><strong>Email:</strong> <a href="mailto:support@algoboost.io">support@algoboost.io</a></li>
                        <li><strong>Chat:</strong> Available in your dashboard</li>
                        <li><strong>Phone:</strong> +1 (555) 123-4567 (Enterprise customers)</li>
                    </ul>

                    <p><strong>Mailing Address:</strong></p>
                    <ul>
                        <li>Algoboost Privacy Team</li>
                        <li>123 Tech Boulevard, Suite 100</li>
                        <li>San Francisco, CA 94105</li>
                        <li>United States</li>
                    </ul>
                </div>

                <h3>16.1 Response Times</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request Type</th>
                            <th>Response Time</th>
                            <th>Contact Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Privacy Rights Request</td>
                            <td>30 days</td>
                            <td>privacy@algoboost.io</td>
                        </tr>
                        <tr>
                            <td>Data Breach Report</td>
                            <td>72 hours</td>
                            <td>security@algoboost.io</td>
                        </tr>
                        <tr>
                            <td>General Privacy Question</td>
                            <td>48 hours</td>
                            <td>privacy@algoboost.io</td>
                        </tr>
                        <tr>
                            <td>GDPR Complaint</td>
                            <td>30 days</td>
                            <td>dpo@algoboost.io</td>
                        </tr>
                    </tbody>
                </table>

                <h3>16.2 Languages</h3>
                <p>This privacy policy and our privacy support are available in:</p>
                <ul>
                    <li>English (primary)</li>
                    <li>Spanish</li>
                    <li>French</li>
                    <li>German</li>
                    <li>Portuguese (Brazilian)</li>
                </ul>
            </section>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center; color: var(--text-light);">
                <p><strong>This Privacy Policy is effective as of January 15, 2025.</strong></p>
                <p>© 2025 Algoboost. A Green Lunar Company. All rights reserved.</p>
                <p><a href="<?php echo home_url('/terms'); ?>">Terms of Service</a> • <a href="<?php echo home_url('/gdpr'); ?>">GDPR Compliance</a> • <a href="<?php echo home_url('/security'); ?>">Security</a></p>
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
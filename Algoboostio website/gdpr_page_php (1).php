<?php /* Template Name: GDPR Compliance */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPR Compliance - Algoboost</title>
    <meta name="description" content="Algoboost GDPR compliance information - How we protect EU user data and ensure compliance with European data protection regulations.">
    
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

        .compliance-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .main {
            background: white;
            margin: 2rem 0;
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .eu-flag {
            font-size: 1.5rem;
            margin-right: 0.5rem;
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

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
            color: #047857;
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

        .principles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .principle-card {
            background: var(--gray-100);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .principle-card h4 {
            margin: 0 0 0.75rem 0;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .principle-card p {
            margin: 0;
            font-size: 0.875rem;
        }

        .legal-basis-list {
            background: var(--gray-100);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin: 1rem 0;
        }

        .legal-basis-list h4 {
            margin-top: 0;
            color: var(--primary);
        }

        .dpo-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin: 2rem 0;
        }

        .dpo-card h3 {
            color: white;
            margin-top: 0;
        }

        .dpo-card a {
            color: rgba(255, 255, 255, 0.9);
        }

        .dpo-card a:hover {
            color: white;
        }

        .rights-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .rights-table th,
        .rights-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .rights-table th {
            background: var(--gray-100);
            font-weight: 600;
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
            <h1 class="page-title">GDPR Compliance</h1>
            <p class="page-subtitle">European Data Protection & Privacy Compliance</p>
            <div class="compliance-badge">
                <span class="eu-flag">🇪🇺</span>
                GDPR Compliant
            </div>
        </div>
    </div>

    <div class="container">
        <div class="main">
            <section>
                <h2>Our Commitment to GDPR Compliance</h2>
                <p>Algoboost is fully committed to compliance with the European Union's General Data Protection Regulation (GDPR). We have implemented comprehensive policies, procedures, and technical safeguards to ensure that personal data of EU residents is processed lawfully, fairly, and transparently.</p>

                <div class="highlight-box">
                    <h3>GDPR Compliance Overview</h3>
                    <ul>
                        <li>Full compliance with all GDPR requirements since May 25, 2018</li>
                        <li>Data Protection Officer (DPO) appointed and available for inquiries</li>
                        <li>Privacy by Design principles implemented throughout our systems</li>
                        <li>Regular compliance audits and assessments by third-party experts</li>
                        <li>EU data residency options available for Enterprise customers</li>
                    </ul>
                </div>
            </section>

            <section>
                <h2>GDPR Principles We Follow</h2>
                <p>Our data processing activities are guided by the seven key principles of GDPR:</p>

                <div class="principles-grid">
                    <div class="principle-card">
                        <h4>⚖️ Lawfulness, Fairness & Transparency</h4>
                        <p>We process data lawfully with clear legal basis and provide transparent information about our data practices.</p>
                    </div>
                    <div class="principle-card">
                        <h4>🎯 Purpose Limitation</h4>
                        <p>Data is collected for specific, explicit purposes and not processed for incompatible purposes.</p>
                    </div>
                    <div class="principle-card">
                        <h4>📉 Data Minimisation</h4>
                        <p>We collect only the data necessary for our stated purposes and nothing more.</p>
                    </div>
                    <div class="principle-card">
                        <h4>✅ Accuracy</h4>
                        <p>We maintain accurate data and provide mechanisms for correction of inaccurate information.</p>
                    </div>
                    <div class="principle-card">
                        <h4>⏰ Storage Limitation</h4>
                        <p>Data is retained only as long as necessary and automatically deleted per our retention schedules.</p>
                    </div>
                    <div class="principle-card">
                        <h4>🔒 Integrity & Confidentiality</h4>
                        <p>Robust security measures protect data against unauthorized processing, loss, or damage.</p>
                    </div>
                </div>
            </section>

            <section>
                <h2>Legal Basis for Processing</h2>
                <p>Under GDPR, we must have a valid legal basis for processing personal data. We rely on the following legal grounds:</p>

                <div class="legal-basis-list">
                    <h4>Contract Performance (Article 6(1)(b))</h4>
                    <ul>
                        <li>Providing the Algoboost conversion tracking service</li>
                        <li>Processing payments and managing your subscription</li>
                        <li>Delivering customer support and technical assistance</li>
                        <li>Sending service-related communications and updates</li>
                    </ul>
                </div>

                <div class="legal-basis-list">
                    <h4>Legitimate Interest (Article 6(1)(f))</h4>
                    <ul>
                        <li>Improving service quality and performance optimization</li>
                        <li>Preventing fraud, abuse, and security threats</li>
                        <li>Conducting business analytics for operational improvements</li>
                        <li>Direct marketing to existing customers (with easy opt-out)</li>
                    </ul>
                </div>

                <div class="legal-basis-list">
                    <h4>Consent (Article 6(1)(a))</h4>
                    <ul>
                        <li>Marketing communications to prospects</li>
                        <li>Optional features like beta testing programs</li>
                        <li>Certain types of analytics and tracking</li>
                        <li>Sharing data with third-party platforms you configure</li>
                    </ul>
                </div>

                <div class="legal-basis-list">
                    <h4>Legal Obligation (Article 6(1)(c))</h4>
                    <ul>
                        <li>Tax and accounting requirements</li>
                        <li>Compliance with court orders or legal processes</li>
                        <li>Regulatory reporting obligations</li>
                        <li>Anti-money laundering and fraud prevention</li>
                    </ul>
                </div>
            </section>

            <section>
                <h2>Your GDPR Rights</h2>
                <p>As an EU resident, you have comprehensive rights regarding your personal data under GDPR:</p>

                <table class="rights-table">
                    <thead>
                        <tr>
                            <th>Right</th>
                            <th>What It Means</th>
                            <th>How to Exercise</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Right of Access</strong> (Art. 15)</td>
                            <td>Obtain confirmation of processing and receive a copy of your personal data</td>
                            <td>Account settings or email request</td>
                        </tr>
                        <tr>
                            <td><strong>Right to Rectification</strong> (Art. 16)</td>
                            <td>Correct inaccurate or incomplete personal data</td>
                            <td>Update in account settings</td>
                        </tr>
                        <tr>
                            <td><strong>Right to Erasure</strong> (Art. 17)</td>
                            <td>Request deletion of your personal data ("right to be forgotten")</td>
                            <td>Contact DPO or support</td>
                        </tr>
                        <tr>
                            <td><strong>Right to Restrict Processing</strong> (Art. 18)</td>
                            <td>Limit how we process your data in certain circumstances</td>
                            <td>Email DPO with specific request</td>
                        </tr>
                        <tr>
                            <td><strong>Right to Data Portability</strong> (Art. 20)</td>
                            <td>Receive your data in machine-readable format or transfer to another service</td>
                            <td>Data export feature in settings</td>
                        </tr>
                        <tr>
                            <td><strong>Right to Object</strong> (Art. 21)</td>
                            <td>Object to processing based on legitimate interest or direct marketing</td>
                            <td>Opt-out links or contact DPO</td>
                        </tr>
                        <tr>
                            <td><strong>Rights Related to Automated Decision Making</strong> (Art. 22)</td>
                            <td>Not be subject to purely automated decisions with legal effects</td>
                            <td>Contact DPO for review</td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-info">
                    <strong>Response Time:</strong> We will respond to all GDPR requests within one month (30 days) of receipt, as required by law. Complex requests may take up to three months with proper notification.
                </div>
            </section>

            <section>
                <div class="dpo-card">
                    <h3>Data Protection Officer (DPO)</h3>
                    <p>Our appointed Data Protection Officer is available to assist with all GDPR-related matters:</p>
                    
                    <div style="margin-top: 1.5rem;">
                        <strong>Contact Information:</strong><br>
                        Email: <a href="mailto:dpo@algoboost.io">dpo@algoboost.io</a><br>
                        Phone: +353 (0)1 XXX XXXX<br>
                        Address: Algoboost Data Protection Officer<br>
                        [European Office Address]<br>
                        Dublin, Ireland
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <strong>When to Contact Our DPO:</strong>
                        <ul>
                            <li>Questions about GDPR compliance or data protection</li>
                            <li>Requests to exercise your data subject rights</li>
                            <li>Concerns about how your data is being processed</li>
                            <li>Privacy impact assessment requests</li>
                            <li>Data protection training inquiries</li>
                        </ul>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <strong>Response Time:</strong> Our DPO will respond to all inquiries within 5 business days and provide regular updates on complex matters.
                    </div>
                </div>
            </section>

            <section>
                <h2>Contact Us About GDPR</h2>
                <div class="contact-info">
                    <h3>GDPR-Related Inquiries</h3>
                    <p>For any questions about GDPR compliance or to exercise your rights:</p>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Data Protection Officer:</strong><br>
                        Email: <a href="mailto:dpo@algoboost.io">dpo@algoboost.io</a><br>
                        Subject Line: Include "GDPR Request" or "Data Subject Rights"
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <strong>Privacy Team:</strong><br>
                        Email: <a href="mailto:privacy@algoboost.io">privacy@algoboost.io</a><br>
                        For general privacy questions and policy clarifications
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <strong>Legal Department:</strong><br>
                        Email: <a href="mailto:legal@algoboost.io">legal@algoboost.io</a><br>
                        For legal compliance and regulatory matters
                    </div>

                    <h4>Required Information for Requests</h4>
                    <p>To process your GDPR request efficiently, please include:</p>
                    <ul>
                        <li>Full name and email address associated with your account</li>
                        <li>Specific right you wish to exercise (access, rectification, erasure, etc.)</li>
                        <li>Detailed description of your request</li>
                        <li>Proof of identity (for security purposes)</li>
                        <li>Preferred response method (email, postal mail, secure portal)</li>
                    </ul>
                </div>
            </section>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center; color: var(--text-light);">
                <p><strong>This GDPR compliance page is current as of August 27, 2025.</strong></p>
                <p>🇪🇺 Fully compliant with EU General Data Protection Regulation (GDPR)</p>
                <p><a href="<?php echo home_url('/privacy'); ?>">Privacy Policy</a> • <a href="<?php echo home_url('/terms'); ?>">Terms of Service</a> • <a href="<?php echo home_url('/security'); ?>">Security</a></p>
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
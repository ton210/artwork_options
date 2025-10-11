<?php /* Template Name: Contact Page */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Algoboost | Support & Sales</title>
    <meta name="description" content="Get support, ask questions, or connect with our sales team. Multiple ways to reach Algoboost for help with your Shopify conversion tracking.">
    <meta name="keywords" content="algoboost support, shopify tracking help, conversion tracking support, contact sales team">

    <!-- Open Graph -->
    <meta property="og:title" content="Contact Us - Algoboost">
    <meta property="og:description" content="Get help with Algoboost. Support, sales, and partnership inquiries welcome.">
    <meta property="og:image" content="<?php echo home_url('/images/contact-og.png'); ?>">
    <meta property="og:url" content="<?php echo home_url('/contact'); ?>">

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
            --bg-light: #f9fafb;
            --white: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
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

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--gray-200);
            z-index: 1000;
            transition: var(--transition);
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
            padding: 5rem 2rem 3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        /* Contact Options */
        .contact-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }

        .contact-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        .contact-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .contact-description {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .contact-info {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .response-time {
            font-size: 0.9rem;
            color: var(--text-light);
            font-style: italic;
        }

        /* Contact Form */
        .contact-form-section {
            background: var(--bg-light);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 4rem;
        }

        .form-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .form-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .contact-form {
            max-width: 700px;
            margin: 0 auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
            background: white;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .form-note {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        /* Success Message */
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: none;
        }

        /* Quick Help */
        .quick-help {
            margin-bottom: 4rem;
        }

        .quick-help-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .quick-help-subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .help-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .help-question {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .help-answer {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .help-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .help-link:hover {
            text-decoration: underline;
        }

        /* Office Info */
        .office-info {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .office-title {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .office-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .office-detail {
            text-align: center;
        }

        .office-detail h4 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .office-detail p {
            color: var(--text-light);
        }

        .availability {
            background: var(--bg-light);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .availability h4 {
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .availability ul {
            list-style: none;
            color: var(--text-light);
        }

        .availability li {
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

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .main-content {
                padding: 2rem 1rem;
            }

            .contact-options {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .help-grid {
                grid-template-columns: 1fr;
            }

            .office-details {
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
                <li><a href="<?php echo home_url('/contact'); ?>" class="active">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Get in Touch</h1>
            <p class="hero-subtitle">We're here to help you master Shopify conversion tracking with Algoboost</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Contact Options -->
        <section class="contact-options">
            <div class="contact-card">
                <span class="contact-icon">📧</span>
                <h3 class="contact-title">Technical Support</h3>
                <p class="contact-description">
                    Get help with tracking setup, server-side configuration, pixel issues, or integration problems.
                </p>
                <div class="contact-info">support@algoboost.io</div>
                <div class="response-time">Response within 24 hours</div>
                <a href="mailto:support@algoboost.io" class="btn btn-outline" style="margin-top: 1rem;">Get Support</a>
            </div>

            <div class="contact-card">
                <span class="contact-icon">💼</span>
                <h3 class="contact-title">Sales Team</h3>
                <p class="contact-description">
                    Interested in Enterprise plans, custom tracking solutions, or agency partnerships?
                </p>
                <div class="contact-info">sales@algoboost.io</div>
                <div class="response-time">Response within 4 hours</div>
                <a href="mailto:sales@algoboost.io" class="btn btn-primary" style="margin-top: 1rem;">Contact Sales</a>
            </div>

            <div class="contact-card">
                <span class="contact-icon">💬</span>
                <h3 class="contact-title">Live Chat</h3>
                <p class="contact-description">
                    Get instant help with quick questions about tracking setup or account issues.
                </p>
                <div class="contact-info">Available Mon-Fri</div>
                <div class="response-time">9 AM - 6 PM EST</div>
                <button class="btn btn-outline" style="margin-top: 1rem;" onclick="openLiveChat()">Start Chat</button>
            </div>

            <div class="contact-card">
                <span class="contact-icon">📖</span>
                <h3 class="contact-title">Documentation</h3>
                <p class="contact-description">
                    Browse our comprehensive guides on server-side tracking, platform setup, and troubleshooting.
                </p>
                <div class="contact-info">Self-service help</div>
                <div class="response-time">Available 24/7</div>
                <a href="<?php echo home_url('/documentation'); ?>" class="btn btn-outline" style="margin-top: 1rem;">View Docs</a>
            </div>
        </section>

        <!-- Contact Form -->
        <section class="contact-form-section">
            <h2 class="form-title">Send us a Message</h2>
            <p class="form-subtitle">Have a specific question about tracking? Our experts are here to help.</p>

            <form class="contact-form" id="contactForm" onsubmit="handleContactForm(event)">
                <div class="success-message" id="successMessage">
                    <strong>Message sent!</strong> We'll get back to you within 24 hours.
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="firstName">First Name *</label>
                        <input type="text" id="firstName" name="firstName" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lastName">Last Name *</label>
                        <input type="text" id="lastName" name="lastName" class="form-input" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="store">Shopify Store URL</label>
                    <input type="text" id="store" name="store" class="form-input" placeholder="your-store.myshopify.com">
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="subject">Subject *</label>
                    <select id="subject" name="subject" class="form-select" required>
                        <option value="">Select a topic</option>
                        <option value="tracking-setup">Tracking Setup Help</option>
                        <option value="server-side">Server-Side Tracking</option>
                        <option value="pixel-issues">Pixel Issues</option>
                        <option value="data-discrepancy">Data Discrepancies</option>
                        <option value="platform-integration">Platform Integration</option>
                        <option value="billing">Billing Question</option>
                        <option value="sales">Sales Inquiry</option>
                        <option value="partnership">Partnership Opportunity</option>
                        <option value="feature">Feature Request</option>
                        <option value="bug">Bug Report</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="message">Message *</label>
                    <textarea id="message" name="message" class="form-textarea" required 
                              placeholder="Please describe your question or issue in detail..."></textarea>
                </div>

                <div class="form-footer">
                    <p class="form-note">
                        * Required fields. We respect your privacy and will never share your information.
                    </p>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </section>

        <!-- Quick Help -->
        <section class="quick-help">
            <h2 class="quick-help-title">Quick Help</h2>
            <p class="quick-help-subtitle">Common questions and immediate solutions</p>

            <div class="help-grid">
                <div class="help-item">
                    <h3 class="help-question">How do I set up Facebook Conversions API?</h3>
                    <p class="help-answer">
                        After installing Algoboost, add your Facebook pixel ID in the dashboard and enable server-side tracking. Our setup wizard guides you through the process.
                    </p>
                    <a href="<?php echo home_url('/documentation#facebook-meta'); ?>" class="help-link">Facebook Setup Guide →</a>
                </div>

                <div class="help-item">
                    <h3 class="help-question">Why is my tracking data inconsistent?</h3>
                    <p class="help-answer">
                        Data discrepancies are normal due to iOS restrictions and attribution windows. Enable server-side tracking for more accurate data.
                    </p>
                    <a href="<?php echo home_url('/documentation#data-discrepancies'); ?>" class="help-link">Data Accuracy Guide →</a>
                </div>

                <div class="help-item">
                    <h3 class="help-question">How do I test my pixel setup?</h3>
                    <p class="help-answer">
                        Use our built-in testing tools or platform-specific pixel helpers to verify events are firing correctly on your store.
                    </p>
                    <a href="<?php echo home_url('/documentation#testing-tracking'); ?>" class="help-link">Testing Guide →</a>
                </div>

                <div class="help-item">
                    <h3 class="help-question">Can I track multiple ad accounts?</h3>
                    <p class="help-answer">
                        Yes! Add multiple pixels for each platform. Growth+ plans support unlimited pixels and advanced attribution modeling.
                    </p>
                    <a href="<?php echo home_url('/pricing'); ?>" class="help-link">View Plans →</a>
                </div>

                <div class="help-item">
                    <h3 class="help-question">Need enhanced conversions?</h3>
                    <p class="help-answer">
                        Enhanced conversions improve match rates by 30-50%. Available on Growth+ plans with automatic customer data hashing.
                    </p>
                    <a href="<?php echo home_url('/documentation#enhanced-conversions'); ?>" class="help-link">Enhanced Conversions →</a>
                </div>

                <div class="help-item">
                    <h3 class="help-question">Looking for custom events?</h3>
                    <p class="help-answer">
                        Track custom events like email signups, wishlist adds, or specific product views. Perfect for advanced attribution.
                    </p>
                    <a href="<?php echo home_url('/documentation#custom-events'); ?>" class="help-link">Custom Events Guide →</a>
                </div>
            </div>
        </section>

        <!-- Office Information -->
        <section class="office-info">
            <h2 class="office-title">Our Team</h2>
            
            <div class="office-details">
                <div class="office-detail">
                    <h4>🌍 Global Support</h4>
                    <p>We're a distributed team providing 24/7 support coverage across all time zones.</p>
                </div>

                <div class="office-detail">
                    <h4>🚀 Response Time</h4>
                    <p>Technical support within 24 hours, sales inquiries within 4 hours.</p>
                </div>

                <div class="office-detail">
                    <h4>💡 Expertise</h4>
                    <p>Our team includes Shopify experts, conversion tracking specialists, and platform integration engineers.</p>
                </div>
            </div>

            <div class="availability">
                <h4>Support Hours</h4>
                <ul>
                    <li><strong>Email Support:</strong> 24/7 (responses within 24 hours)</li>
                    <li><strong>Live Chat:</strong> Monday - Friday, 9 AM - 6 PM EST</li>
                    <li><strong>Priority Support:</strong> Available for Growth+ plan customers</li>
                    <li><strong>Enterprise Support:</strong> Dedicated support for Enterprise customers</li>
                </ul>
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
        // Contact form handling
        function handleContactForm(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Simulate form submission
            console.log('Contact form submitted:', data);
            
            // Show success message
            document.getElementById('successMessage').style.display = 'block';
            
            // Reset form
            form.reset();
            
            // Scroll to success message
            document.getElementById('successMessage').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
            
            // Hide success message after 5 seconds
            setTimeout(() => {
                document.getElementById('successMessage').style.display = 'none';
            }, 5000);
        }

        // Live chat functionality (placeholder)
        function openLiveChat() {
            // In a real implementation, this would open your live chat widget
            alert('Live chat will open here! 💬\n\nFor now, please email us at support@algoboost.io');
            
            // You could integrate with Intercom, Crisp, or other chat services
            // Example: window.Intercom('show');
        }

        // Auto-populate store URL if coming from dashboard
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const shop = urlParams.get('shop');
            
            if (shop) {
                document.getElementById('store').value = shop;
            }
        });

        // Form validation enhancements
        document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = var(--success);
                } else {
                    this.style.borderColor = '#e53e3e';
                }
            });
            
            input.addEventListener('input', function() {
                this.style.borderColor = '#e2e8f0';
            });
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{3})/, '($1) $2');
            }
            e.target.value = value;
        });

        // Subject-based form helpers
        document.getElementById('subject').addEventListener('change', function() {
            const messageField = document.getElementById('message');
            const subject = this.value;
            
            let placeholder = 'Please describe your question or issue in detail...';
            
            switch(subject) {
                case 'tracking-setup':
                    placeholder = 'Please describe what you\'re trying to set up:\n• Which platforms (Facebook, Google, TikTok, etc.)\n• What tracking issues you\'re experiencing\n• Your current setup\n• Your store URL';
                    break;
                case 'server-side':
                    placeholder = 'Tell us about your server-side tracking needs:\n• Which platforms you want to connect\n• Current tracking accuracy issues\n• Volume of conversions per month\n• Technical requirements';
                    break;
                case 'pixel-issues':
                    placeholder = 'Help us troubleshoot your pixel:\n• Which platform (Facebook, Google, etc.)\n• What\'s not working correctly\n• Error messages you\'re seeing\n• Browser and device information';
                    break;
                case 'data-discrepancy':
                    placeholder = 'Describe the data discrepancy:\n• Which platforms are showing different numbers\n• Time period you\'re comparing\n• Specific metrics that don\'t match\n• Attribution settings you\'re using';
                    break;
                case 'billing':
                    placeholder = 'Please describe your billing question. Include your store URL so we can look up your account.';
                    break;
                case 'sales':
                    placeholder = 'Tell us about your business needs:\n• Monthly conversion volume\n• Platforms you advertise on\n• Specific features you\'re interested in\n• Timeline for implementation';
                    break;
                case 'feature':
                    placeholder = 'Describe the feature you\'d like to see:\n• What tracking problem would it solve?\n• How would you use it?\n• Any specific platform requirements?';
                    break;
                case 'bug':
                    placeholder = 'Help us reproduce the bug:\n• Steps to reproduce\n• Expected vs actual behavior\n• Browser and device information\n• Screenshots if applicable';
                    break;
            }
            
            messageField.placeholder = placeholder;
        });

        // Analytics tracking for contact methods
        document.querySelectorAll('.contact-card .btn, .help-link').forEach(link => {
            link.addEventListener('click', function() {
                const contactMethod = this.closest('.contact-card')?.querySelector('.contact-title')?.textContent ||
                                    this.textContent;
                
                console.log('Contact method used:', contactMethod);
                
                // Custom event for analytics
                window.dispatchEvent(new CustomEvent('contactMethodUsed', {
                    detail: { method: contactMethod }
                }));
            });
        });
    </script>
</body>
</html>
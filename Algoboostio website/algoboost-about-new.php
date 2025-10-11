<?php /* Template Name: Algoboost About */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - The Algoboost Story | Algoboost</title>
    <meta name="description" content="Learn about Algoboost's mission to help Shopify merchants track conversions accurately in the post-iOS 14.5 world. Meet our team, values, and story.">
    <meta name="keywords" content="algoboost team, shopify conversion tracking company, green lunar company, about algoboost, conversion tracking experts">
    
    <link rel="icon" type="image/png" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎯</text></svg>">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #10b981;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --text-dark: #1a202c;
            --text-light: #718096;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --radius: 0.75rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--bg-light);
        }

        /* Navigation */
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

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 0;
            margin-top: 80px;
        }

        .breadcrumb-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .breadcrumb a {
            color: var(--text-light);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Hero Section */
        .hero {
            padding: 5rem 2rem;
            background: white;
            text-align: center;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Story Section */
        .story-section {
            padding: 5rem 2rem;
            background: var(--bg-light);
        }

        .story-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 4rem;
        }

        .story-text h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .story-text p {
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .story-visual {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            text-align: center;
        }

        .story-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }

        /* Mission & Vision */
        .mission-section {
            padding: 5rem 2rem;
            background: white;
        }

        .mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .mission-card {
            background: var(--bg-light);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            border: 1px solid var(--gray-200);
        }

        .mission-card-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            color: white;
        }

        .mission-card:nth-child(1) .mission-card-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .mission-card:nth-child(2) .mission-card-icon {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .mission-card:nth-child(3) .mission-card-icon {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .mission-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .mission-card p {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Values Section */
        .values-section {
            padding: 5rem 2rem;
            background: var(--bg-light);
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 3rem;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .value-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .value-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .value-emoji {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .value-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .value-description {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Team Section */
        .team-section {
            padding: 5rem 2rem;
            background: white;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .team-member {
            background: var(--bg-light);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .team-member:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .member-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }

        .member-name {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .member-role {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .member-bio {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 2rem;
            background: var(--bg-light);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-weight: 500;
        }

        /* CTA Section */
        .cta-section {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
        }

        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .btn-white {
            background: white;
            color: var(--primary);
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary);
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

        .footer-bottom a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-bottom a:hover {
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero-title {
                font-size: 2rem;
            }

            .story-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .mission-grid,
            .values-grid,
            .team-grid {
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
                <li><a href="<?php echo home_url('/about'); ?>" class="active">About</a></li>
                <li><a href="<?php echo home_url('/documentation'); ?>">Docs</a></li>
                <li><a href="<?php echo home_url('/blog'); ?>">Blog</a></li>
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary">Install App</a>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="breadcrumb-container">
            <a href="<?php echo home_url(); ?>">Home</a> / About Us
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Building the Future of Conversion Tracking</h1>
            <p class="hero-subtitle">
                We're passionate about helping Shopify merchants succeed with accurate, reliable conversion tracking that works in the modern advertising landscape.
            </p>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story-section">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>Our Story</h2>
                    <p>
                        Algoboost was born from the frustration experienced by countless Shopify merchants after iOS 14.5's App Tracking Transparency update. Overnight, advertising performance dropped, attribution became unreliable, and ROAS calculations became meaningless.
                    </p>
                    <p>
                        As part of the Green Lunar family of products, we recognized that merchants needed more than just another tracking tool – they needed a comprehensive solution that could adapt to the changing privacy landscape while maintaining the accuracy required for profitable advertising.
                    </p>
                    <p>
                        Our team of experienced developers, data scientists, and ecommerce experts came together with one goal: create the most advanced, reliable, and user-friendly conversion tracking platform for Shopify merchants.
                    </p>
                </div>
                <div class="story-visual">
                    <div class="story-icon">📊</div>
                    <h3>Founded in 2023</h3>
                    <p style="color: var(--text-light); margin-top: 0.5rem;">
                        Built specifically to address post-iOS 14.5 tracking challenges
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-section">
        <div class="container">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-card-icon">🎯</div>
                    <h3>Our Mission</h3>
                    <p>To empower Shopify merchants with accurate, reliable conversion tracking that enables data-driven decision making and profitable scaling, regardless of platform restrictions or privacy updates.</p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-card-icon">🚀</div>
                    <h3>Our Vision</h3>
                    <p>A world where every ecommerce business can confidently measure and optimize their marketing performance with complete transparency and accuracy across all advertising channels.</p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-card-icon">⚡</div>
                    <h3>Our Approach</h3>
                    <p>We combine cutting-edge server-side tracking technology with intuitive user interfaces to deliver enterprise-grade solutions that are accessible to businesses of all sizes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values-section">
        <div class="container">
            <h2 class="section-title">Our Values</h2>
            <p class="section-subtitle">The principles that guide everything we do</p>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-emoji">🔒</div>
                    <h3 class="value-title">Privacy First</h3>
                    <p class="value-description">We believe in respecting user privacy while providing accurate tracking. Our solutions are designed to be GDPR compliant and respect user consent preferences.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-emoji">📊</div>
                    <h3 class="value-title">Data Accuracy</h3>
                    <p class="value-description">Accurate data is the foundation of good business decisions. We're obsessed with providing the most precise tracking possible in today's complex environment.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-emoji">🤝</div>
                    <h3 class="value-title">Customer Success</h3>
                    <p class="value-description">Your success is our success. We're committed to providing exceptional support and continuously improving based on your feedback and needs.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-emoji">⚡</div>
                    <h3 class="value-title">Innovation</h3>
                    <p class="value-description">The digital landscape is constantly evolving. We stay ahead of the curve with cutting-edge technology and proactive solution development.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-emoji">🌍</div>
                    <h3 class="value-title">Transparency</h3>
                    <p class="value-description">We believe in honest communication, clear pricing, and transparent processes. You'll always know exactly what you're getting with Algoboost.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-emoji">🎓</div>
                    <h3 class="value-title">Education</h3>
                    <p class="value-description">We empower merchants with knowledge through comprehensive documentation, tutorials, and ongoing education about conversion tracking best practices.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Meet Our Team</h2>
            <p class="section-subtitle">The experts behind Algoboost's advanced tracking technology</p>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">👨‍💻</div>
                    <h3 class="member-name">Alex Chen</h3>
                    <p class="member-role">Founder & CEO</p>
                    <p class="member-bio">Former Google Ads specialist with 8+ years in conversion tracking. Passionate about solving attribution challenges for ecommerce businesses.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-avatar">👩‍🔬</div>
                    <h3 class="member-name">Sarah Rodriguez</h3>
                    <p class="member-role">Head of Engineering</p>
                    <p class="member-bio">Previously at Facebook, specialized in server-side tracking infrastructure. Expert in privacy-compliant data processing and analytics.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-avatar">👨‍📊</div>
                    <h3 class="member-name">Mike Thompson</h3>
                    <p class="member-role">Head of Product</p>
                    <p class="member-bio">E-commerce veteran with deep understanding of merchant needs. Formerly product manager at Shopify Plus, focused on enterprise solutions.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-avatar">👩‍💼</div>
                    <h3 class="member-name">Emma Wilson</h3>
                    <p class="member-role">Customer Success Lead</p>
                    <p class="member-bio">Dedicated to ensuring every merchant succeeds with Algoboost. Background in technical support and merchant onboarding at leading SaaS companies.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">5K+</div>
                    <div class="stat-label">Active Merchants</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">18+</div>
                    <div class="stat-label">Platform Integrations</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">Uptime Guarantee</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Join Our Growing Community?</h2>
            <p class="cta-subtitle">Thousands of merchants trust Algoboost for their conversion tracking needs</p>
            <div>
                <a href="https://apps.shopify.com/algoboost" class="btn btn-white">Start Free Trial</a>
                <a href="<?php echo home_url('/contact'); ?>" class="btn btn-outline">Get in Touch</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Product</h3>
                <a href="<?php echo home_url('/features'); ?>">Features</a>
                <a href="<?php echo home_url('/pricing'); ?>">Pricing</a>
                <a href="<?php echo home_url('/documentation'); ?>">Documentation</a>
                <a href="<?php echo home_url('/security'); ?>">Security</a>
                <a href="<?php echo home_url('/sitemap'); ?>">Sitemap</a>
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
        // Animation on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        // Apply animations to cards
        document.querySelectorAll('.mission-card, .value-card, .team-member, .stat-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
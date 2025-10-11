<?php /* Template Name: Algoboost Pricing */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans - Advanced Shopify Conversion Tracking | Algoboost</title>
    <meta name="description" content="Choose the perfect Algoboost plan for your Shopify store. From free to enterprise, get advanced conversion tracking, server-side tracking, and enhanced conversions.">
    <meta name="keywords" content="algoboost pricing, shopify conversion tracking pricing, server side tracking cost, enhanced conversions pricing, shopify pixel tracking plans">
    
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

        /* Hero Section */
        .hero {
            padding: 4rem 2rem;
            background: white;
            text-align: center;
        }

        .hero-container {
            max-width: 800px;
            margin: 0 auto;
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
            margin-bottom: 2rem;
        }

        /* Billing Toggle */
        .billing-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .billing-switch {
            background: var(--gray-200);
            border-radius: 50px;
            padding: 0.25rem;
            display: flex;
            position: relative;
        }

        .billing-option {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .billing-option.active {
            background: var(--primary);
            color: white;
        }

        .billing-save {
            background: var(--success);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Pricing Section */
        .pricing-section {
            padding: 3rem 2rem 5rem;
            background: var(--bg-light);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-card {
            background: white;
            border-radius: var(--radius);
            border: 2px solid var(--gray-200);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
        }

        .pricing-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-4px);
        }

        .pricing-card.popular {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: 'Most Popular';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: var(--primary);
            color: white;
            text-align: center;
            padding: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .pricing-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--gray-200);
        }

        .pricing-card.popular .pricing-header {
            padding-top: 3rem;
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .plan-description {
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .plan-price {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 0.25rem;
            margin-bottom: 1rem;
        }

        .price-currency {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
        }

        .price-amount {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
        }

        .price-period {
            font-size: 1rem;
            color: var(--text-light);
        }

        .price-annual {
            display: none;
        }

        .billing-annual .price-monthly {
            display: none;
        }

        .billing-annual .price-annual {
            display: flex;
        }

        .plan-cta {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: inline-block;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        .plan-cta.primary {
            background: var(--primary);
            color: white;
        }

        .plan-cta.primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .plan-cta.secondary {
            background: var(--gray-100);
            color: var(--text-dark);
            border: 2px solid var(--gray-300);
        }

        .plan-cta.secondary:hover {
            background: var(--gray-200);
        }

        .pricing-features {
            padding: 2rem;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 0.75rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .features-list li::before {
            content: "✓";
            color: var(--success);
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .feature-unavailable::before {
            content: "✕";
            color: var(--text-light);
            background: var(--gray-100);
        }

        .feature-unavailable {
            color: var(--text-light);
            text-decoration: line-through;
        }

        /* Feature Comparison */
        .comparison-section {
            padding: 5rem 2rem;
            background: white;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: var(--text-light);
            text-align: center;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .comparison-table {
            overflow-x: auto;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--text-dark);
        }

        .plan-column {
            text-align: center;
            min-width: 120px;
        }

        .check-mark {
            color: var(--success);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .x-mark {
            color: var(--text-light);
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* FAQ Section */
        .faq-section {
            padding: 5rem 2rem;
            background: var(--bg-light);
        }

        .faq-grid {
            display: grid;
            gap: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .faq-question {
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            transition: var(--transition);
        }

        .faq-question:hover {
            background: var(--gray-50);
        }

        .faq-toggle {
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .faq-answer {
            padding: 0 1.5rem 1.5rem;
            color: var(--text-light);
            line-height: 1.6;
            display: none;
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        .faq-item.active .faq-toggle {
            transform: rotate(45deg);
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

        .btn {
            padding: 0.875rem 2rem;
            border-radius: var(--radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            margin: 0 0.5rem;
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

        /* Trust Signals */
        .trust-section {
            padding: 3rem 2rem;
            background: white;
            text-align: center;
        }

        .trust-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .trust-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .trust-icon {
            width: 48px;
            height: 48px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .trust-text {
            font-weight: 600;
            color: var(--text-dark);
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

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .pricing-card.popular {
                transform: none;
            }

            .billing-toggle {
                flex-direction: column;
                gap: 1rem;
            }

            .comparison-table {
                font-size: 0.875rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out;
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
                <li><a href="<?php echo home_url('/pricing'); ?>" class="active">Pricing</a></li>
                <li><a href="<?php echo home_url('/about'); ?>">About</a></li>
                <li><a href="<?php echo home_url('/documentation'); ?>">Docs</a></li>
                <li><a href="<?php echo home_url('/blog'); ?>">Blog</a></li>
                <li><a href="<?php echo home_url('/contact'); ?>">Contact</a></li>
            </ul>
            
            <a href="https://apps.shopify.com/algoboost" class="btn btn-primary" style="background: var(--primary); color: white; padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; text-decoration: none;">Install App</a>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="breadcrumb-container">
            <a href="<?php echo home_url(); ?>">Home</a> / Pricing
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <h1 class="hero-title">Simple, Transparent Pricing</h1>
            <p class="hero-subtitle">
                Choose the perfect plan for your Shopify store. Start free, scale as you grow.
            </p>
            
            <!-- Billing Toggle -->
            <div class="billing-toggle">
                <span>Monthly</span>
                <div class="billing-switch">
                    <div class="billing-option active" data-billing="monthly">Monthly</div>
                    <div class="billing-option" data-billing="annual">Annual</div>
                </div>
                <span>Annual <span class="billing-save">Save 20%</span></span>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container">
            <div class="pricing-grid" id="pricingGrid">
                <!-- Free Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="plan-name">Free</h3>
                        <p class="plan-description">Perfect for testing and small stores</p>
                        <div class="plan-price price-monthly">
                            <span class="price-currency">$</span>
                            <span class="price-amount">0</span>
                            <span class="price-period">/month</span>
                        </div>
                        <div class="plan-price price-annual">
                            <span class="price-currency">$</span>
                            <span class="price-amount">0</span>
                            <span class="price-period">/month</span>
                        </div>
                        <a href="https://apps.shopify.com/algoboost" class="plan-cta secondary">Get Started Free</a>
                    </div>
                    <div class="pricing-features">
                        <ul class="features-list">
                            <li>100 script loads/month</li>
                            <li>1 pixel installation</li>
                            <li>Basic conversion tracking</li>
                            <li>Real-time dashboard</li>
                            <li>Email support</li>
                            <li class="feature-unavailable">Server-side tracking</li>
                            <li class="feature-unavailable">Enhanced conversions</li>
                            <li class="feature-unavailable">A/B testing</li>
                            <li class="feature-unavailable">Custom events</li>
                        </ul>
                    </div>
                </div>

                <!-- Starter Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="plan-name">Starter</h3>
                        <p class="plan-description">For growing businesses</p>
                        <div class="plan-price price-monthly">
                            <span class="price-currency">$</span>
                            <span class="price-amount">9.99</span>
                            <span class="price-period">/month</span>
                        </div>
                        <div class="plan-price price-annual">
                            <span class="price-currency">$</span>
                            <span class="price-amount">7.99</span>
                            <span class="price-period">/month</span>
                        </div>
                        <a href="https://apps.shopify.com/algoboost" class="plan-cta primary">Start 7-Day Trial</a>
                    </div>
                    <div class="pricing-features">
                        <ul class="features-list">
                            <li>1,000 script loads/month</li>
                            <li>Up to 3 pixels</li>
                            <li>Basic analytics dashboard</li>
                            <li>Enhanced conversions</li>
                            <li>3 custom events</li>
                            <li>Email support</li>
                            <li>CSV exports</li>
                            <li class="feature-unavailable">Server-side tracking</li>
                            <li class="feature-unavailable">A/B testing</li>
                        </ul>
                    </div>
                </div>

                <!-- Growth Plan - Popular -->
                <div class="pricing-card popular">
                    <div class="pricing-header">
                        <h3 class="plan-name">Growth</h3>
                        <p class="plan-description">For established stores</p>
                        <div class="plan-price price-monthly">
                            <span class="price-currency">$</span>
                            <span class="price-amount">29.99</span>
                            <span class="price-period">/month</span>
                        </div>
                        <div class="plan-price price-annual">
                            <span class="price-currency">$</span>
                            <span class="price-amount">23.99</span>
                            <span class="price-period">/month</span>
                        </div>
                        <a href="https://apps.shopify.com/algoboost" class="plan-cta primary">Start 7-Day Trial</a>
                    </div>
                    <div class="pricing-features">
                        <ul class="features-list">
                            <li>5,000 script loads/month</li>
                            <li>Up to 10 pixels</li>
                            <li>Server-side tracking</li>
                            <li>Enhanced conversions</li>
                            <li>10 custom events</li>
                            <li>A/B testing (3 tests)</li>
                            <li>Attribution models</li>
                            <li>Priority email support</li>
                            <li>API access</li>
                            <li>Conversion rules</li>
                        </ul>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="plan-name">Enterprise</h3>
                        <p class="plan-description">Unlimited everything</p>
                        <div class="plan-price price-monthly">
                            <span class="price-currency">$</span>
                            <span class="price-amount">99.99</span>
                            <span class="price-period">/month</span>
                        </div>
                        <div class="plan-price price-annual">
                            <span class="price-currency">$</span>
                            <span class="price-amount">79.99</span>
                            <span class="price-period">/month</span>
                        </div>
                        <a href="https://apps.shopify.com/algoboost" class="plan-cta primary">Start 14-Day Trial</a>
                    </div>
                    <div class="pricing-features">
                        <ul class="features-list">
                            <li>Unlimited script loads</li>
                            <li>Unlimited pixels</li>
                            <li>Server-side tracking</li>
                            <li>Enhanced conversions</li>
                            <li>Unlimited custom events</li>
                            <li>Unlimited A/B testing</li>
                            <li>All attribution models</li>
                            <li>Priority support</li>
                            <li>Custom reports</li>
                            <li>API access</li>
                            <li>White-label options</li>
                            <li>Dedicated account manager</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Signals -->
    <section class="trust-section">
        <div class="container">
            <h2 class="section-title">Why Merchants Choose Algoboost</h2>
            <div class="trust-grid">
                <div class="trust-item">
                    <div class="trust-icon">🔒</div>
                    <div class="trust-text">GDPR Compliant</div>
                </div>
                <div class="trust-item">
                    <div class="trust-icon">⚡</div>
                    <div class="trust-text">99.9% Uptime</div>
                </div>
                <div class="trust-item">
                    <div class="trust-icon">💳</div>
                    <div class="trust-text">Cancel Anytime</div>
                </div>
                <div class="trust-item">
                    <div class="trust-icon">🛡️</div>
                    <div class="trust-text">30-Day Guarantee</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Comparison -->
    <section class="comparison-section">
        <div class="container">
            <h2 class="section-title">Feature Comparison</h2>
            <p class="section-subtitle">See what's included in each plan</p>
            
            <div class="comparison-table">
                <table>
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th class="plan-column">Free</th>
                            <th class="plan-column">Starter</th>
                            <th class="plan-column">Growth</th>
                            <th class="plan-column">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Script Loads/Month</td>
                            <td class="plan-column">100</td>
                            <td class="plan-column">1,000</td>
                            <td class="plan-column">5,000</td>
                            <td class="plan-column">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Pixels</td>
                            <td class="plan-column">1</td>
                            <td class="plan-column">3</td>
                            <td class="plan-column">10</td>
                            <td class="plan-column">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Server-Side Tracking</td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                        </tr>
                        <tr>
                            <td>Enhanced Conversions</td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                        </tr>
                        <tr>
                            <td>A/B Testing</td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column">3 Tests</td>
                            <td class="plan-column">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Custom Events</td>
                            <td class="plan-column">0</td>
                            <td class="plan-column">3</td>
                            <td class="plan-column">10</td>
                            <td class="plan-column">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Priority Support</td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                        </tr>
                        <tr>
                            <td>API Access</td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                        </tr>
                        <tr>
                            <td>White-Label Options</td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="x-mark">✕</span></td>
                            <td class="plan-column"><span class="check-mark">✓</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-subtitle">Everything you need to know about Algoboost pricing</p>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Can I change plans anytime?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately, and you'll be charged/credited the prorated amount for the billing cycle.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>What happens if I exceed my plan limits?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        If you exceed your monthly script load limit, tracking will be temporarily paused until the next billing cycle or you can upgrade your plan. We'll notify you when you reach 80% of your limit.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Do you offer refunds?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Yes, we offer a 30-day money-back guarantee. If you're not satisfied with Algoboost, contact support within 30 days of your purchase for a full refund.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Is there a setup fee?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        No, there are no setup fees or hidden costs. You only pay the monthly subscription fee for your chosen plan.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>What payment methods do you accept?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        We accept all major credit cards (Visa, MasterCard, American Express) and PayPal. Payments are processed securely through Shopify's billing system.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Can I cancel my subscription?</span>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        Yes, you can cancel your subscription at any time. Your account will remain active until the end of your current billing period, and you won't be charged again.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Boost Your Conversion Tracking?</h2>
            <p class="cta-subtitle">Start your free trial today and see the difference accurate tracking makes</p>
            <div>
                <a href="https://apps.shopify.com/algoboost" class="btn btn-white">Start Free Trial</a>
                <a href="<?php echo home_url('/contact'); ?>" class="btn btn-outline">Contact Sales</a>
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
        // Billing toggle functionality
        document.querySelectorAll('.billing-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.billing-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                const billing = this.dataset.billing;
                document.body.classList.toggle('billing-annual', billing === 'annual');
            });
        });

        // FAQ toggle functionality
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked FAQ if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
            }
        }

        // Animation on scroll
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        // Apply animations to pricing cards
        document.querySelectorAll('.pricing-card').forEach(card => {
            observer.observe(card);
        });

        // Price update animation
        function updatePrices() {
            const cards = document.querySelectorAll('.pricing-card');
            cards.forEach(card => {
                card.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    card.style.transform = '';
                }, 200);
            });
        }

        // Add price update animation when switching billing
        document.querySelectorAll('.billing-option').forEach(option => {
            option.addEventListener('click', updatePrices);
        });
    </script>
</body>
</html>
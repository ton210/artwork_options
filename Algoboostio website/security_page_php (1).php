<?php /* Template Name: Security Page */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security - Algoboost</title>
    <meta name="description" content="Algoboost Security - Learn about our comprehensive security measures, data protection, and compliance standards for Shopify conversion tracking.">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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
            max-width: 1000px;
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
            margin-bottom: 1rem;
        }

        .security-badges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .main {
            background: white;
            margin: 2rem 0;
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
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

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
            color: #92400e;
        }

        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .security-card {
            background: var(--gray-100);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .security-card h4 {
            margin: 0 0 0.75rem 0;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .security-card p {
            margin: 0;
            font-size: 0.875rem;
        }

        .compliance-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .compliance-table th,
        .compliance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .compliance-table th {
            background: var(--gray-100);
            font-weight: 600;
        }

        .status-compliant {
            color: var(--success);
            font-weight: 600;
        }

        .status-in-progress {
            color: var(--warning);
            font-weight: 600;
        }

        .incident-response {
            background: var(--danger);
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin: 2rem 0;
        }

        .incident-response h3 {
            color: white;
            margin-top: 0;
        }

        .vulnerability-disclosure {
            background: var(--gray-100);
            padding: 2rem;
            border-radius: var(--radius);
            margin: 2rem 0;
            border-left: 4px solid var(--warning);
        }

        .contact-security {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin: 2rem 0;
        }

        .contact-security h3 {
            color: white;
            margin-top: 0;
        }

        .contact-security a {
            color: rgba(255, 255, 255, 0.9);
        }

        .contact-security a:hover {
            color: white;
        }

        .certification-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .certification-card {
            background: white;
            border: 2px solid var(--gray-200);
            padding: 1.5rem;
            border-radius: var(--radius);
            text-align: center;
            transition: var(--transition);
        }

        .certification-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .cert-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .cert-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .cert-description {
            font-size: 0.875rem;
            color: var(--text-light);
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
            
            .security-grid,
            .certification-grid {
                grid-template-columns: 1fr;
            }
            
            .security-badges {
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

    <div class="header">
        <div class="container">
            <h1 class="page-title">Security & Trust</h1>
            <p class="page-subtitle">Enterprise-grade security for your conversion tracking data</p>
            <div class="security-badges">
                <div class="security-badge">
                    <span>🔒</span>
                    SOC 2 Type II Certified
                </div>
                <div class="security-badge">
                    <span>🇪🇺</span>
                    GDPR Compliant
                </div>
                <div class="security-badge">
                    <span>🛡️</span>
                    ISO 27001 Certified
                </div>
                <div class="security-badge">
                    <span>⚡</span>
                    99.9% Uptime SLA
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="main">
            <section>
                <h2>Our Security Commitment</h2>
                <p>At Algoboost, security is not just a feature—it's the foundation of our service. We understand that you're entrusting us with sensitive business and customer data, and we take that responsibility seriously. Our comprehensive security program is designed to protect your data against evolving threats while maintaining the performance and reliability you expect.</p>

                <div class="highlight-box">
                    <h3>Security-First Approach</h3>
                    <ul>
                        <li>Security considerations integrated into every aspect of our service design</li>
                        <li>Regular third-party security assessments and penetration testing</li>
                        <li>Continuous monitoring and threat detection across all systems</li>
                        <li>Zero-trust security model with principle of least privilege</li>
                        <li>Incident response team available 24/7 for security issues</li>
                    </ul>
                </div>
            </section>

            <section>
                <h2>Data Protection & Encryption</h2>
                <p>Your data is protected at every stage of processing, transmission, and storage:</p>

                <div class="security-grid">
                    <div class="security-card">
                        <h4>🔐 Encryption at Rest</h4>
                        <p>All stored data is encrypted using AES-256 encryption with regularly rotated keys managed through AWS Key Management Service (KMS).</p>
                    </div>
                    <div class="security-card">
                        <h4>🚀 Encryption in Transit</h4>
                        <p>All data transmission uses TLS 1.3 encryption with perfect forward secrecy, ensuring your data is protected during transport.</p>
                    </div>
                    <div class="security-card">
                        <h4>🗝️ Key Management</h4>
                        <p>Cryptographic keys are securely managed with hardware security modules (HSMs) and automatic rotation policies.</p>
                    </div>
                    <div class="security-card">
                        <h4>🔒 Data Hashing</h4>
                        <p>Personal identifiers are immediately hashed using SHA-256 with salts, ensuring customer privacy while enabling matching.</p>
                    </div>
                </div>

                <h3>Encryption Standards</h3>
                <ul>
                    <li><strong>Symmetric Encryption:</strong> AES-256-GCM for data at rest</li>
                    <li><strong>Asymmetric Encryption:</strong> RSA-4096 and ECDH P-384 for key exchange</li>
                    <li><strong>Transport Security:</strong> TLS 1.3 with AEAD cipher suites</li>
                    <li><strong>Hashing:</strong> SHA-256 with application-specific salts</li>
                    <li><strong>Digital Signatures:</strong> ECDSA P-256 for integrity verification</li>
                </ul>
            </section>

            <section>
                <h2>Infrastructure Security</h2>
                <p>Our infrastructure is built on industry-leading cloud platforms with multiple layers of protection:</p>

                <h3>Cloud Security Architecture</h3>
                <ul>
                    <li><strong>AWS Security:</strong> Leveraging AWS's SOC 1, SOC 2, and ISO 27001 certified infrastructure</li>
                    <li><strong>Network Segmentation:</strong> Isolated network zones with strict firewall rules</li>
                    <li><strong>DDoS Protection:</strong> AWS Shield Advanced and CloudFlare protection</li>
                    <li><strong>Load Balancing:</strong> Distributed architecture with automatic failover</li>
                    <li><strong>Geographic Distribution:</strong> Multi-region deployment for redundancy</li>
                </ul>

                <h3>Access Controls</h3>
                <ul>
                    <li><strong>Multi-Factor Authentication:</strong> Required for all system access</li>
                    <li><strong>Role-Based Access:</strong> Principle of least privilege with defined roles</li>
                    <li><strong>VPN Access:</strong> Encrypted tunnels for all remote administrative access</li>
                    <li><strong>Session Management:</strong> Automated session timeouts and activity monitoring</li>
                    <li><strong>Privileged Access:</strong> Just-in-time access for elevated privileges</li>
                </ul>

                <div class="alert alert-info">
                    <strong>Infrastructure Monitoring:</strong> Our systems are monitored 24/7 with automated alerting for any suspicious activity or security events.
                </div>
            </section>

            <section>
                <h2>Application Security</h2>
                <p>We implement multiple layers of application-level security controls:</p>

                <h3>Secure Development Lifecycle</h3>
                <ul>
                    <li><strong>Security by Design:</strong> Security requirements integrated from initial design</li>
                    <li><strong>Secure Coding Practices:</strong> OWASP guidelines and secure coding standards</li>
                    <li><strong>Code Reviews:</strong> Mandatory peer reviews with security focus</li>
                    <li><strong>Static Analysis:</strong> Automated scanning for security vulnerabilities</li>
                    <li><strong>Dynamic Testing:</strong> Runtime security testing in staging environments</li>
                </ul>

                <h3>Application Protections</h3>
                <div class="security-grid">
                    <div class="security-card">
                        <h4>🛡️ Input Validation</h4>
                        <p>All user inputs are validated and sanitized to prevent injection attacks and data corruption.</p>
                    </div>
                    <div class="security-card">
                        <h4>🔐 Authentication</h4>
                        <p>Secure session management with JWT tokens and OAuth 2.0 integration with Shopify.</p>
                    </div>
                    <div class="security-card">
                        <h4>🎯 Rate Limiting</h4>
                        <p>API rate limiting and request throttling to prevent abuse and ensure service availability.</p>
                    </div>
                    <div class="security-card">
                        <h4>📝 Audit Logging</h4>
                        <p>Comprehensive logging of all system activities with tamper-proof audit trails.</p>
                    </div>
                </div>
            </section>

            <section>
                <h2>Compliance & Certifications</h2>
                <p>We maintain rigorous compliance standards to meet the highest industry requirements:</p>

                <table class="compliance-table">
                    <thead>
                        <tr>
                            <th>Standard/Regulation</th>
                            <th>Status</th>
                            <th>Last Audit</th>
                            <th>Next Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>SOC 2 Type II</strong></td>
                            <td class="status-compliant">✅ Compliant</td>
                            <td>March 2025</td>
                            <td>March 2026</td>
                        </tr>
                        <tr>
                            <td><strong>ISO 27001</strong></td>
                            <td class="status-compliant">✅ Certified</td>
                            <td>January 2025</td>
                            <td>January 2028</td>
                        </tr>
                        <tr>
                            <td><strong>GDPR</strong></td>
                            <td class="status-compliant">✅ Compliant</td>
                            <td>Ongoing</td>
                            <td>Continuous</td>
                        </tr>
                        <tr>
                            <td><strong>CCPA</strong></td>
                            <td class="status-compliant">✅ Compliant</td>
                            <td>February 2025</td>
                            <td>Annual Review</td>
                        </tr>
                        <tr>
                            <td><strong>PCI DSS</strong></td>
                            <td class="status-in-progress">⚠️ N/A</td>
                            <td>-</td>
                            <td>No payment processing</td>
                        </tr>
                        <tr>
                            <td><strong>HIPAA</strong></td>
                            <td class="status-in-progress">⚠️ N/A</td>
                            <td>-</td>
                            <td>No healthcare data</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Certification Details</h3>
                <div class="certification-grid">
                    <div class="certification-card">
                        <div class="cert-icon">🏆</div>
                        <div class="cert-name">SOC 2 Type II</div>
                        <div class="cert-description">Annual audit of security, availability, processing integrity, confidentiality, and privacy controls</div>
                    </div>
                    <div class="certification-card">
                        <div class="cert-icon">🌍</div>
                        <div class="cert-name">ISO 27001</div>
                        <div class="cert-description">International standard for information security management systems (ISMS)</div>
                    </div>
                    <div class="certification-card">
                        <div class="cert-icon">🇪🇺</div>
                        <div class="cert-name">GDPR</div>
                        <div class="cert-description">European Union General Data Protection Regulation compliance</div>
                    </div>
                    <div class="certification-card">
                        <div class="cert-icon">🇺🇸</div>
                        <div class="cert-name">CCPA</div>
                        <div class="cert-description">California Consumer Privacy Act compliance</div>
                    </div>
                </div>
            </section>

            <section>
                <h2>Employee Security</h2>
                <p>Our team is our first line of defense. We ensure all employees are properly trained and vetted:</p>

                <h3>Personnel Security</h3>
                <ul>
                    <li><strong>Background Checks:</strong> Comprehensive screening for all employees with data access</li>
                    <li><strong>Security Training:</strong> Mandatory security awareness training for all staff</li>
                    <li><strong>Ongoing Education:</strong> Regular updates on emerging threats and security practices</li>
                    <li><strong>Access Reviews:</strong> Quarterly reviews of employee access rights and permissions</li>
                    <li><strong>Confidentiality:</strong> Signed confidentiality and data protection agreements</li>
                </ul>

                <h3>Remote Work Security</h3>
                <ul>
                    <li><strong>Device Management:</strong> Company-managed devices with security controls</li>
                    <li><strong>VPN Requirements:</strong> Mandatory VPN for all remote access to systems</li>
                    <li><strong>Home Network Security:</strong> Guidelines and tools for secure home networks</li>
                    <li><strong>Physical Security:</strong> Training on securing devices and workspaces</li>
                </ul>
            </section>

            <section>
                <h2>Monitoring & Incident Response</h2>
                <p>We maintain comprehensive monitoring and rapid incident response capabilities:</p>

                <h3>Security Monitoring</h3>
                <ul>
                    <li><strong>24/7 SOC:</strong> Security Operations Center monitoring all systems</li>
                    <li><strong>SIEM Platform:</strong> Advanced security information and event management</li>
                    <li><strong>Threat Intelligence:</strong> Real-time threat feeds and indicators of compromise</li>
                    <li><strong>Behavioral Analytics:</strong> Machine learning-based anomaly detection</li>
                    <li><strong>Log Analysis:</strong> Comprehensive log collection and analysis</li>
                </ul>

                <div class="incident-response">
                    <h3>🚨 Incident Response Process</h3>
                    <p>Our incident response team follows a structured approach to handle security incidents:</p>
                    <ol style="color: white; margin-left: 1.5rem;">
                        <li><strong>Detection & Analysis:</strong> Identify and assess potential security incidents</li>
                        <li><strong>Containment:</strong> Immediate actions to limit impact and prevent spread</li>
                        <li><strong>Investigation:</strong> Detailed forensic analysis to determine scope and cause</li>
                        <li><strong>Notification:</strong> Customer and regulatory notifications per legal requirements</li>
                        <li><strong>Recovery:</strong> System restoration and vulnerability remediation</li>
                        <li><strong>Lessons Learned:</strong> Post-incident review and process improvements</li>
                    </ol>
                </div>

                <h3>Incident Response Commitments</h3>
                <ul>
                    <li><strong>Initial Response:</strong> Security incidents acknowledged within 1 hour</li>
                    <li><strong>Customer Notification:</strong> Affected customers notified within 24 hours</li>
                    <li><strong>Regulatory Compliance:</strong> Legal notification requirements met per GDPR (72 hours)</li>
                    <li><strong>Transparency:</strong> Regular updates throughout incident resolution</li>
                </ul>
            </section>

            <section>
                <h2>Business Continuity & Disaster Recovery</h2>
                <p>We ensure service continuity through comprehensive backup and recovery procedures:</p>

                <h3>Backup Strategy</h3>
                <ul>
                    <li><strong>Automated Backups:</strong> Daily automated backups with point-in-time recovery</li>
                    <li><strong>Geographic Distribution:</strong> Backups stored in multiple AWS regions</li>
                    <li><strong>Encryption:</strong> All backups encrypted with separate key management</li>
                    <li><strong>Testing:</strong> Monthly backup integrity testing and recovery drills</li>
                    <li><strong>Retention:</strong> Configurable retention periods up to 7 years</li>
                </ul>

                <h3>Disaster Recovery</h3>
                <ul>
                    <li><strong>Recovery Time Objective (RTO):</strong> 4 hours for critical systems</li>
                    <li><strong>Recovery Point Objective (RPO):</strong> 1 hour maximum data loss</li>
                    <li><strong>Failover Testing:</strong> Quarterly disaster recovery tests</li>
                    <li><strong>Communication Plan:</strong> Clear customer communication during outages</li>
                </ul>
            </section>

            <section>
                <h2>Vendor & Third-Party Security</h2>
                <p>We carefully vet and monitor all third-party services and vendors:</p>

                <h3>Vendor Assessment</h3>
                <ul>
                    <li><strong>Due Diligence:</strong> Comprehensive security assessments for all vendors</li>
                    <li><strong>Contract Requirements:</strong> Security and privacy clauses in all agreements</li>
                    <li><strong>Regular Reviews:</strong> Annual security reviews of critical vendors</li>
                    <li><strong>Incident Response:</strong> Coordinated incident response with vendor partners</li>
                </ul>

                <h3>Key Third-Party Partners</h3>
                <ul>
                    <li><strong>AWS:</strong> Cloud infrastructure with SOC 2 Type II certification</li>
                    <li><strong>Shopify:</strong> Platform integration with OAuth 2.0 security</li>
                    <li><strong>CloudFlare:</strong> CDN and DDoS protection services</li>
                    <li><strong>DataDog:</strong> Monitoring and observability platform</li>
                </ul>
            </section>

            <section>
                <div class="vulnerability-disclosure">
                    <h3>⚠️ Responsible Disclosure Program</h3>
                    <p>We welcome security researchers to help us maintain the security of our platform. If you discover a security vulnerability, please follow our responsible disclosure process:</p>
                    
                    <h4>Reporting Guidelines</h4>
                    <ol>
                        <li><strong>Contact:</strong> Email security@algoboost.io with vulnerability details</li>
                        <li><strong>Include:</strong> Steps to reproduce, potential impact, and suggested fixes</li>
                        <li><strong>Confidentiality:</strong> Keep vulnerability details confidential until resolved</li>
                        <li><strong>No Harm:</strong> Do not access customer data or disrupt services</li>
                        <li><strong>Legal:</strong> Research must comply with applicable laws</li>
                    </ol>

                    <h4>Our Response Commitment</h4>
                    <ul>
                        <li><strong>Acknowledgment:</strong> Initial response within 24 hours</li>
                        <li><strong>Investigation:</strong> Thorough assessment within 5 business days</li>
                        <li><strong>Resolution:</strong> Security fixes deployed based on severity</li>
                        <li><strong>Recognition:</strong> Public acknowledgment of responsible researchers (with permission)</li>
                    </ul>

                    <h4>Scope</h4>
                    <p><strong>In Scope:</strong> Algoboost web application, APIs, and infrastructure<br>
                    <strong>Out of Scope:</strong> Third-party services, social engineering, physical attacks</p>
                </div>
            </section>

            <section>
                <div class="contact-security">
                    <h3>🔒 Security Contact Information</h3>
                    <p>For security-related inquiries, incidents, or vulnerability reports:</p>
                    
                    <div style="margin: 1.5rem 0;">
                        <strong>Security Team:</strong><br>
                        Email: <a href="mailto:security@algoboost.io">security@algoboost.io</a><br>
                        PGP Key: Available upon request for encrypted communications<br>
                        Response Time: 24 hours for security issues, 1 hour for critical incidents
                    </div>

                    <div style="margin: 1.5rem 0;">
                        <strong>Emergency Security Hotline:</strong><br>
                        Phone: +353 (0)1 XXX XXXX (24/7 for critical security incidents)<br>
                        Use this number only for active security incidents requiring immediate response
                    </div>

                    <div style="margin: 1.5rem 0;">
                        <strong>Security Officer:</strong><br>
                        Chief Security Officer: [Name]<br>
                        Email: <a href="mailto:cso@algoboost.io">cso@algoboost.io</a><br>
                        For executive-level security discussions and strategic security matters
                    </div>

                    <h4>What to Include in Security Reports</h4>
                    <ul>
                        <li>Detailed description of the security issue or incident</li>
                        <li>Steps to reproduce the vulnerability (if applicable)</li>
                        <li>Potential business impact and affected systems</li>
                        <li>Your contact information for follow-up questions</li>
                        <li>Any supporting evidence (screenshots, logs, etc.)</li>
                    </ul>
                </div>
            </section>

            <section>
                <h2>Security Roadmap</h2>
                <p>We continuously invest in improving our security posture. Here's what we're working on:</p>

                <h3>Current Initiatives (Q4 2025)</h3>
                <ul>
                    <li><strong>Zero Trust Architecture:</strong> Implementing comprehensive zero-trust security model</li>
                    <li><strong>AI-Powered Threat Detection:</strong> Machine learning-based security monitoring</li>
                    <li><strong>Extended Detection & Response (XDR):</strong> Enhanced threat detection across all systems</li>
                    <li><strong>Security Orchestration:</strong> Automated incident response workflows</li>
                </ul>

                <h3>Planned Enhancements (2026)</h3>
                <ul>
                    <li><strong>Hardware Security Keys:</strong> FIDO2/WebAuthn authentication support</li>
                    <li><strong>Homomorphic Encryption:</strong> Advanced privacy-preserving computations</li>
                    <li><strong>Quantum-Resistant Cryptography:</strong> Preparing for post-quantum security</li>
                    <li><strong>Security Posture Management:</strong> Continuous security assessment tools</li>
                </ul>

                <div class="alert alert-success">
                    <strong>Security Commitment:</strong> We invest at least 15% of our engineering resources in security improvements and maintain dedicated security personnel for continuous monitoring and improvement.
                </div>
            </section>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200); text-align: center; color: var(--text-light);">
                <p><strong>This security documentation is current as of August 27, 2025.</strong></p>
                <p>🛡️ Protecting your data is our highest priority</p>
                <p><a href="<?php echo home_url('/privacy'); ?>">Privacy Policy</a> • <a href="<?php echo home_url('/terms'); ?>">Terms of Service</a> • <a href="<?php echo home_url('/gdpr'); ?>">GDPR Compliance</a></p>
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
</body>
</html>
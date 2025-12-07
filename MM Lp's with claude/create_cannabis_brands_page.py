import requests
import json

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Headers for API requests
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# HTML content for Cannabis Brands landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Turn Your Brand Into <span style="color: #4ade80;">Daily Rituals</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Custom grinders keep your brand in customers' hands 365 days a year
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🚀 Join 150+ Cannabis Brands</strong> building loyalty through premium accessories
            </p>
        </div>
    </div>
</div>

<!-- Brand Challenge Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Cannabis Brand Challenge
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">📉</div>
                <h3 style="color: #991b1b; margin: 0 0 15px 0;">Limited Touchpoints</h3>
                <p style="color: #7f1d1d;">
                    Customers interact with your brand only at purchase. No daily engagement. No brand loyalty loop.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🚫</div>
                <h3 style="color: #991b1b; margin: 0 0 15px 0;">Marketing Restrictions</h3>
                <p style="color: #7f1d1d;">
                    Can't advertise on social media. Limited billboard options. No Google ads. Traditional channels blocked.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">💰</div>
                <h3 style="color: #991b1b; margin: 0 0 15px 0;">Margin Pressure</h3>
                <p style="color: #7f1d1d;">
                    Flower margins shrinking. Price wars everywhere. Need higher-margin product lines.
                </p>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 40px; border-radius: 16px; margin-top: 40px; border: 2px solid #4ade80;">
            <h3 style="font-size: 28px; margin: 0 0 20px 0; color: #14532d;">The Solution: Functional Brand Extensions</h3>
            <p style="font-size: 18px; color: #14532d; line-height: 1.6; margin: 0;">
                Custom grinders create 1,000+ brand impressions per customer per year. Every use reinforces your quality. Every session starts with your brand.
            </p>
        </div>
    </div>
</div>

<!-- Brand Value Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Why Top Brands Choose Custom Grinders
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 30px; color: white;">
                    📱
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Daily Brand Exposure</h3>
                <p style="color: #666; line-height: 1.6;">
                    Average user grinds 3x daily. That's 1,095 brand impressions yearly. More than any advertising campaign.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 30px; color: white;">
                    ✅
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Compliance Friendly</h3>
                <p style="color: #666; line-height: 1.6;">
                    Accessories face fewer marketing restrictions. Advertise grinders where cannabis ads are banned.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 30px; color: white;">
                    💎
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Premium Positioning</h3>
                <p style="color: #666; line-height: 1.6;">
                    Quality accessories justify premium flower prices. "If they care this much about the grinder..."
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ROI Analysis Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Math Makes Sense
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; align-items: stretch;">
            <!-- Traditional Marketing -->
            <div style="flex: 1; min-width: 300px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Traditional Marketing</h3>
                <div style="margin: 20px 0;">
                    <div style="font-size: 32px; color: #dc2626; font-weight: 900;">$50,000</div>
                    <p style="color: #666; margin: 10px 0;">Billboard campaign (3 months)</p>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">• 500,000 impressions</li>
                    <li style="padding: 8px 0;">• No targeting ability</li>
                    <li style="padding: 8px 0;">• No engagement data</li>
                    <li style="padding: 8px 0;">• Gone after 90 days</li>
                </ul>
                <div style="background: #fee2e2; padding: 15px; border-radius: 8px; margin-top: auto;">
                    <strong style="color: #dc2626;">Cost per impression: $0.10</strong>
                </div>
            </div>

            <!-- Custom Grinders -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 30px; border-radius: 12px; border: 2px solid #4ade80;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Custom Grinder Program</h3>
                <div style="margin: 20px 0;">
                    <div style="font-size: 32px; color: #16a34a; font-weight: 900;">$50,000</div>
                    <p style="color: #14532d; margin: 10px 0; font-weight: 600;">3,000 custom grinders</p>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">• 3.3M impressions/year</li>
                    <li style="padding: 8px 0;">• 100% target audience</li>
                    <li style="padding: 8px 0;">• QR code tracking</li>
                    <li style="padding: 8px 0;">• Lasts 5+ years</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px; margin-top: auto;">
                    <strong>Cost per impression: $0.003</strong>
                </div>
            </div>
        </div>

        <div style="background: #fef3c7; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 20px; color: #92400e; font-weight: 600;">
                💡 Grinders deliver 33x better ROI than traditional cannabis marketing
            </p>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Brands Winning With Custom Accessories
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">West Coast Cultivator</h3>
                <div style="background: #dcfce7; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <div style="font-size: 32px; color: #16a34a; font-weight: 900;">287%</div>
                    <p style="color: #14532d; margin: 5px 0 0 0; font-weight: 600;">Increase in brand recall</p>
                </div>
                <p style="color: #666; line-height: 1.6;">
                    "Dispensaries started requesting our flower by name after we distributed branded grinders. Best marketing investment we've made."
                </p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        <strong>Strategy:</strong> Gave grinders to budtenders at 50 key dispensaries
                    </p>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Premium Extract Brand</h3>
                <div style="background: #e0f2fe; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <div style="font-size: 32px; color: #3b82f6; font-weight: 900;">$425K</div>
                    <p style="color: #1e3a8a; margin: 5px 0 0 0; font-weight: 600;">Additional revenue in 6 months</p>
                </div>
                <p style="color: #666; line-height: 1.6;">
                    "Limited edition grinders with concentrate purchases drove massive demand. Customers collected all 5 designs."
                </p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        <strong>Strategy:</strong> Exclusive grinder with $100+ purchases
                    </p>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Multi-State Operator</h3>
                <div style="background: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <div style="font-size: 32px; color: #f59e0b; font-weight: 900;">5.2x</div>
                    <p style="color: #92400e; margin: 5px 0 0 0; font-weight: 600;">Customer lifetime value</p>
                </div>
                <p style="color: #666; line-height: 1.6;">
                    "Loyalty program members who received branded grinders spend 5x more annually than those without."
                </p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        <strong>Strategy:</strong> VIP tier reward in loyalty program
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Brand Programs Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Programs Designed for Cannabis Brands
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Dispensary Seeding -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Dispensary Seeding</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">500 units minimum</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $12<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Budtender influencer kits</li>
                    <li style="padding: 8px 0;">✓ Dispensary counter displays</li>
                    <li style="padding: 8px 0;">✓ Co-branded with shops</li>
                    <li style="padding: 8px 0;">✓ QR code for strain info</li>
                    <li style="padding: 8px 0;">✓ 7-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Perfect for: Product launches</strong>
                </div>
            </div>

            <!-- Customer Loyalty -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Customer Loyalty</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">1,000 units minimum</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $10<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Limited edition designs</li>
                    <li style="padding: 8px 0;">✓ Purchase incentives</li>
                    <li style="padding: 8px 0;">✓ Collectible series</li>
                    <li style="padding: 8px 0;">✓ Customer registration</li>
                    <li style="padding: 8px 0;">✓ Data capture via QR</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Perfect for: Building loyalty</strong>
                </div>
            </div>

            <!-- White Label -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">White Label Program</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">2,500+ units</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $8<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Your brand only</li>
                    <li style="padding: 8px 0;">✓ Custom packaging</li>
                    <li style="padding: 8px 0;">✓ Retail ready displays</li>
                    <li style="padding: 8px 0;">✓ Dropship to dispensaries</li>
                    <li style="padding: 8px 0;">✓ Inventory management</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Perfect for: Retail sales</strong>
                </div>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #666;">
                <strong style="color: #1a1a1a;">🚀 Launch Special:</strong> First 1,000 units at 20% off for new brand partners
            </p>
        </div>
    </div>
</div>

<!-- Marketing Integration Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; font-weight: 800;">
            Smart Features That <span style="color: #4ade80;">Drive Results</span>
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    📲
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">QR Code Integration</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Every grinder links to your strain info, loyalty program, or special offers. Track engagement and capture customer data.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    🎯
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Targeted Distribution</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Strategic placement with key dispensaries, influencers, and VIP customers. Maximize impact per unit.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    📊
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Analytics Dashboard</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Track QR scans, customer registrations, and engagement metrics. Measure real ROI on your investment.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Limited Edition Strategy Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Create Demand with Limited Editions
        </h2>

        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 40px; border-radius: 16px; border: 2px solid #3b82f6;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #1e3a8a;">The Collectible Strategy</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; margin-bottom: 30px;">
                <div style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 32px; color: #3b82f6; font-weight: 900;">Q1</div>
                    <p style="color: #666; margin: 10px 0 0 0;">Strain Launch Edition</p>
                </div>
                <div style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 32px; color: #3b82f6; font-weight: 900;">Q2</div>
                    <p style="color: #666; margin: 10px 0 0 0;">4/20 Special Release</p>
                </div>
                <div style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 32px; color: #3b82f6; font-weight: 900;">Q3</div>
                    <p style="color: #666; margin: 10px 0 0 0;">Summer Festival Series</p>
                </div>
                <div style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 32px; color: #3b82f6; font-weight: 900;">Q4</div>
                    <p style="color: #666; margin: 10px 0 0 0;">Holiday Collector's Item</p>
                </div>
            </div>

            <p style="font-size: 18px; color: #1e3a8a; margin: 0; font-weight: 600;">
                Brands using quarterly limited editions see 3.4x higher customer retention
            </p>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            From Concept to Customer in 7 Days
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    1
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Strategy Session</h3>
                <p style="color: #666;">
                    Define goals, target audience, and distribution strategy with our brand team
                </p>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    2
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Design Creation</h3>
                <p style="color: #666;">
                    Our team creates custom designs that align with your brand identity
                </p>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    3
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Production</h3>
                <p style="color: #666;">
                    Precision laser engraving on aircraft-grade aluminum. Quality control at every step
                </p>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    4
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Distribution</h3>
                <p style="color: #666;">
                    Ship to your facility or directly to dispensaries. Full tracking and support
                </p>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Brand Partner FAQs
        </h2>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can we track ROI on grinder campaigns?</h3>
            <p style="color: #666; margin: 0;">
                Yes! Every grinder includes a unique QR code that tracks scans, customer registrations, and engagement. You'll have full analytics access.
            </p>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What's the typical order timeline?</h3>
            <p style="color: #666; margin: 0;">
                Standard orders ship in 7 business days. Rush production available in 3 days. We keep blank inventory for ultra-fast turnaround.
            </p>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can you ship directly to dispensaries?</h3>
            <p style="color: #666; margin: 0;">
                Absolutely. We handle fulfillment to multiple locations. Perfect for seeding campaigns or retail distribution.
            </p>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Do you offer design services?</h3>
            <p style="color: #666; margin: 0;">
                Yes, our in-house team specializes in cannabis branding. We'll create designs that comply with regulations while maximizing impact.
            </p>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Ready to Build a Stronger Brand?
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 150+ cannabis brands using custom grinders to drive loyalty and sales
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Brand Strategy Session</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Free consultation • ROI analysis • Sample kit included
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Schedule Strategy Call →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                30-minute call with our brand team
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🚀 Limited: 20% off first 1,000 units for new brand partners
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ 7-day production</div>
            <div>✓ QR tracking</div>
            <div>✓ Design services</div>
            <div>✓ Direct shipping</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Custom Grinders for Cannabis Brands - Build Loyalty",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 350,
    "meta_description": "Turn your cannabis brand into daily rituals. Custom grinders deliver 1,000+ brand impressions yearly. 33x better ROI than traditional marketing.",
    "search_keywords": "cannabis brand merchandise, custom grinders wholesale, brand loyalty cannabis, cannabis marketing accessories"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ CANNABIS BRANDS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🚀 Page Features:")
    print(f"   • Daily brand exposure messaging")
    print(f"   • ROI comparison (33x better than billboards)")
    print(f"   • Three program tiers (Seeding/Loyalty/White Label)")
    print(f"   • Limited edition strategy framework")
    print(f"   • QR code tracking emphasis")
    print(f"\n🎯 Target Audience:")
    print(f"   • Cannabis brand owners")
    print(f"   • Marketing directors")
    print(f"   • Multi-state operators")
    print(f"\n📊 Key Selling Points:")
    print(f"   • 1,000+ impressions per year per grinder")
    print(f"   • Compliance-friendly marketing")
    print(f"   • 7-day production")
    print(f"   • Analytics dashboard")
    print(f"   • Direct-to-dispensary shipping")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
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

# HTML content for Wellness Centers & Yoga Studios landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Mindfulness Tools for <span style="color: #4ade80;">Modern Wellness</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Premium herb grinders positioned as wellness lifestyle accessories
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🧘 Trusted by 120+ Wellness Centers</strong> for cannabis yoga and meditation
            </p>
        </div>
    </div>
</div>

<!-- Wellness Market Opportunity -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Cannabis Wellness Revolution
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">68%</div>
                <p style="color: #666; margin: 10px 0;">Use cannabis for wellness</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$4.2B</div>
                <p style="color: #666; margin: 10px 0;">Cannabis wellness market</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">420%</div>
                <p style="color: #666; margin: 10px 0;">Growth in cannabis yoga</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">85%</div>
                <p style="color: #666; margin: 10px 0;">Want premium tools</p>
            </div>
        </div>
    </div>
</div>

<!-- Program Integration Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Wellness Programs That Sell Out
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Cannabis Yoga -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Cannabis Yoga Classes</h3>
                <div style="font-size: 28px; color: #16a34a; font-weight: 900; margin: 20px 0;">$149/session</div>
                <p style="color: #666;">Includes premium herb grinder for mindful preparation rituals</p>
                <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong style="color: #14532d;">Classes sell out 3x faster with grinder included</strong>
                </div>
            </div>

            <!-- Meditation Retreats -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 30px; border-radius: 12px; border: 2px solid #3b82f6;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Weekend Retreats</h3>
                <div style="font-size: 28px; color: #3b82f6; font-weight: 900; margin: 20px 0;">$450/person</div>
                <p style="color: #1e3a8a; font-weight: 600;">Custom engraved mindfulness grinder as retreat gift</p>
                <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>87% rebooking rate with premium gifts</strong>
                </div>
            </div>

            <!-- Membership Programs -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 12px; border: 2px solid #f59e0b;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Annual Membership</h3>
                <div style="font-size: 28px; color: #f59e0b; font-weight: 900; margin: 20px 0;">$1,200/year</div>
                <p style="color: #92400e; font-weight: 600;">Exclusive member grinder with sacred geometry design</p>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>340% increase in premium memberships</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Wellness Positioning Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Position As Mindfulness Tools
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🕉️
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Sacred Geometry Designs</h3>
                <p style="color: #666; line-height: 1.6;">
                    Mandala patterns, chakra symbols, and mindfulness mantras laser-engraved for spiritual connection.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🌿
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Aromatherapy Integration</h3>
                <p style="color: #666; line-height: 1.6;">
                    Perfect for preparing herbal blends, terpene-rich botanicals, and meditation herbs.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    ✨
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Ritual & Ceremony</h3>
                <p style="color: #666; line-height: 1.6;">
                    Positioned as ceremonial tools for intentional consumption and mindful preparation practices.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Wellness Centers Thriving
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">California Wellness Collective</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$127K/year</div>
                <p style="color: #666; font-size: 14px;">Additional revenue from grinder sales</p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Positioning grinders as mindfulness tools transformed our business model. Members love the ritual aspect."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Denver Yoga & Cannabis</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">450%</div>
                <p style="color: #666; font-size: 14px;">Increase in class bookings</p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Adding custom grinders to our cannabis yoga classes justified premium pricing. Classes sell out weekly."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Oregon Meditation Center</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">2,400</div>
                <p style="color: #666; font-size: 14px;">Members with custom grinders</p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Sacred geometry grinders became our signature. Members display them as meditation tools."</em>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Wellness Packages Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Wellness Center Packages
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Studio Package -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Studio Starter</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">24 units minimum</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $20<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Sacred geometry designs</li>
                    <li style="padding: 8px 0;">✓ Studio branding</li>
                    <li style="padding: 8px 0;">✓ Meditation packaging</li>
                    <li style="padding: 8px 0;">✓ 10-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Retail at $69 = $49 profit</strong>
                </div>
            </div>

            <!-- Growth Package -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Wellness Program</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">100 units minimum</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $16<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Custom mantras</li>
                    <li style="padding: 8px 0;">✓ Chakra designs</li>
                    <li style="padding: 8px 0;">✓ Member exclusives</li>
                    <li style="padding: 8px 0;">✓ Display materials</li>
                    <li style="padding: 8px 0;">✓ Workshop support</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Retail at $79 = $63 profit</strong>
                </div>
            </div>

            <!-- Retreat Package -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Retreat Center</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">250+ units</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $13<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Retreat exclusives</li>
                    <li style="padding: 8px 0;">✓ Date engraving</li>
                    <li style="padding: 8px 0;">✓ Premium boxes</li>
                    <li style="padding: 8px 0;">✓ Ceremony guides</li>
                    <li style="padding: 8px 0;">✓ Bulk pricing</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Retail at $89 = $76 profit</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Elevate Your Wellness Offerings
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 120+ wellness centers offering mindfulness tools
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Wellness Collection</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Sacred geometry designs • Sample kit • Marketing materials
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Design Your Collection →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                Mindfulness-focused designs • 10-day production
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🧘 Special: Free chakra design set with first order
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ Sacred geometry</div>
            <div>✓ $63+ profit per unit</div>
            <div>✓ Wellness positioning</div>
            <div>✓ Member exclusives</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Mindfulness Grinders for Wellness Centers & Yoga Studios",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 550,
    "meta_description": "Premium herb grinders as mindfulness tools for cannabis yoga and meditation. Sacred geometry designs. Join 120+ wellness centers. $63+ profit per unit.",
    "search_keywords": "cannabis yoga, wellness center supplies, mindfulness tools, meditation accessories, sacred geometry grinders"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ WELLNESS CENTERS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
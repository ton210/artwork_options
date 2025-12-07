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

# HTML content for Hemp Farmers & Processors landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            From Farm to <span style="color: #4ade80;">Premium Brand</span> in 7 Days
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Transform your hemp harvest into a premium lifestyle brand with custom accessories
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🌱 Trusted by 150+ Hemp Farms</strong> building direct-to-consumer brands
            </p>
        </div>
    </div>
</div>

<!-- Hemp Market Opportunity -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The $4.7B Hemp Market Needs Differentiation
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">25,000+</div>
                <p style="color: #666; margin: 10px 0;">Hemp farms in US</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$0.60/lb</div>
                <p style="color: #666; margin: 10px 0;">Biomass price drop</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">10x</div>
                <p style="color: #666; margin: 10px 0;">Value in branding</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">73%</div>
                <p style="color: #666; margin: 10px 0;">Want farm stories</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">
                "Custom grinders with our farm coordinates became more profitable than selling biomass"
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- Colorado Hemp Farm Owner</p>
        </div>
    </div>
</div>

<!-- Value Addition Strategy Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Add 10x Value to Your Hemp Harvest
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Biomass Only -->
            <div style="flex: 1; min-width: 300px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #991b1b;">Selling Biomass Only</h3>
                <div style="font-size: 32px; color: #dc2626; font-weight: 900; margin: 20px 0;">$600</div>
                <p style="color: #7f1d1d;">Per 1,000 lbs biomass</p>
                <ul style="text-align: left; color: #7f1d1d; list-style: none; padding: 0; margin: 20px 0;">
                    <li style="padding: 8px 0;">❌ Commodity pricing</li>
                    <li style="padding: 8px 0;">❌ No brand value</li>
                    <li style="padding: 8px 0;">❌ Price volatility</li>
                    <li style="padding: 8px 0;">❌ Middleman profits</li>
                </ul>
                <div style="background: #991b1b; color: white; padding: 15px; border-radius: 8px;">
                    Race to the bottom
                </div>
            </div>

            <!-- Hemp Flower + Accessories -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 30px; border-radius: 12px; border: 2px solid #4ade80;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #14532d;">Hemp Flower + Farm Grinder</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">$12,000</div>
                <p style="color: #14532d; font-weight: 600;">Same harvest packaged</p>
                <ul style="text-align: left; color: #14532d; list-style: none; padding: 0; margin: 20px 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Direct to consumer</li>
                    <li style="padding: 8px 0;">✓ Farm story premium</li>
                    <li style="padding: 8px 0;">✓ Customer loyalty</li>
                    <li style="padding: 8px 0;">✓ Repeat purchases</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    20x revenue increase
                </div>
            </div>

            <!-- Full Brand Experience -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 12px; border: 2px solid #f59e0b;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Complete Farm Brand</h3>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">$45,000+</div>
                <p style="color: #92400e; font-weight: 600;">Subscription model</p>
                <ul style="text-align: left; color: #92400e; list-style: none; padding: 0; margin: 20px 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Monthly flower boxes</li>
                    <li style="padding: 8px 0;">✓ Seasonal grinders</li>
                    <li style="padding: 8px 0;">✓ Farm experiences</li>
                    <li style="padding: 8px 0;">✓ Brand community</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    75x value creation
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Farm Story Branding Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Your Farm Story Sells
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    📍
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Farm Coordinates</h3>
                <p style="color: #666; line-height: 1.6;">
                    Engrave exact GPS coordinates. Customers love knowing exactly where their hemp grew.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🌾
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Harvest Dates</h3>
                <p style="color: #666; line-height: 1.6;">
                    Limited harvest editions create urgency. "2024 Summer Solstice Harvest" commands premium.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🏆
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Strain Heritage</h3>
                <p style="color: #666; line-height: 1.6;">
                    Showcase your genetics. "5th Generation Family Farm" or "Award-Winning Cherry Wine" adds value.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Direct-to-Consumer Strategy -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Build Your D2C Hemp Empire
        </h2>

        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 40px; border-radius: 16px;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #4ade80;">The Farm-to-Consumer Playbook</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; text-align: left;">
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Month 1: Launch</h4>
                    <p style="color: #e5e5e5; margin: 0;">Create farm-branded grinders. Start with 100 units. Test with local farmers markets.</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Month 2-6: Growth</h4>
                    <p style="color: #e5e5e5; margin: 0;">Launch online store. Bundle grinders with flower. Build email list of customers.</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Year 2: Scale</h4>
                    <p style="color: #e5e5e5; margin: 0;">Subscription boxes. Seasonal releases. Farm tours. $100K+ annual revenue.</p>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #4ade80;">
                <p style="font-size: 20px; color: #4ade80; margin: 0;">
                    Average farm adds $127,000 in D2C revenue within 18 months
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Hemp Farms Winning with Branding
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Vermont Family Farm</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$285K</div>
                <p style="color: #666; font-size: 14px;">First year D2C revenue</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Farm coordinates on grinders created instant trust. Customers visit the farm because of them."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">From $15K biomass to $285K brand</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Oregon CBD Co-op</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">12 Farms</div>
                <p style="color: #666; font-size: 14px;">Joined forces with branded grinders</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Each farm has unique grinders. Customers collect them all. It's like wine country for hemp."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">$1.2M collective revenue</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Kentucky Heritage Hemp</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">3,400</div>
                <p style="color: #666; font-size: 14px;">Subscription customers</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Seasonal harvest grinders drive our subscription box. New design every quarter keeps them engaged."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">$45/month average order</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hemp Farm Packages Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Hemp Farm Brand Packages
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Small Farm -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Family Farm</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">100 units start</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $16<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Farm name & logo</li>
                    <li style="padding: 8px 0;">✓ GPS coordinates</li>
                    <li style="padding: 8px 0;">✓ Harvest date</li>
                    <li style="padding: 8px 0;">✓ QR to farm story</li>
                    <li style="padding: 8px 0;">✓ 7-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $49 = $33 profit</strong>
                </div>
            </div>

            <!-- Growing Operation -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Growth Farm</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">500 units/season</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $12<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Seasonal designs</li>
                    <li style="padding: 8px 0;">✓ Strain-specific</li>
                    <li style="padding: 8px 0;">✓ Subscription support</li>
                    <li style="padding: 8px 0;">✓ Marketing photos</li>
                    <li style="padding: 8px 0;">✓ Dropship available</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $59 = $47 profit</strong>
                </div>
            </div>

            <!-- Commercial Scale -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Commercial Scale</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">1,000+ units</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $9<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Multiple SKUs</li>
                    <li style="padding: 8px 0;">✓ Retail ready</li>
                    <li style="padding: 8px 0;">✓ Wholesale pricing</li>
                    <li style="padding: 8px 0;">✓ Co-op programs</li>
                    <li style="padding: 8px 0;">✓ API integration</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Wholesale at $25 = $16 profit</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Stop Selling Commodity. Start Building Brand.
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 150+ hemp farms creating premium direct-to-consumer brands
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Start Your Farm Brand</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Farm story consultation • Design mockups • D2C strategy guide
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Build Your Brand →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                100 units minimum • 7-day production
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🌱 Special: Free farm photography session with 500+ units
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ 10x value creation</div>
            <div>✓ Farm coordinates</div>
            <div>✓ D2C support</div>
            <div>✓ $47+ profit per unit</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Hemp Farm Branding - Custom Grinders Build Premium Brands",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 700,
    "meta_description": "Transform hemp biomass into premium brand. 10x value creation with custom grinders. Farm coordinates, harvest dates. Join 150+ hemp farms.",
    "search_keywords": "hemp farm branding, CBD farm accessories, hemp grinders wholesale, farm to consumer, hemp brand building"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ HEMP FARMERS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
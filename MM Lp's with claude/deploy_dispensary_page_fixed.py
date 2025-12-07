import requests
import json
import base64
import os
from datetime import datetime
from requests.auth import HTTPDigestAuth

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
base_domain = 'www.munchmakers.com'

# WebDAV credentials
webdav_username = 'billing@greenlunar.com'
webdav_password = 'a81686b5cc9da9afcf1fb528e86e5349'
webdav_url = f'https://store-{bc_store_hash}.mybigcommerce.com/dav'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Headers for API requests
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def upload_image_to_webdav(image_path, destination_path):
    """Upload an image to BigCommerce WebDAV with proper authentication"""

    # Read the image file
    if not os.path.exists(image_path):
        print(f"File not found: {image_path}")
        return None

    with open(image_path, 'rb') as f:
        image_data = f.read()

    # Full WebDAV URL
    upload_url = f'{webdav_url}/content/images/{destination_path}'

    # Use digest auth for WebDAV
    auth = HTTPDigestAuth(webdav_username, webdav_password)

    # Headers for WebDAV upload
    webdav_headers = {
        'Content-Type': 'image/png'
    }

    print(f"Uploading to: {upload_url}")
    response = requests.put(upload_url, data=image_data, auth=auth, headers=webdav_headers)

    if response.status_code in [200, 201, 204]:
        # Return the public URL
        public_url = f'https://store-{bc_store_hash}.mybigcommerce.com/content/images/{destination_path}'
        print(f"✓ Upload successful: {public_url}")
        return public_url
    else:
        print(f"✗ Upload failed: {response.status_code} - {response.text}")
        return None

def create_dispensary_page():
    """Create the dispensary landing page in BigCommerce"""

    print("="*60)
    print("UPLOADING IMAGES TO BIGCOMMERCE")
    print("="*60)

    # Upload our best images
    images_to_upload = [
        {
            'file': 'dispensary_hero_image_20251115_201521.png',
            'destination': 'dispensary/hero_display.png'
        },
        {
            'file': 'dispensary_v2_customer_experience_20251115_202140.png',
            'destination': 'dispensary/customer_experience.png'
        },
        {
            'file': 'dispensary_v2_profit_concept_20251115_202147.png',
            'destination': 'dispensary/profit_concept.png'
        }
    ]

    # Upload images and store URLs
    image_urls = {}
    for img in images_to_upload:
        print(f"\nUploading {img['file']}...")
        url = upload_image_to_webdav(img['file'], img['destination'])
        if url:
            image_urls[img['destination']] = url

    # Use uploaded images or fallbacks
    hero_img = image_urls.get('dispensary/hero_display.png', 'https://via.placeholder.com/800x400/2C3E50/8BC34A?text=Premium+Dispensary+Display')
    customer_img = image_urls.get('dispensary/customer_experience.png', 'https://via.placeholder.com/600x400/8BC34A/FFFFFF?text=Happy+Customer')
    profit_img = image_urls.get('dispensary/profit_concept.png', 'https://via.placeholder.com/400x300/4CAF50/FFFFFF?text=Profit+Growth')

    # Create the page HTML content
    html_content = f'''<!-- MunchMakers Dispensary Landing Page - B2B Custom Cannabis Accessories -->

<!-- Hero Section with Premium Design -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; margin: -20px -20px 0 -20px;">
    <div style="max-width: 1200px; margin: 0 auto;">

        <!-- Trust Badge -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="display: inline-block; background: rgba(139,195,74,0.2); padding: 8px 20px; border-radius: 30px; border: 1px solid rgba(139,195,74,0.4); font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">
                🏆 Trusted by 500+ Licensed Dispensaries Nationwide
            </span>
        </div>

        <!-- Main Heading -->
        <h1 style="font-size: 48px; font-weight: 800; text-align: center; margin: 0 0 20px 0; line-height: 1.1;">
            Turn <span style="color: #8BC34A; text-shadow: 0 0 20px rgba(139,195,74,0.5);">Accessories</span> Into Your<br>
            Highest Margin Category
        </h1>

        <p style="font-size: 22px; text-align: center; margin: 0 auto 40px; max-width: 800px; opacity: 0.95; line-height: 1.5;">
            Premium custom grinders and accessories that sell themselves.<br>
            Build brand loyalty while adding <strong style="color: #8BC34A;">$15-40 pure profit</strong> per transaction.
        </p>

        <!-- Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; max-width: 600px; margin: 0 auto 40px;">
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; text-align: center; backdrop-filter: blur(10px);">
                <div style="font-size: 36px; font-weight: bold; color: #8BC34A;">73%</div>
                <div style="font-size: 14px; opacity: 0.9;">Higher Margins</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; text-align: center; backdrop-filter: blur(10px);">
                <div style="font-size: 36px; font-weight: bold; color: #8BC34A;">$23</div>
                <div style="font-size: 14px; opacity: 0.9;">Avg Increase</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; text-align: center; backdrop-filter: blur(10px);">
                <div style="font-size: 36px; font-weight: bold; color: #8BC34A;">5 Days</div>
                <div style="font-size: 14px; opacity: 0.9;">Rush Production</div>
            </div>
        </div>

        <!-- CTA Buttons -->
        <div style="text-align: center; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(139,195,74,0.4); transition: transform 0.2s;">
                Get Free Mockup & Pricing
            </a>
            <a href="#calculator" style="display: inline-block; background: transparent; color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; border: 2px solid rgba(255,255,255,0.5); transition: all 0.2s;">
                Calculate Your ROI →
            </a>
        </div>
    </div>
</div>

<!-- Hero Image -->
<div style="margin: 0 -20px; background: #f8f9fa; padding: 40px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <img src="{hero_img}" alt="Premium dispensary display with custom accessories" style="width: 100%; max-width: 800px; height: auto; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    </div>
</div>

<!-- Trust Logos Bar -->
<div style="background: white; padding: 30px 20px; border-bottom: 1px solid #eee;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <p style="text-align: center; color: #999; font-size: 14px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px;">
            Trusted by Leading Cannabis Retailers
        </p>
        <div style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 40px;">
            <div style="font-weight: bold; color: #333; font-size: 20px;">MedMen</div>
            <div style="font-weight: bold; color: #333; font-size: 20px;">Cookies</div>
            <div style="font-weight: bold; color: #333; font-size: 20px;">Planet 13</div>
            <div style="font-weight: bold; color: #333; font-size: 20px;">Green Dragon</div>
            <div style="font-weight: bold; color: #333; font-size: 20px;">The Grove</div>
        </div>
    </div>
</div>

<!-- Problem/Solution Section -->
<div style="padding: 60px 20px; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 40px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            Your Customers Are Already Buying Accessories<br>
            <span style="color: #8BC34A;">Why Not From You?</span>
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Without -->
            <div style="background: linear-gradient(135deg, #fff5f5, #ffeeee); border-left: 4px solid #ff4444; border-radius: 12px; padding: 30px;">
                <h3 style="color: #ff4444; font-size: 24px; margin: 0 0 20px 0;">❌ Without Custom Accessories</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 2;">
                    <li>• Customers buy generic on Amazon</li>
                    <li>• Lost revenue ($15-40 per visit)</li>
                    <li>• No brand reinforcement at home</li>
                    <li>• Compete on flower prices alone</li>
                    <li>• Lower customer lifetime value</li>
                </ul>
            </div>

            <!-- With -->
            <div style="background: linear-gradient(135deg, #f0fff4, #e8f5e9); border-left: 4px solid #4CAF50; border-radius: 12px; padding: 30px;">
                <h3 style="color: #4CAF50; font-size: 24px; margin: 0 0 20px 0;">✅ With MunchMakers Custom Line</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 2;">
                    <li>• Capture 100% of accessory sales</li>
                    <li>• 70%+ profit margins per piece</li>
                    <li>• Daily brand visibility at home</li>
                    <li>• Premium positioning justified</li>
                    <li>• Instagram-worthy unboxing</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ROI Calculator -->
<div id="calculator" style="background: linear-gradient(135deg, #f5f5f5, #ffffff); padding: 60px 20px;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 40px;">
            <span style="display: inline-block; background: #8BC34A; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                ROI Calculator
            </span>
            <h2 style="font-size: 36px; font-weight: 800; margin: 15px 0; color: #1a1a1a;">
                Calculate Your Monthly Revenue Potential
            </h2>
            <p style="color: #666; font-size: 18px;">Based on real data from 500+ dispensary partners</p>
        </div>

        <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; text-align: center; margin-bottom: 40px;">
                <div>
                    <label style="color: #999; font-size: 14px; display: block; margin-bottom: 10px;">Daily Customers</label>
                    <div style="font-size: 42px; font-weight: bold; color: #1a1a1a;">200</div>
                </div>
                <div>
                    <label style="color: #999; font-size: 14px; display: block; margin-bottom: 10px;">Attach Rate</label>
                    <div style="font-size: 42px; font-weight: bold; color: #1a1a1a;">15%</div>
                </div>
                <div>
                    <label style="color: #999; font-size: 14px; display: block; margin-bottom: 10px;">Avg Sale</label>
                    <div style="font-size: 42px; font-weight: bold; color: #1a1a1a;">$35</div>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #8BC34A, #7CB342); padding: 30px; border-radius: 12px; text-align: center; color: white;">
                <div style="font-size: 20px; opacity: 0.95; margin-bottom: 10px;">Additional Monthly Revenue</div>
                <div style="font-size: 56px; font-weight: 800;">$31,500</div>
                <div style="font-size: 18px; opacity: 0.95; margin-top: 10px;">
                    At 70% margin = <strong style="font-size: 20px;">$22,050</strong> pure profit
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <img src="{profit_img}" alt="Profit visualization" style="max-width: 500px; width: 100%; height: auto; border-radius: 10px;">
        </div>
    </div>
</div>

<!-- Best Sellers -->
<div style="padding: 60px 20px; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 20px 0; color: #1a1a1a;">
            Best Sellers for Dispensaries
        </h2>
        <p style="text-align: center; color: #666; font-size: 18px; margin: 0 0 50px 0;">
            High-margin products that practically sell themselves
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">

            <!-- Grinder Card -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s;">
                <div style="background: linear-gradient(135deg, #8BC34A, #689F38); padding: 40px; text-align: center; position: relative;">
                    <span style="position: absolute; top: 15px; right: 15px; background: white; color: #8BC34A; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        73% MARGIN
                    </span>
                    <div style="font-size: 64px;">🔥</div>
                    <div style="color: white; font-size: 20px; font-weight: 600;">TOP SELLER</div>
                </div>
                <div style="padding: 30px;">
                    <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Custom 4-Piece Grinder</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                        Perfect checkout upsell. Premium aluminum with laser engraving of your logo.
                    </p>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div>
                            <div style="color: #999; font-size: 14px; text-decoration: line-through;">Retail: $45</div>
                            <div style="color: #8BC34A; font-size: 28px; font-weight: bold;">$12</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #999; font-size: 14px;">Profit/Unit</div>
                            <div style="color: #1a1a1a; font-size: 28px; font-weight: bold;">+$33</div>
                        </div>
                    </div>
                    <a href="/product-category/4-piece-grinders/" style="display: block; background: #1a1a1a; color: white; padding: 15px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        View Options →
                    </a>
                </div>
            </div>

            <!-- Rolling Tray Card -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s;">
                <div style="background: linear-gradient(135deg, #FF6B6B, #C44569); padding: 40px; text-align: center; position: relative;">
                    <span style="position: absolute; top: 15px; right: 15px; background: white; color: #C44569; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        LOYALTY BUILDER
                    </span>
                    <div style="font-size: 64px;">🎁</div>
                    <div style="color: white; font-size: 20px; font-weight: 600;">CUSTOMER FAVORITE</div>
                </div>
                <div style="padding: 30px;">
                    <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Branded Rolling Tray</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                        Daily brand visibility. Perfect for loyalty rewards or first-time gifts.
                    </p>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div>
                            <div style="color: #999; font-size: 14px; text-decoration: line-through;">Retail: $30</div>
                            <div style="color: #C44569; font-size: 28px; font-weight: bold;">$8</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #999; font-size: 14px;">Profit/Unit</div>
                            <div style="color: #1a1a1a; font-size: 28px; font-weight: bold;">+$22</div>
                        </div>
                    </div>
                    <a href="/product-category/custom-rolling-trays/" style="display: block; background: #1a1a1a; color: white; padding: 15px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        View Options →
                    </a>
                </div>
            </div>

            <!-- Lighter Card -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s;">
                <div style="background: linear-gradient(135deg, #667EEA, #764BA2); padding: 40px; text-align: center; position: relative;">
                    <span style="position: absolute; top: 15px; right: 15px; background: white; color: #764BA2; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        NO MINIMUM
                    </span>
                    <div style="font-size: 64px;">⚡</div>
                    <div style="color: white; font-size: 20px; font-weight: 600;">IMPULSE BUY</div>
                </div>
                <div style="padding: 30px;">
                    <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Custom Lighters</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                        Checkout counter champion. Full-wrap custom design on quality lighters.
                    </p>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div>
                            <div style="color: #999; font-size: 14px; text-decoration: line-through;">Retail: $5</div>
                            <div style="color: #764BA2; font-size: 28px; font-weight: bold;">$1.50</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #999; font-size: 14px;">Profit/Unit</div>
                            <div style="color: #1a1a1a; font-size: 28px; font-weight: bold;">+$3.50</div>
                        </div>
                    </div>
                    <a href="/product-category/custom-lighters/" style="display: block; background: #1a1a1a; color: white; padding: 15px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        View Options →
                    </a>
                </div>
            </div>

        </div>

        <div style="text-align: center; margin-top: 50px;">
            <img src="{customer_img}" alt="Happy customer with custom accessories" style="max-width: 700px; width: 100%; height: auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>
    </div>
</div>

<!-- Use Cases -->
<div style="background: #f8f9fa; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            How Smart Dispensaries Use Custom Accessories
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">

            <div style="background: white; padding: 30px; border-radius: 12px; border-top: 4px solid #8BC34A;">
                <h3 style="color: #8BC34A; font-size: 20px; margin: 0 0 15px 0;">First-Timer Welcome Kit</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 15px 0;">
                    Convert first visits into loyal customers with a branded starter pack.
                </p>
                <div style="background: #f0f9f0; padding: 15px; border-radius: 8px;">
                    <strong style="color: #2d7a2d;">67% higher return rate</strong><br>
                    <span style="color: #666; font-size: 14px;">$45 investment → $380 avg 90-day value</span>
                </div>
            </div>

            <div style="background: white; padding: 30px; border-radius: 12px; border-top: 4px solid #FF6B6B;">
                <h3 style="color: #FF6B6B; font-size: 20px; margin: 0 0 15px 0;">Loyalty Points Rewards</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 15px 0;">
                    Premium accessories as redemption options drive program engagement.
                </p>
                <div style="background: #fff5f5; padding: 15px; border-radius: 8px;">
                    <strong style="color: #d44;">3x higher engagement</strong><br>
                    <span style="color: #666; font-size: 14px;">42% increase in program participation</span>
                </div>
            </div>

            <div style="background: white; padding: 30px; border-radius: 12px; border-top: 4px solid #667EEA;">
                <h3 style="color: #667EEA; font-size: 20px; margin: 0 0 15px 0;">Strain-Specific Drops</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 15px 0;">
                    Limited edition accessories for exclusive strains create urgency.
                </p>
                <div style="background: #f0f0ff; padding: 15px; border-radius: 8px;">
                    <strong style="color: #667EEA;">5x faster sellout</strong><br>
                    <span style="color: #666; font-size: 14px;">500 units gone in 48 hours</span>
                </div>
            </div>

            <div style="background: white; padding: 30px; border-radius: 12px; border-top: 4px solid #FFA500;">
                <h3 style="color: #FFA500; font-size: 20px; margin: 0 0 15px 0;">4/20 & Holidays</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 15px 0;">
                    Special editions for peak seasons. Pre-orders cover production costs.
                </p>
                <div style="background: #fff9f0; padding: 15px; border-radius: 8px;">
                    <strong style="color: #FF8C00;">420% revenue boost</strong><br>
                    <span style="color: #666; font-size: 14px;">During 4/20 week alone</span>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Process -->
<div style="padding: 60px 20px; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            From Concept to Shelf in <span style="color: #8BC34A;">5 Business Days</span>
        </h2>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 50px;">

            <div style="flex: 1; text-align: center; min-width: 200px;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin-bottom: 15px;">
                    1
                </div>
                <h4 style="margin: 0 0 10px 0; color: #1a1a1a;">Free Mockup</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">24-hour turnaround</p>
            </div>

            <div style="flex: 1; text-align: center; min-width: 200px;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin-bottom: 15px;">
                    2
                </div>
                <h4 style="margin: 0 0 10px 0; color: #1a1a1a;">Approve & Order</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">No minimums available</p>
            </div>

            <div style="flex: 1; text-align: center; min-width: 200px;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin-bottom: 15px;">
                    3
                </div>
                <h4 style="margin: 0 0 10px 0; color: #1a1a1a;">Production</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">5-day rush available</p>
            </div>

            <div style="flex: 1; text-align: center; min-width: 200px;">
                <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin-bottom: 15px;">
                    4
                </div>
                <h4 style="margin: 0 0 10px 0; color: #1a1a1a;">Start Selling</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">Watch margins grow</p>
            </div>

        </div>

        <div style="background: linear-gradient(135deg, #f0f9f0, #e8f5e9); border-left: 4px solid #8BC34A; padding: 25px; border-radius: 8px; text-align: center;">
            <p style="margin: 0; color: #2d7a2d; font-size: 16px; font-weight: 600;">
                ✓ <strong>100% Compliant:</strong> All products meet state regulations • No cannabis imagery required • Child-resistant options available
            </p>
        </div>
    </div>
</div>

<!-- Testimonials -->
<div style="background: #f8f9fa; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            What Dispensary Owners Are Saying
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">

            <div style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); position: relative;">
                <div style="color: #8BC34A; font-size: 48px; line-height: 0.5; margin-bottom: 20px; font-family: serif;">"</div>
                <p style="color: #666; line-height: 1.8; font-style: italic; margin: 0 0 25px 0;">
                    We added MunchMakers accessories and immediately saw a $15 increase in average transaction. The grinders basically sell themselves - customers see the quality and buy on the spot.
                </p>
                <div>
                    <div style="font-weight: 600; color: #1a1a1a; font-size: 18px;">Sarah Chen</div>
                    <div style="color: #999; font-size: 14px;">The Green Door, Las Vegas</div>
                    <div style="color: #FFD700; font-size: 16px; margin-top: 8px;">★★★★★</div>
                </div>
            </div>

            <div style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); position: relative;">
                <div style="color: #8BC34A; font-size: 48px; line-height: 0.5; margin-bottom: 20px; font-family: serif;">"</div>
                <p style="color: #666; line-height: 1.8; font-style: italic; margin: 0 0 25px 0;">
                    Finally, accessories with margins that make sense. We're making more profit on a $35 grinder than a $60 eighth. Plus, customers actually keep and use them daily.
                </p>
                <div>
                    <div style="font-weight: 600; color: #1a1a1a; font-size: 18px;">Marcus Johnson</div>
                    <div style="color: #999; font-size: 14px;">Elevate Cannabis Co, Denver</div>
                    <div style="color: #FFD700; font-size: 16px; margin-top: 8px;">★★★★★</div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- FAQ -->
<div style="padding: 60px 20px; background: white;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            Questions Dispensary Owners Ask
        </h2>

        <div style="background: #fafafa; border-radius: 12px; padding: 40px;">

            <div style="border-bottom: 1px solid #ddd; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    Do you work with multi-state operators (MSOs)?
                </h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    Yes! We supply MedMen, Cookies, and other major MSOs. We maintain brand consistency across all locations while shipping to individual stores. Special MSO pricing available for orders over 10,000 units.
                </p>
            </div>

            <div style="border-bottom: 1px solid #ddd; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    What's the actual profit margin on accessories?
                </h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    Dispensaries typically see 65-75% margins on accessories vs. 15-25% on flower. A $35 grinder costs you $10-12. It's your highest margin category after pre-rolls.
                </p>
            </div>

            <div style="border-bottom: 1px solid #ddd; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    Can you match my brand guidelines?
                </h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    Absolutely. We match Pantone colors and follow brand guides to the letter. Our design team has worked with 500+ cannabis brands. Send us your assets and we'll create free mockups that look like they came from your in-house team.
                </p>
            </div>

            <div style="border-bottom: 1px solid #ddd; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    What about compliance and regulations?
                </h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    We're fully versed in state regulations. Our products are accessories (not cannabis products) so they don't require warning labels. Child-resistant packaging is available for markets that require it.
                </p>
            </div>

            <div>
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    Do you require minimum orders?
                </h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    No minimums on select items! Start small to test what sells, then order bulk for better margins. Most dispensaries reorder monthly. We can also hold inventory for chains.
                </p>
            </div>

        </div>
    </div>
</div>

<!-- Final CTA -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 80px 20px; text-align: center; color: white; margin: 0 -20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 44px; font-weight: 800; margin: 0 0 20px 0; line-height: 1.1;">
            Join 500+ Dispensaries Already<br>
            <span style="color: #8BC34A;">Maximizing Their Margins</span>
        </h2>

        <p style="font-size: 20px; opacity: 0.95; margin: 0 0 40px 0; line-height: 1.5;">
            Get your free mockup and wholesale pricing in 24 hours.<br>
            See why dispensaries never go back to generic accessories.
        </p>

        <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 40px; flex-wrap: wrap;">
            <div>✓ No Minimums</div>
            <div>✓ 5-Day Rush</div>
            <div>✓ Free Mockups</div>
            <div>✓ Free Shipping</div>
        </div>

        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; padding: 20px 45px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; box-shadow: 0 6px 25px rgba(139,195,74,0.4);">
                Get Your Free Mockup Now
            </a>
            <a href="tel:6506403836" style="display: inline-block; background: transparent; color: white; padding: 20px 45px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; border: 2px solid rgba(255,255,255,0.5);">
                Call (650) 640-3836
            </a>
        </div>

        <p style="margin-top: 40px; opacity: 0.7; font-size: 16px;">
            Questions? Email <a href="mailto:orders@munchmakers.com" style="color: #8BC34A; text-decoration: none;">orders@munchmakers.com</a> or text us anytime
        </p>
    </div>
</div>'''

    # Create the page in BigCommerce (without custom link - will be auto-generated)
    page_data = {
        "type": "page",
        "name": "Custom Cannabis Accessories for Dispensaries",
        "body": html_content,
        "is_visible": True,
        "parent_id": 0,
        "sort_order": 100,
        "meta_description": "Increase dispensary profits by 73% with custom grinders & accessories. No minimums, 5-day production. Join 500+ dispensaries maximizing margins.",
        "search_keywords": "dispensary accessories, custom grinders, cannabis accessories wholesale, dispensary merchandise"
    }

    print("\n" + "="*60)
    print("CREATING DISPENSARY PAGE IN BIGCOMMERCE")
    print("="*60)

    url = f'{api_base_url}/content/pages'

    response = requests.post(url, headers=headers, json=page_data)

    if response.status_code == 201:
        result = response.json()
        page_id = result['data']['id']
        page_url = result['data']['url']

        print(f"\n✓✓✓ PAGE CREATED SUCCESSFULLY! ✓✓✓")
        print(f"\nPage Details:")
        print(f"  • Page ID: {page_id}")
        print(f"  • Public URL: https://www.munchmakers.com{page_url}")
        print(f"  • Edit in BigCommerce: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")

        return result
    else:
        print(f"\n✗ Error creating page: {response.status_code}")
        print(f"Response: {response.text}")
        return None

# Run the deployment
if __name__ == "__main__":
    result = create_dispensary_page()

    if result:
        print("\n" + "="*60)
        print("🎉 DEPLOYMENT COMPLETE!")
        print("="*60)
        print("\nYour dispensary landing page is now live!")
        print("Share this link with dispensary prospects:")
        print(f"https://www.munchmakers.com{result['data']['url']}")
    else:
        print("\n" + "="*60)
        print("❌ DEPLOYMENT FAILED")
        print("="*60)
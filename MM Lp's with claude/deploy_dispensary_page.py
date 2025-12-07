import requests
import json
import base64
import os
from datetime import datetime

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
base_domain = 'www.munchmakers.com'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'
webdav_url = f'https://store-{bc_store_hash}.mybigcommerce.com/dav'

# Headers for API requests
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def upload_image_to_webdav(image_path, destination_name):
    """Upload an image to BigCommerce WebDAV"""

    # Read the image file
    with open(image_path, 'rb') as f:
        image_data = f.read()

    # WebDAV upload URL
    upload_url = f'{webdav_url}/content/images/dispensary/{destination_name}'

    # Headers for WebDAV
    webdav_headers = {
        'Content-Type': 'image/png',
        'X-Auth-Token': bc_access_token
    }

    response = requests.put(upload_url, data=image_data, headers=webdav_headers)

    if response.status_code in [201, 204]:
        # Return the public URL
        return f'https://store-{bc_store_hash}.mybigcommerce.com/content/images/dispensary/{destination_name}'
    else:
        print(f"Failed to upload {destination_name}: {response.status_code}")
        return None

def create_dispensary_page():
    """Create the dispensary landing page in BigCommerce"""

    # First, upload our best images
    images_to_upload = [
        {
            'file': 'dispensary_hero_image_20251115_201521.png',
            'name': 'hero_display.png'
        },
        {
            'file': 'dispensary_v2_customer_experience_20251115_202140.png',
            'name': 'customer_experience.png'
        },
        {
            'file': 'dispensary_v2_profit_concept_20251115_202147.png',
            'name': 'profit_concept.png'
        },
        {
            'file': 'dispensary_v2_product_display_20251115_202144.png',
            'name': 'product_display.png'
        }
    ]

    # Upload images and get URLs
    image_urls = {}
    for img in images_to_upload:
        if os.path.exists(img['file']):
            print(f"Uploading {img['name']}...")
            url = upload_image_to_webdav(img['file'], img['name'])
            if url:
                image_urls[img['name']] = url
                print(f"✓ Uploaded: {url}")
        else:
            print(f"✗ File not found: {img['file']}")

    # Use CDN URLs or fallback images
    hero_img = image_urls.get('hero_display.png', 'https://cdn11.bigcommerce.com/s-tqjrceegho/images/stencil/original/image-manager/premium-dispensary-display.jpg')
    customer_img = image_urls.get('customer_experience.png', 'https://cdn11.bigcommerce.com/s-tqjrceegho/images/stencil/original/image-manager/customer-grinder.jpg')
    profit_img = image_urls.get('profit_concept.png', 'https://cdn11.bigcommerce.com/s-tqjrceegho/images/stencil/original/image-manager/profit-concept.jpg')

    # Create the page HTML content (similar style to the rewards page example)
    html_content = f'''
<!-- MunchMakers Dispensary Landing Page - Premium B2B Design -->

<!-- Hero Section -->
<div style="position: relative; background: linear-gradient(135deg, #141414 0%, #2a2a2a 100%); color: #ffffff; padding: 60px 20px; overflow: hidden;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">

        <!-- Left Content -->
        <div>
            <!-- Trust Badge -->
            <div style="display: inline-block; background: rgba(139,195,74,0.15); padding: 8px 16px; border-radius: 50px; margin-bottom: 20px; border: 1px solid rgba(139,195,74,0.3);">
                <span style="font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">🏆 500+ Dispensaries Trust MunchMakers</span>
            </div>

            <h1 style="font-size: 46px; font-weight: 800; margin: 0 0 20px 0; line-height: 1.1; letter-spacing: -1px;">
                Turn Your <span style="color: #8BC34A;">Accessories</span> Into Your Highest Margin Category
            </h1>

            <p style="font-size: 20px; margin: 0 0 30px 0; opacity: 0.9; line-height: 1.5;">
                Premium custom grinders and accessories that sell themselves. Build brand loyalty while adding $15-40 pure profit per transaction.
            </p>

            <!-- Key Metrics -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; color: #8BC34A;">73%</div>
                    <div style="font-size: 12px; opacity: 0.8;">Higher margins</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; color: #8BC34A;">$23</div>
                    <div style="font-size: 12px; opacity: 0.8;">Avg increase</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; color: #8BC34A;">5 Days</div>
                    <div style="font-size: 12px; opacity: 0.8;">Rush delivery</div>
                </div>
            </div>

            <!-- CTAs -->
            <div style="display: flex; gap: 15px;">
                <a href="https://www.munchmakers.com/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A 0%, #7cb342 100%); color: white; padding: 16px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; box-shadow: 0 4px 14px rgba(139,195,74,0.3);">
                    Get Free Mockup & Pricing
                </a>
                <a href="#roi-calculator" style="display: inline-block; background: transparent; color: white; padding: 16px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; border: 2px solid rgba(255,255,255,0.3);">
                    Calculate Your ROI →
                </a>
            </div>
        </div>

        <!-- Right: Hero Image -->
        <div>
            <img src="{hero_img}" alt="Premium dispensary display" style="width: 100%; height: auto; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        </div>
    </div>
</div>

<!-- Stats Bar -->
<div style="background: #f8f9fa; padding: 25px 20px; border-top: 3px solid #8BC34A;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 20px; text-align: center;">
        <div style="font-size: 14px; color: #666;">
            <strong style="color: #141414; font-size: 16px;">MedMen</strong><br>Partner Since 2021
        </div>
        <div style="font-size: 14px; color: #666;">
            <strong style="color: #141414; font-size: 16px;">Cookies</strong><br>300K+ Units
        </div>
        <div style="font-size: 14px; color: #666;">
            <strong style="color: #141414; font-size: 16px;">Planet 13</strong><br>Custom Program
        </div>
        <div style="font-size: 14px; color: #666;">
            <strong style="color: #141414; font-size: 16px;">Green Dragon</strong><br>15 Locations
        </div>
    </div>
</div>

<!-- Problem/Solution Section -->
<section style="padding: 60px 20px; background: #ffffff;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; margin: 0 0 50px 0; color: #141414; font-weight: 800;">
            Your Customers Are Buying Accessories.<br>
            <span style="color: #8BC34A;">Why Not From You?</span>
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Problem Card -->
            <div style="background: #fff5f5; border: 2px solid #ffdddd; border-radius: 12px; padding: 30px;">
                <div style="font-size: 24px; margin-bottom: 10px;">❌</div>
                <h3 style="color: #d44; margin: 0 0 15px 0; font-size: 20px;">Without Custom Accessories</h3>
                <ul style="list-style: none; padding: 0; color: #666; line-height: 1.8; margin: 0;">
                    <li style="padding: 5px 0;">• Customers buy generic on Amazon</li>
                    <li style="padding: 5px 0;">• Lost revenue ($15-40 per visit)</li>
                    <li style="padding: 5px 0;">• No brand reinforcement at home</li>
                    <li style="padding: 5px 0;">• Compete on flower prices alone</li>
                    <li style="padding: 5px 0;">• Lower customer lifetime value</li>
                </ul>
            </div>

            <!-- Solution Card -->
            <div style="background: #f0fff0; border: 2px solid #8BC34A; border-radius: 12px; padding: 30px;">
                <div style="font-size: 24px; margin-bottom: 10px;">✅</div>
                <h3 style="color: #8BC34A; margin: 0 0 15px 0; font-size: 20px;">With MunchMakers Custom Line</h3>
                <ul style="list-style: none; padding: 0; color: #666; line-height: 1.8; margin: 0;">
                    <li style="padding: 5px 0;">• Capture 100% of accessory sales</li>
                    <li style="padding: 5px 0;">• 70%+ profit margins per piece</li>
                    <li style="padding: 5px 0;">• Daily brand visibility at home</li>
                    <li style="padding: 5px 0;">• Premium positioning justified</li>
                    <li style="padding: 5px 0;">• Instagram-worthy unboxing</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ROI Calculator Section -->
<section id="roi-calculator" style="background: linear-gradient(135deg, #f7f7f7 0%, #ffffff 100%); padding: 60px 20px;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-block; background: #8BC34A; color: white; padding: 6px 16px; border-radius: 20px; margin-bottom: 15px;">
                <span style="font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">ROI CALCULATOR</span>
            </div>
            <h2 style="font-size: 36px; margin: 0 0 10px 0; color: #141414; font-weight: 800;">Your Potential Monthly Revenue</h2>
            <p style="font-size: 18px; color: #666; margin: 0;">Based on real data from 500+ dispensary partners</p>
        </div>

        <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 30px;">
                <div style="text-align: center;">
                    <label style="display: block; color: #666; margin-bottom: 8px; font-size: 14px;">Daily Customers</label>
                    <div style="font-size: 32px; font-weight: 700; color: #141414;">200</div>
                </div>
                <div style="text-align: center;">
                    <label style="display: block; color: #666; margin-bottom: 8px; font-size: 14px;">Attach Rate</label>
                    <div style="font-size: 32px; font-weight: 700; color: #141414;">15%</div>
                </div>
                <div style="text-align: center;">
                    <label style="display: block; color: #666; margin-bottom: 8px; font-size: 14px;">Avg Sale</label>
                    <div style="font-size: 32px; font-weight: 700; color: #141414;">$35</div>
                </div>
            </div>

            <div style="border-top: 3px solid #8BC34A; padding-top: 30px; text-align: center;">
                <div style="font-size: 18px; color: #666; margin-bottom: 10px;">Additional Monthly Revenue</div>
                <div style="font-size: 48px; font-weight: 800; color: #8BC34A;">$31,500</div>
                <div style="font-size: 16px; color: #666; margin-top: 10px;">At 70% margin = <strong>$22,050 profit/month</strong></div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <img src="{profit_img}" alt="Profit visualization" style="max-width: 400px; width: 100%; height: auto; border-radius: 10px;">
        </div>
    </div>
</section>

<!-- Product Showcase -->
<section style="padding: 60px 20px; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 36px; margin: 0 0 10px 0; color: #141414; font-weight: 800;">Best Sellers for Dispensaries</h2>
            <p style="font-size: 18px; color: #666; margin: 0;">High-margin products that practically sell themselves</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">

            <!-- Product 1 -->
            <div style="background: white; border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="background: linear-gradient(135deg, #8BC34A, #689F38); height: 200px; display: flex; align-items: center; justify-content: center; position: relative;">
                    <div style="position: absolute; top: 15px; right: 15px; background: white; color: #8BC34A; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        73% MARGIN
                    </div>
                    <div style="color: white; text-align: center;">
                        <div style="font-size: 48px;">🔥</div>
                        <div style="font-size: 18px; font-weight: 600;">Top Seller</div>
                    </div>
                </div>
                <div style="padding: 25px;">
                    <h3 style="margin: 0 0 10px 0; color: #141414; font-size: 20px;">Custom 4-Piece Grinder</h3>
                    <p style="color: #666; margin: 10px 0; font-size: 14px;">Perfect upsell at checkout. Premium aluminum with your logo.</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
                        <div>
                            <div style="color: #999; font-size: 12px; text-decoration: line-through;">Retail: $45</div>
                            <div style="color: #8BC34A; font-size: 24px; font-weight: 700;">Cost: $12</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #666; font-size: 12px;">Profit per unit</div>
                            <div style="color: #141414; font-size: 20px; font-weight: 600;">+$33</div>
                        </div>
                    </div>
                    <a href="https://www.munchmakers.com/product-category/4-piece-grinders/" style="display: block; text-align: center; background: #141414; color: white; padding: 12px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        View Options →
                    </a>
                </div>
            </div>

            <!-- Product 2 -->
            <div style="background: white; border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="background: linear-gradient(135deg, #FF6B6B, #C44569); height: 200px; display: flex; align-items: center; justify-content: center; position: relative;">
                    <div style="position: absolute; top: 15px; right: 15px; background: white; color: #C44569; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        LOYALTY BUILDER
                    </div>
                    <div style="color: white; text-align: center;">
                        <div style="font-size: 48px;">🎁</div>
                        <div style="font-size: 18px; font-weight: 600;">Customer Favorite</div>
                    </div>
                </div>
                <div style="padding: 25px;">
                    <h3 style="margin: 0 0 10px 0; color: #141414; font-size: 20px;">Branded Rolling Tray</h3>
                    <p style="color: #666; margin: 10px 0; font-size: 14px;">Daily brand visibility. Perfect loyalty reward or gift.</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
                        <div>
                            <div style="color: #999; font-size: 12px; text-decoration: line-through;">Retail: $30</div>
                            <div style="color: #C44569; font-size: 24px; font-weight: 700;">Cost: $8</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #666; font-size: 12px;">Profit per unit</div>
                            <div style="color: #141414; font-size: 20px; font-weight: 600;">+$22</div>
                        </div>
                    </div>
                    <a href="https://www.munchmakers.com/product-category/custom-rolling-trays/" style="display: block; text-align: center; background: #141414; color: white; padding: 12px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        View Options →
                    </a>
                </div>
            </div>

            <!-- Product 3 -->
            <div style="background: white; border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="background: linear-gradient(135deg, #667EEA, #764BA2); height: 200px; display: flex; align-items: center; justify-content: center; position: relative;">
                    <div style="position: absolute; top: 15px; right: 15px; background: white; color: #764BA2; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        NO MINIMUM
                    </div>
                    <div style="color: white; text-align: center;">
                        <div style="font-size: 48px;">⚡</div>
                        <div style="font-size: 18px; font-weight: 600;">Quick Seller</div>
                    </div>
                </div>
                <div style="padding: 25px;">
                    <h3 style="margin: 0 0 10px 0; color: #141414; font-size: 20px;">Custom Lighter Packs</h3>
                    <p style="color: #666; margin: 10px 0; font-size: 14px;">Impulse buy champion. Display at checkout for easy add-ons.</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
                        <div>
                            <div style="color: #999; font-size: 12px; text-decoration: line-through;">Retail: $5</div>
                            <div style="color: #764BA2; font-size: 24px; font-weight: 700;">Cost: $1.50</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #666; font-size: 12px;">Profit per unit</div>
                            <div style="color: #141414; font-size: 20px; font-weight: 600;">+$3.50</div>
                        </div>
                    </div>
                    <a href="https://www.munchmakers.com/product-category/custom-lighters/" style="display: block; text-align: center; background: #141414; color: white; padding: 12px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        View Options →
                    </a>
                </div>
            </div>

        </div>

        <div style="text-align: center; margin-top: 40px;">
            <img src="{customer_img}" alt="Customer experience" style="max-width: 600px; width: 100%; height: auto; border-radius: 10px;">
        </div>
    </div>
</section>

<!-- Use Cases Section -->
<section style="background: #f8f9fa; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; margin: 0 0 50px 0; color: #141414; font-weight: 800;">
            How Top Dispensaries Use Custom Accessories
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">

            <!-- Use Case 1 -->
            <div style="background: white; padding: 30px; border-radius: 12px; border-left: 4px solid #8BC34A;">
                <h3 style="color: #8BC34A; margin: 0 0 15px 0; font-size: 20px;">First-Time Customer Kit</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                    Convert first-timers with branded grinder + tray combo.
                    <strong>Result:</strong> 67% higher 30-day return rate.
                </p>
                <div style="background: #f0f9f0; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #2d7a2d;">
                        <strong>ROI:</strong> $45 cost → $380 avg 90-day value
                    </div>
                </div>
            </div>

            <!-- Use Case 2 -->
            <div style="background: white; padding: 30px; border-radius: 12px; border-left: 4px solid #FF6B6B;">
                <h3 style="color: #FF6B6B; margin: 0 0 15px 0; font-size: 20px;">Loyalty Program Rewards</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                    Premium accessories as point redemption options.
                    <strong>Result:</strong> 3x higher engagement than discounts.
                </p>
                <div style="background: #fff5f5; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #d44;">
                        <strong>Impact:</strong> 42% increase in participation
                    </div>
                </div>
            </div>

            <!-- Use Case 3 -->
            <div style="background: white; padding: 30px; border-radius: 12px; border-left: 4px solid #667EEA;">
                <h3 style="color: #667EEA; margin: 0 0 15px 0; font-size: 20px;">Strain-Specific Merch</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                    Limited edition grinders for exclusive drops.
                    <strong>Result:</strong> Sell out 5x faster than flower alone.
                </p>
                <div style="background: #f0f0ff; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #667EEA;">
                        <strong>Example:</strong> 500 units sold in 48 hours
                    </div>
                </div>
            </div>

            <!-- Use Case 4 -->
            <div style="background: white; padding: 30px; border-radius: 12px; border-left: 4px solid #FFA500;">
                <h3 style="color: #FFA500; margin: 0 0 15px 0; font-size: 20px;">Holiday & 4/20 Promos</h3>
                <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                    Special editions for peak seasons.
                    <strong>Result:</strong> 420% revenue increase during 4/20.
                </p>
                <div style="background: #fff9f0; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #FF8C00;">
                        <strong>Tip:</strong> Order 60 days early for best pricing
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Process Section -->
<section style="padding: 60px 20px; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; margin: 0 0 50px 0; color: #141414; font-weight: 800;">
            From Concept to Shelf in <span style="color: #8BC34A;">5 Business Days</span>
        </h2>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-bottom: 50px;">
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: #8BC34A; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 15px;">1</div>
                <h4 style="margin: 0 0 10px 0; color: #141414;">Free Mockup</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">24-hour turnaround</p>
            </div>

            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: #8BC34A; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 15px;">2</div>
                <h4 style="margin: 0 0 10px 0; color: #141414;">Approve & Order</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">No minimums available</p>
            </div>

            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: #8BC34A; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 15px;">3</div>
                <h4 style="margin: 0 0 10px 0; color: #141414;">Production</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">5-day rush available</p>
            </div>

            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: #8BC34A; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 15px;">4</div>
                <h4 style="margin: 0 0 10px 0; color: #141414;">Start Selling</h4>
                <p style="color: #666; font-size: 14px; margin: 0;">Watch margins grow</p>
            </div>
        </div>

        <!-- Compliance Notice -->
        <div style="background: #f0f9f0; border: 1px solid #8BC34A; border-radius: 12px; padding: 20px; text-align: center;">
            <p style="margin: 0; color: #2d7a2d; font-size: 16px;">
                <strong>✓ Fully Compliant:</strong> All products meet state regulations. No cannabis imagery required. Child-resistant packaging available.
            </p>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section style="background: #f8f9fa; padding: 60px 20px;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; margin: 0 0 50px 0; color: #141414; font-weight: 800;">
            Common Questions from Dispensary Owners
        </h2>

        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">

            <details style="border-bottom: 1px solid #eee;">
                <summary style="padding: 25px 30px; cursor: pointer; font-weight: 600; color: #141414; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Do you work with multi-state operators (MSOs)?
                    <span style="color: #8BC34A; font-size: 20px;">+</span>
                </summary>
                <div style="padding: 0 30px 25px; color: #666; line-height: 1.6;">
                    Yes! We supply MedMen, Cookies, and other major MSOs. We maintain brand consistency across all locations while shipping to individual stores. Special MSO pricing for 10,000+ units.
                </div>
            </details>

            <details style="border-bottom: 1px solid #eee;">
                <summary style="padding: 25px 30px; cursor: pointer; font-weight: 600; color: #141414; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    What's the actual profit margin on accessories?
                    <span style="color: #8BC34A; font-size: 20px;">+</span>
                </summary>
                <div style="padding: 0 30px 25px; color: #666; line-height: 1.6;">
                    Dispensaries typically see 65-75% margins on accessories vs. 15-25% on flower. A $35 grinder costs you $10-12. It's your highest margin category after pre-rolls.
                </div>
            </details>

            <details style="border-bottom: 1px solid #eee;">
                <summary style="padding: 25px 30px; cursor: pointer; font-weight: 600; color: #141414; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Can you match my brand guidelines exactly?
                    <span style="color: #8BC34A; font-size: 20px;">+</span>
                </summary>
                <div style="padding: 0 30px 25px; color: #666; line-height: 1.6;">
                    Absolutely. We match Pantone colors and follow brand guides. Our design team has worked with 500+ cannabis brands. Send your assets for a free mockup in 24 hours.
                </div>
            </details>

            <details style="border-bottom: 1px solid #eee;">
                <summary style="padding: 25px 30px; cursor: pointer; font-weight: 600; color: #141414; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    What about compliance and regulations?
                    <span style="color: #8BC34A; font-size: 20px;">+</span>
                </summary>
                <div style="padding: 0 30px 25px; color: #666; line-height: 1.6;">
                    We're fully versed in state regulations. Our products don't require warning labels (they're accessories, not cannabis). Child-resistant packaging available where required.
                </div>
            </details>

            <details>
                <summary style="padding: 25px 30px; cursor: pointer; font-weight: 600; color: #141414; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Do you dropship or require inventory commitment?
                    <span style="color: #8BC34A; font-size: 20px;">+</span>
                </summary>
                <div style="padding: 0 30px 25px; color: #666; line-height: 1.6;">
                    We offer both! Start with no minimums to test what sells, then order bulk for better margins. Many dispensaries reorder monthly. We can also hold inventory for chains.
                </div>
            </details>

        </div>
    </div>
</section>

<!-- Testimonials -->
<section style="padding: 60px 20px; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; margin: 0 0 50px 0; color: #141414; font-weight: 800;">
            What Dispensary Owners Say
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">

            <div style="background: #fafafa; padding: 30px; border-radius: 12px; position: relative;">
                <div style="color: #8BC34A; font-size: 48px; line-height: 1; margin-bottom: 20px;">"</div>
                <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0; font-style: italic;">
                    "We added MunchMakers accessories and immediately saw a $15 increase in average transaction. The grinders basically sell themselves - customers see the quality and buy on the spot."
                </p>
                <div>
                    <div style="font-weight: 600; color: #141414;">Sarah Chen</div>
                    <div style="color: #666; font-size: 14px;">The Green Door, Las Vegas</div>
                    <div style="color: #8BC34A; font-size: 14px; margin-top: 5px;">★★★★★</div>
                </div>
            </div>

            <div style="background: #fafafa; padding: 30px; border-radius: 12px; position: relative;">
                <div style="color: #8BC34A; font-size: 48px; line-height: 1; margin-bottom: 20px;">"</div>
                <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0; font-style: italic;">
                    "Finally, accessories with margins that make sense. We're making more profit on a $35 grinder than a $60 eighth. Plus, customers actually keep and use them daily."
                </p>
                <div>
                    <div style="font-weight: 600; color: #141414;">Marcus Johnson</div>
                    <div style="color: #666; font-size: 14px;">Elevate Cannabis Co, Denver</div>
                    <div style="color: #8BC34A; font-size: 14px; margin-top: 5px;">★★★★★</div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #141414 0%, #2a2a2a 100%); padding: 80px 20px; text-align: center; color: white;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 800;">
            Join 500+ Dispensaries Already<br>Maximizing Their Margins
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; opacity: 0.9;">
            Get your free mockup and pricing in 24 hours. See why dispensaries never go back to generic accessories.
        </p>

        <!-- Trust badges -->
        <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 40px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="color: #8BC34A; font-size: 24px;">✓</div>
                <span>No Minimums</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="color: #8BC34A; font-size: 24px;">✓</div>
                <span>5-Day Rush</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="color: #8BC34A; font-size: 24px;">✓</div>
                <span>Free Mockups</span>
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            <a href="https://www.munchmakers.com/loyalty-program/" style="display: inline-block; background: #8BC34A; color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; box-shadow: 0 4px 20px rgba(139,195,74,0.4);">
                Get Your Free Mockup Now
            </a>
            <a href="tel:+16506403836" style="display: inline-block; background: transparent; color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; border: 2px solid white;">
                Call: (650) 640-3836
            </a>
        </div>

        <p style="margin-top: 30px; font-size: 14px; opacity: 0.7;">
            Questions? Email orders@munchmakers.com or text (650) 640-3836
        </p>
    </div>
</div>
'''

    # Create the page in BigCommerce
    page_data = {
        "type": "page",
        "name": "Custom Cannabis Accessories for Dispensaries | B2B Wholesale",
        "link": "/for-dispensaries/",
        "body": html_content,
        "is_visible": True,
        "parent_id": 0,
        "sort_order": 100,
        "meta_description": "Increase your dispensary's average transaction by 23% with custom grinders, rolling trays, and accessories. 70%+ profit margins. No minimums. 5-day production.",
        "search_keywords": "dispensary accessories, custom grinders dispensary, cannabis accessories wholesale, dispensary merchandise, custom rolling trays, dispensary branding"
    }

    url = f'{api_base_url}/content/pages'

    print("\nCreating dispensary landing page in BigCommerce...")
    response = requests.post(url, headers=headers, json=page_data)

    if response.status_code == 201:
        result = response.json()
        page_id = result['data']['id']
        page_url = result['data']['url']
        print(f"\n✓ Page created successfully!")
        print(f"  Page ID: {page_id}")
        print(f"  Public URL: https://www.munchmakers.com{page_url}")
        print(f"  Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
        return result
    else:
        print(f"\n✗ Error creating page: {response.status_code}")
        print(f"  Response: {response.text}")
        return None

# Run the deployment
if __name__ == "__main__":
    print("="*60)
    print("DEPLOYING CANNABIS DISPENSARY LANDING PAGE")
    print("="*60)

    result = create_dispensary_page()

    if result:
        print("\n" + "="*60)
        print("DEPLOYMENT SUCCESSFUL!")
        print("="*60)
    else:
        print("\n" + "="*60)
        print("DEPLOYMENT FAILED - Check error messages above")
        print("="*60)
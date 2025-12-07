import requests
import json
import base64
import time
from datetime import datetime
from requests.auth import HTTPDigestAuth

# API credentials
FREEPIK_API_KEY = 'FPSX381b01bdceb04b9fa3c51f52816cfacd'
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
webdav_username = 'billing@greenlunar.com'
webdav_password = 'a81686b5cc9da9afcf1fb528e86e5349'
webdav_base = 'https://store-tqjrceegho.mybigcommerce.com/dav'

# Setup
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'
auth = HTTPDigestAuth(webdav_username, webdav_password)
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

print("="*60)
print("CREATING CANNABIS CULTIVATORS LANDING PAGE")
print("="*60)

# Step 1: Generate cultivator-specific images
def generate_image(prompt, name):
    """Generate image with Freepik API"""
    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers_freepik = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json"
    }

    data = {
        "prompt": prompt,
        "negative_prompt": "cartoon, illustration, low quality, blurry, text, logos, watermarks",
        "guidance_scale": 7.5,
        "seed": None,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        }
    }

    print(f"\nGenerating {name}...")
    response = requests.post(url, headers=headers_freepik, json=data)

    if response.status_code == 200:
        result = response.json()
        if 'data' in result and len(result['data']) > 0:
            base64_data = result['data'][0].get('base64')
            if base64_data:
                # Save locally
                filename = f"cultivator_{name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
                with open(filename, 'wb') as f:
                    f.write(base64.b64decode(base64_data))
                print(f"✓ Generated {filename}")
                return base64_data, filename

    print(f"✗ Failed to generate {name}")
    return None, None

# Step 2: Upload to WebDAV
def upload_to_webdav(local_file, remote_name):
    """Upload image to BigCommerce WebDAV"""
    if not local_file:
        return None

    with open(local_file, 'rb') as f:
        image_data = f.read()

    upload_url = f'{webdav_base}/product_images/{remote_name}'
    response = requests.put(upload_url, data=image_data, auth=auth, headers={'Content-Type': 'image/png'})

    if response.status_code in [200, 201, 204]:
        url = f"https://store-{bc_store_hash}.mybigcommerce.com/product_images/{remote_name}"
        print(f"✓ Uploaded to: {url}")
        return url

    return None

# Generate cultivator-specific images
print("\n" + "-"*60)
print("GENERATING CULTIVATOR IMAGES")
print("-"*60)

cultivator_images = [
    {
        "name": "hero",
        "remote": "cultivator_hero.png",
        "prompt": "Professional cannabis cultivation facility with rows of healthy green plants, modern grow lights, cultivator examining plants with care, clean professional environment, depth of field, commercial photography"
    },
    {
        "name": "craft_grinder",
        "remote": "cultivator_craft.png",
        "prompt": "Premium black aluminum herb grinder with custom engraving showing strain name, sitting on rustic wood surface with cannabis leaves artfully arranged, macro photography, professional product shot, warm lighting"
    },
    {
        "name": "brand_experience",
        "remote": "cultivator_brand.png",
        "prompt": "Hands opening premium white gift box revealing custom black grinder and rolling tray with brand logo, unboxing experience, lifestyle photography, bright clean background, professional commercial photo"
    },
    {
        "name": "harvest",
        "remote": "cultivator_harvest.png",
        "prompt": "Cannabis cultivator in professional grow facility holding harvested plants, proud farmer portrait, golden hour lighting, bokeh background of grow operation, documentary style photography"
    }
]

image_urls = {}
for img in cultivator_images:
    base64_data, local_file = generate_image(img["prompt"], img["name"])
    if local_file:
        url = upload_to_webdav(local_file, img["remote"])
        if url:
            image_urls[img["name"]] = url
    time.sleep(3)  # Rate limit

# Use uploaded images or fallbacks
hero_img = image_urls.get('hero', 'https://via.placeholder.com/1200x600/2d5016/8BC34A?text=Cannabis+Cultivation')
craft_img = image_urls.get('craft_grinder', 'https://via.placeholder.com/800x400/1a1a1a/8BC34A?text=Craft+Grinder')
brand_img = image_urls.get('brand_experience', 'https://via.placeholder.com/800x400/ffffff/8BC34A?text=Brand+Experience')
harvest_img = image_urls.get('harvest', 'https://via.placeholder.com/800x400/8BC34A/ffffff?text=Premium+Harvest')

# Step 3: Create the HTML content
print("\n" + "-"*60)
print("CREATING PAGE CONTENT")
print("-"*60)

html_content = f'''<!-- MunchMakers Cannabis Cultivators Landing Page -->

<!-- Hero Section - Cultivator Focused -->
<div style="background: linear-gradient(135deg, #2d5016 0%, #3a6218 100%); color: white; padding: 70px 20px; margin: -20px -20px 0 -20px; position: relative; overflow: hidden;">
    <div style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;">

        <!-- Trust Badge -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="display: inline-block; background: rgba(255,255,255,0.15); padding: 8px 20px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.3); font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">
                🌿 Trusted by 200+ Craft Cannabis Cultivators
            </span>
        </div>

        <!-- Main Message -->
        <h1 style="font-size: 48px; font-weight: 800; text-align: center; margin: 0 0 20px 0; line-height: 1.1;">
            Your Strains Deserve to be<br>
            <span style="color: #8BC34A; text-shadow: 0 0 30px rgba(139,195,74,0.5);">Remembered by Name</span>
        </h1>

        <p style="font-size: 22px; text-align: center; margin: 0 auto 40px; max-width: 800px; opacity: 0.95; line-height: 1.5;">
            Build lasting brand recognition with custom accessories that travel with your premium flower.
            Make every customer a brand ambassador.
        </p>

        <!-- Value Props -->
        <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 40px; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #8BC34A;">87%</div>
                <div style="font-size: 14px; opacity: 0.9;">Keep Your Grinder</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #8BC34A;">365</div>
                <div style="font-size: 14px; opacity: 0.9;">Days of Visibility</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #8BC34A;">3x</div>
                <div style="font-size: 14px; opacity: 0.9;">Brand Recall</div>
            </div>
        </div>

        <!-- CTAs -->
        <div style="text-align: center;">
            <a href="/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 20px rgba(139,195,74,0.4); margin: 0 10px;">
                Get Your Strain Mockup
            </a>
            <a href="#showcase" style="display: inline-block; background: transparent; color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; border: 2px solid rgba(255,255,255,0.5); margin: 0 10px;">
                See Examples →
            </a>
        </div>
    </div>
</div>

<!-- Hero Image -->
<div style="background: #f8f9fa; padding: 50px 20px; margin: 0 -20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <img src="{hero_img}" alt="Cannabis cultivation facility" style="width: 100%; max-width: 900px; height: auto; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    </div>
</div>

<!-- The Problem Section -->
<div style="padding: 70px 20px; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 40px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            You Grow Fire. But After It's Smoked,<br>
            <span style="color: #8BC34A;">How Do They Remember You?</span>
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">

            <!-- Without Branded Accessories -->
            <div style="background: linear-gradient(135deg, #fff5f5, #ffeeee); border-left: 4px solid #ff4444; border-radius: 12px; padding: 35px;">
                <h3 style="color: #ff4444; font-size: 24px; margin: 0 0 25px 0;">😔 The Current Reality</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 2.2;">
                    <li style="padding: 5px 0;">• "What was that strain I loved?"</li>
                    <li style="padding: 5px 0;">• Your brand disappears after purchase</li>
                    <li style="padding: 5px 0;">• Dispensaries get the credit</li>
                    <li style="padding: 5px 0;">• No direct consumer connection</li>
                    <li style="padding: 5px 0;">• Premium flower becomes commodity</li>
                </ul>
            </div>

            <!-- With MunchMakers Branding -->
            <div style="background: linear-gradient(135deg, #f0fff4, #e8f5e9); border-left: 4px solid #4CAF50; border-radius: 12px; padding: 35px;">
                <h3 style="color: #4CAF50; font-size: 24px; margin: 0 0 25px 0;">🌟 With Your Brand Everywhere</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 2.2;">
                    <li style="padding: 5px 0;">• Daily brand visibility at home</li>
                    <li style="padding: 5px 0;">• "Check out my XYZ Farms grinder!"</li>
                    <li style="padding: 5px 0;">• Social media tags & shares</li>
                    <li style="padding: 5px 0;">• Customers seek your strains</li>
                    <li style="padding: 5px 0;">• Build a loyal following</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<!-- Showcase Section -->
<div id="showcase" style="background: #f8f9fa; padding: 70px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <span style="display: inline-block; background: #8BC34A; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                Product Showcase
            </span>
            <h2 style="font-size: 36px; font-weight: 800; margin: 0 0 15px 0; color: #1a1a1a;">
                Premium Accessories That Match Your Craft Quality
            </h2>
            <p style="color: #666; font-size: 18px; max-width: 700px; margin: 0 auto;">
                Every piece is designed to reflect the care and quality you put into your cultivation
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; margin-bottom: 50px;">
            <div>
                <h3 style="font-size: 28px; color: #2d5016; margin: 0 0 20px 0;">Strain-Specific Grinders</h3>
                <p style="color: #666; line-height: 1.8; font-size: 17px; margin-bottom: 25px;">
                    Imagine customers grinding your Blue Dream in a grinder that says "Blue Dream by Your Farm"
                    with your story, terpene profile, and QR code to find more of your products.
                    That's brand power that lasts.
                </p>
                <ul style="list-style: none; padding: 0; color: #2d5016; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Laser engraved strain details</li>
                    <li style="padding: 8px 0;">✓ QR codes for strain education</li>
                    <li style="padding: 8px 0;">✓ Your cultivation story</li>
                    <li style="padding: 8px 0;">✓ Terpene profiles & effects</li>
                </ul>
            </div>
            <div>
                <img src="{craft_img}" alt="Strain-specific custom grinder" style="width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
            <div style="order: 2;">
                <h3 style="font-size: 28px; color: #2d5016; margin: 0 0 20px 0;">Harvest Collection Boxes</h3>
                <p style="color: #666; line-height: 1.8; font-size: 17px; margin-bottom: 25px;">
                    Create an unboxing experience worthy of your premium flower.
                    Limited edition accessories for special harvests turn customers into collectors
                    who seek out every drop.
                </p>
                <ul style="list-style: none; padding: 0; color: #2d5016; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Numbered limited editions</li>
                    <li style="padding: 8px 0;">✓ Harvest date & batch info</li>
                    <li style="padding: 8px 0;">✓ Collectible designs</li>
                    <li style="padding: 8px 0;">✓ Premium presentation</li>
                </ul>
            </div>
            <div style="order: 1;">
                <img src="{brand_img}" alt="Premium unboxing experience" style="width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
</div>

<!-- Use Cases for Cultivators -->
<div style="padding: 70px 20px; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            How Successful Cultivators Build Their Brand
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">

            <div style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border-top: 4px solid #8BC34A;">
                <h3 style="color: #8BC34A; font-size: 22px; margin: 0 0 15px 0;">Flagship Strain Launch</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Launch new strains with matching accessories. First 100 customers get the limited edition grinder.
                </p>
                <div style="background: #f0f9f0; padding: 15px; border-radius: 8px;">
                    <strong style="color: #2d7a2d;">Results:</strong><br>
                    <span style="color: #666; font-size: 14px;">Sells out 3x faster • Creates buzz • Instagram tags explode</span>
                </div>
            </div>

            <div style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border-top: 4px solid #FF9800;">
                <h3 style="color: #FF9800; font-size: 22px; margin: 0 0 15px 0;">Dispensary Partnerships</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Provide dispensaries with your branded accessories as purchase incentives for your flower.
                </p>
                <div style="background: #fff9f0; padding: 15px; border-radius: 8px;">
                    <strong style="color: #FF6B00;">Impact:</strong><br>
                    <span style="color: #666; font-size: 14px;">40% increase in reorders • Dispensaries push your brand</span>
                </div>
            </div>

            <div style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border-top: 4px solid #9C27B0;">
                <h3 style="color: #9C27B0; font-size: 22px; margin: 0 0 15px 0;">Direct-to-Consumer</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Build email list by offering branded grinders. Create direct relationships within legal limits.
                </p>
                <div style="background: #faf0ff; padding: 15px; border-radius: 8px;">
                    <strong style="color: #7B1FA2;">Growth:</strong><br>
                    <span style="color: #666; font-size: 14px;">2,000+ emails in 3 months • Direct feedback channel</span>
                </div>
            </div>

            <div style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border-top: 4px solid #00BCD4;">
                <h3 style="color: #00BCD4; font-size: 22px; margin: 0 0 15px 0;">Competition Wins</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Cannabis cup winner? Commemorate with special edition accessories. Turn awards into sales.
                </p>
                <div style="background: #f0feff; padding: 15px; border-radius: 8px;">
                    <strong style="color: #0097A7;">Revenue:</strong><br>
                    <span style="color: #666; font-size: 14px;">$50K in accessory sales from one cup win</span>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Cultivator Benefits Section -->
<div style="background: linear-gradient(135deg, #2d5016 0%, #3a6218 100%); color: white; padding: 70px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0;">
            Why Cultivators Choose MunchMakers
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">🎯</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">No Minimums Available</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Test the market with small batches. Perfect for limited harvests and special releases.
                </p>
            </div>

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">🎨</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">Full Customization</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Your story, your way. Include strain info, QR codes, terpene profiles, harvest dates.
                </p>
            </div>

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">⚡</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">5-Day Production</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Got a harvest dropping next week? We've got you covered with rush production.
                </p>
            </div>

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">💎</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">Premium Quality</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Aircraft-grade aluminum, laser engraving. Quality that matches your flower.
                </p>
            </div>

        </div>
    </div>
</div>

<!-- Pricing Section -->
<div style="padding: 70px 20px; background: #f8f9fa;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 36px; font-weight: 800; margin: 0 0 15px 0; color: #1a1a1a;">
                Investment That Grows Your Brand
            </h2>
            <p style="color: #666; font-size: 18px;">
                Pricing that makes sense for craft cultivators
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">

            <!-- Starter Package -->
            <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-align: center;">
                <h3 style="color: #666; font-size: 18px; margin: 0 0 20px 0; text-transform: uppercase; letter-spacing: 1px;">Starter</h3>
                <div style="font-size: 48px; font-weight: 800; color: #2d5016; margin-bottom: 20px;">$8-12</div>
                <div style="color: #666; font-size: 16px; margin-bottom: 30px;">per grinder</div>
                <ul style="list-style: none; padding: 0; margin: 0 0 30px 0; text-align: left; color: #666; line-height: 2;">
                    <li>✓ No minimum order</li>
                    <li>✓ Basic customization</li>
                    <li>✓ Your logo engraved</li>
                    <li>✓ 10-day production</li>
                </ul>
                <a href="/loyalty-program/" style="display: block; background: #f8f9fa; color: #2d5016; padding: 15px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Start Small →
                </a>
            </div>

            <!-- Growth Package -->
            <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 10px 30px rgba(139,195,74,0.15); text-align: center; border: 2px solid #8BC34A; transform: scale(1.05);">
                <span style="display: inline-block; background: #8BC34A; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-bottom: 15px;">MOST POPULAR</span>
                <h3 style="color: #8BC34A; font-size: 18px; margin: 0 0 20px 0; text-transform: uppercase; letter-spacing: 1px;">Growth</h3>
                <div style="font-size: 48px; font-weight: 800; color: #2d5016; margin-bottom: 20px;">$6-10</div>
                <div style="color: #666; font-size: 16px; margin-bottom: 30px;">per grinder (100+ units)</div>
                <ul style="list-style: none; padding: 0; margin: 0 0 30px 0; text-align: left; color: #666; line-height: 2;">
                    <li>✓ Full customization</li>
                    <li>✓ Multiple designs</li>
                    <li>✓ QR codes included</li>
                    <li>✓ 5-day rush available</li>
                </ul>
                <a href="/loyalty-program/" style="display: block; background: #8BC34A; color: white; padding: 15px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Get Quote →
                </a>
            </div>

            <!-- Scale Package -->
            <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-align: center;">
                <h3 style="color: #666; font-size: 18px; margin: 0 0 20px 0; text-transform: uppercase; letter-spacing: 1px;">Scale</h3>
                <div style="font-size: 48px; font-weight: 800; color: #2d5016; margin-bottom: 20px;">$4-8</div>
                <div style="color: #666; font-size: 16px; margin-bottom: 30px;">per grinder (500+ units)</div>
                <ul style="list-style: none; padding: 0; margin: 0 0 30px 0; text-align: left; color: #666; line-height: 2;">
                    <li>✓ Best pricing</li>
                    <li>✓ Priority production</li>
                    <li>✓ Free design services</li>
                    <li>✓ Inventory holding</li>
                </ul>
                <a href="/loyalty-program/" style="display: block; background: #f8f9fa; color: #2d5016; padding: 15px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Let's Talk →
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Success Story -->
<div style="padding: 70px 20px; background: white;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #f0f9f0, #e8f5e9); border-radius: 12px; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="{harvest_img}" alt="Successful cultivator" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid white;">
            </div>

            <div style="font-size: 48px; color: #8BC34A; text-align: center; line-height: 1; margin-bottom: 20px;">"</div>

            <p style="font-size: 20px; line-height: 1.8; color: #2d5016; text-align: center; font-style: italic; margin: 0 0 30px 0;">
                "We launched our Zkittlez harvest with 500 custom grinders. They sold out in 3 days and now
                customers specifically ask for our brand at dispensaries. The grinders created more brand
                awareness than any advertising we've done."
            </p>

            <div style="text-align: center;">
                <div style="font-weight: 600; color: #2d5016; font-size: 18px;">Marcus Thompson</div>
                <div style="color: #666; font-size: 14px;">Emerald Triangle Farms, Humboldt County</div>
                <div style="color: #FFD700; font-size: 16px; margin-top: 10px;">★★★★★</div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="padding: 70px 20px; background: #f8f9fa;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            Questions from Fellow Cultivators
        </h2>

        <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #2d5016; font-size: 20px; margin: 0 0 15px 0;">
                    Can I do different designs for different strains?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Absolutely! Most cultivators create strain-specific designs. We can do different colors, engravings, and even include terpene profiles and effects for each strain. No extra setup fees for multiple designs on the same order.
                </p>
            </div>

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #2d5016; font-size: 20px; margin: 0 0 15px 0;">
                    What about small batch/limited releases?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Perfect for craft cultivators! No minimums mean you can do 25 pieces for that special pheno hunt winner or 1000 for your flagship strain. Many growers do numbered limited editions that become collectibles.
                </p>
            </div>

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #2d5016; font-size: 20px; margin: 0 0 15px 0;">
                    Can you include QR codes for test results/strain info?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Yes! QR codes are hugely popular. Link to COAs, your website, strain lineage, cultivation story, or even Spotify playlists. It's a direct connection to your customers that dispensaries can't interfere with.
                </p>
            </div>

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #2d5016; font-size: 20px; margin: 0 0 15px 0;">
                    How do other cultivators price these for resale?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Most cultivators bundle them with eighth purchases or sell them for $25-40 retail. At our wholesale prices, you're looking at 60-75% margins. Some use them as purchase incentives: "Buy a half oz, get our limited edition grinder."
                </p>
            </div>

            <div>
                <h3 style="color: #2d5016; font-size: 20px; margin: 0 0 15px 0;">
                    What file formats do you need for designs?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    We work with everything - PNG, JPG, AI, PSD, even hand sketches. Our design team can create professional mockups from your logo and ideas. Free design services included on orders over 100 units.
                </p>
            </div>

        </div>
    </div>
</div>

<!-- Final CTA -->
<div style="background: linear-gradient(135deg, #2d5016 0%, #3a6218 100%); padding: 80px 20px; text-align: center; color: white; margin: 0 -20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 44px; font-weight: 800; margin: 0 0 20px 0; line-height: 1.1;">
            Ready to Build a Brand That<br>
            <span style="color: #8BC34A;">Travels With Your Flower?</span>
        </h2>

        <p style="font-size: 20px; opacity: 0.95; margin: 0 0 40px 0; line-height: 1.5;">
            Join 200+ craft cultivators who've turned their strains into household names
        </p>

        <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 40px; flex-wrap: wrap; font-size: 16px;">
            <div>✓ No Minimums</div>
            <div>✓ Strain-Specific Designs</div>
            <div>✓ 5-Day Rush</div>
            <div>✓ QR Codes</div>
        </div>

        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; padding: 20px 45px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; box-shadow: 0 6px 25px rgba(139,195,74,0.4);">
                Get Your Strain Mockup Free
            </a>
            <a href="tel:6506403836" style="display: inline-block; background: transparent; color: white; padding: 20px 45px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; border: 2px solid rgba(255,255,255,0.5);">
                Talk to a Cultivator Specialist
            </a>
        </div>

        <p style="margin-top: 40px; opacity: 0.7; font-size: 16px;">
            Questions? Email <a href="mailto:cultivators@munchmakers.com" style="color: #8BC34A; text-decoration: none;">cultivators@munchmakers.com</a>
        </p>
    </div>
</div>'''

# Step 4: Create the page in BigCommerce
print("\n" + "-"*60)
print("CREATING BIGCOMMERCE PAGE")
print("-"*60)

page_data = {
    "type": "page",
    "name": "Custom Accessories for Cannabis Cultivators & Growers",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 101,
    "meta_description": "Build lasting brand recognition for your strains. Custom grinders & accessories that travel with your premium flower. No minimums.",
    "search_keywords": "cannabis cultivator accessories, grower merchandise, strain specific grinders, craft cannabis branding"
}

url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print(f"\n{'='*60}")
    print("✅ CULTIVATORS PAGE CREATED SUCCESSFULLY!")
    print("="*60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🎯 Target Audience: Cannabis Cultivators & Craft Growers")
    print("📝 Key Messages:")
    print("   • Build brand recognition that lasts")
    print("   • Your strains deserve to be remembered")
    print("   • Turn customers into brand ambassadors")
    print("   • Direct connection to consumers")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
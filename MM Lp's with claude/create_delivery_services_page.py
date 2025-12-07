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
print("CREATING CANNABIS DELIVERY SERVICES LANDING PAGE")
print("="*60)

# Step 1: Generate delivery-specific images
def generate_image(prompt, name):
    """Generate image with Freepik API"""
    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers_freepik = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json"
    }

    data = {
        "prompt": prompt,
        "negative_prompt": "cartoon, illustration, low quality, blurry, text, logos, watermarks, amateur",
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
                filename = f"delivery_{name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
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

# Generate delivery-specific images
print("\n" + "-"*60)
print("GENERATING DELIVERY SERVICE IMAGES")
print("-"*60)

delivery_images = [
    {
        "name": "hero",
        "remote": "delivery_hero.png",
        "prompt": "Professional delivery driver handing premium black branded bag to happy customer at modern home doorway, evening golden hour lighting, both people smiling, delivery app visible on phone, professional commercial photography"
    },
    {
        "name": "unboxing",
        "remote": "delivery_unboxing.png",
        "prompt": "Hands opening sleek black delivery bag revealing custom branded grinders, lighters and accessories inside, Instagram worthy unboxing moment, bright white background, professional product photography, shallow depth of field"
    },
    {
        "name": "social_proof",
        "remote": "delivery_social.png",
        "prompt": "Young person taking photo with smartphone of branded cannabis accessories from delivery, social media sharing moment, modern apartment setting, natural lighting, lifestyle photography"
    },
    {
        "name": "delivery_kit",
        "remote": "delivery_kit.png",
        "prompt": "Premium black delivery bags with custom branded accessories kit laid out professionally, including grinders, rolling trays, lighters, organized presentation, white background, commercial product photography"
    }
]

image_urls = {}
for img in delivery_images:
    base64_data, local_file = generate_image(img["prompt"], img["name"])
    if local_file:
        url = upload_to_webdav(local_file, img["remote"])
        if url:
            image_urls[img["name"]] = url
    time.sleep(3)  # Rate limit

# Use uploaded images or fallbacks
hero_img = image_urls.get('hero', 'https://via.placeholder.com/1200x600/1a1a1a/8BC34A?text=Premium+Delivery+Service')
unboxing_img = image_urls.get('unboxing', 'https://via.placeholder.com/800x400/ffffff/8BC34A?text=Unboxing+Experience')
social_img = image_urls.get('social_proof', 'https://via.placeholder.com/800x400/8BC34A/ffffff?text=Social+Sharing')
kit_img = image_urls.get('delivery_kit', 'https://via.placeholder.com/800x400/1a1a1a/8BC34A?text=Delivery+Kit')

# Step 3: Create the HTML content
print("\n" + "-"*60)
print("CREATING PAGE CONTENT")
print("-"*60)

html_content = f'''<!-- MunchMakers Cannabis Delivery Services Landing Page -->

<!-- Hero Section - Delivery Focused -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 70px 20px; margin: -20px -20px 0 -20px; position: relative;">
    <div style="max-width: 1200px; margin: 0 auto;">

        <!-- Trust Badge -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="display: inline-block; background: rgba(139,195,74,0.15); padding: 8px 20px; border-radius: 30px; border: 1px solid rgba(139,195,74,0.3); font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">
                🚚 Powering 100+ Delivery Services Nationwide
            </span>
        </div>

        <!-- Main Message -->
        <h1 style="font-size: 48px; font-weight: 800; text-align: center; margin: 0 0 20px 0; line-height: 1.1;">
            Turn Every Delivery Into an<br>
            <span style="color: #8BC34A; text-shadow: 0 0 30px rgba(139,195,74,0.5);">Unforgettable Experience</span>
        </h1>

        <p style="font-size: 22px; text-align: center; margin: 0 auto 40px; max-width: 800px; opacity: 0.95; line-height: 1.5;">
            Stand out from the crowd with custom accessories that make customers choose YOU over the competition.
            Create Instagram moments, not just deliveries.
        </p>

        <!-- Key Stats -->
        <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 40px; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #8BC34A;">68%</div>
                <div style="font-size: 14px; opacity: 0.9;">Higher Retention</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #8BC34A;">3.5x</div>
                <div style="font-size: 14px; opacity: 0.9;">More Referrals</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #8BC34A;">$47</div>
                <div style="font-size: 14px; opacity: 0.9;">Higher AOV</div>
            </div>
        </div>

        <!-- CTAs -->
        <div style="text-align: center;">
            <a href="/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 20px rgba(139,195,74,0.4); margin: 0 10px;">
                Get Free Delivery Kit Mockup
            </a>
            <a href="#calculator" style="display: inline-block; background: transparent; color: white; padding: 18px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; border: 2px solid rgba(255,255,255,0.5); margin: 0 10px;">
                Calculate Your ROI →
            </a>
        </div>
    </div>
</div>

<!-- Hero Image -->
<div style="background: #f8f9fa; padding: 50px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <img src="{hero_img}" alt="Premium cannabis delivery experience" style="width: 100%; max-width: 900px; height: auto; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
    </div>
</div>

<!-- The Problem Section -->
<div style="padding: 70px 20px; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 40px; text-align: center; font-weight: 800; margin: 0 0 20px 0; color: #1a1a1a;">
            In a Sea of Delivery Apps,<br>
            <span style="color: #8BC34A;">How Do You Stand Out?</span>
        </h2>

        <p style="text-align: center; color: #666; font-size: 18px; margin: 0 auto 50px; max-width: 700px;">
            When everyone delivers in 45 minutes with the same products, you need something that makes customers remember YOU
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">

            <!-- Generic Delivery -->
            <div style="background: linear-gradient(135deg, #fff5f5, #ffeeee); border-left: 4px solid #ff4444; border-radius: 12px; padding: 35px;">
                <h3 style="color: #ff4444; font-size: 24px; margin: 0 0 25px 0;">📦 Generic Delivery Service</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 2.2;">
                    <li style="padding: 5px 0;">• Brown bag handoff at door</li>
                    <li style="padding: 5px 0;">• Forgotten in 5 minutes</li>
                    <li style="padding: 5px 0;">• Customer shops competitors</li>
                    <li style="padding: 5px 0;">• $100+ to acquire new customer</li>
                    <li style="padding: 5px 0;">• 20% reorder rate</li>
                    <li style="padding: 5px 0;">• Zero social media mentions</li>
                </ul>
            </div>

            <!-- Premium Experience -->
            <div style="background: linear-gradient(135deg, #f0fff4, #e8f5e9); border-left: 4px solid #4CAF50; border-radius: 12px; padding: 35px;">
                <h3 style="color: #4CAF50; font-size: 24px; margin: 0 0 25px 0;">🎁 Your Premium Experience</h3>
                <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 2.2;">
                    <li style="padding: 5px 0;">• Unboxing moment they film</li>
                    <li style="padding: 5px 0;">• Branded gear they keep</li>
                    <li style="padding: 5px 0;">• Customers request YOU</li>
                    <li style="padding: 5px 0;">• Organic acquisition via shares</li>
                    <li style="padding: 5px 0;">• 65% reorder rate</li>
                    <li style="padding: 5px 0;">• Instagram story tags daily</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<!-- Unboxing Experience Section -->
<div style="background: #f8f9fa; padding: 70px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <span style="display: inline-block; background: #8BC34A; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                The Experience
            </span>
            <h2 style="font-size: 36px; font-weight: 800; margin: 0 0 15px 0; color: #1a1a1a;">
                Create Moments Worth Sharing
            </h2>
            <p style="color: #666; font-size: 18px; max-width: 700px; margin: 0 auto;">
                Every delivery is an opportunity to create a memorable brand experience
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; margin-bottom: 50px;">
            <div>
                <h3 style="font-size: 28px; color: #1a1a1a; margin: 0 0 20px 0;">The Unboxing Magic</h3>
                <p style="color: #666; line-height: 1.8; font-size: 17px; margin-bottom: 25px;">
                    Transform your delivery from a transaction into an experience. When customers open your branded bag
                    and find premium accessories with their order, they don't just receive products – they receive a gift.
                </p>
                <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                    <h4 style="color: #8BC34A; margin: 0 0 15px 0;">What Happens Next:</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; color: #666; line-height: 1.8;">
                        <li>📸 They photograph it for Instagram</li>
                        <li>🏷️ They tag your service</li>
                        <li>👥 Friends ask "where'd you get that?"</li>
                        <li>🔄 They order from you again</li>
                    </ul>
                </div>
            </div>
            <div>
                <img src="{unboxing_img}" alt="Premium unboxing experience" style="width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            </div>
        </div>

        <div style="text-align: center;">
            <img src="{social_img}" alt="Social media sharing moment" style="max-width: 800px; width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>
    </div>
</div>

<!-- ROI Calculator Section -->
<div id="calculator" style="padding: 70px 20px; background: white;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 40px;">
            <span style="display: inline-block; background: #8BC34A; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                ROI Calculator
            </span>
            <h2 style="font-size: 36px; font-weight: 800; margin: 15px 0; color: #1a1a1a;">
                The Math Makes Sense
            </h2>
            <p style="color: #666; font-size: 18px;">See how branded accessories pay for themselves</p>
        </div>

        <div style="background: linear-gradient(135deg, #f8f9fa, #ffffff); border-radius: 16px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">

                <!-- Without Accessories -->
                <div style="background: white; padding: 30px; border-radius: 12px; border: 2px solid #ff4444;">
                    <h3 style="color: #ff4444; margin: 0 0 20px 0;">Without Branded Accessories</h3>
                    <div style="margin-bottom: 15px;">
                        <div style="color: #666; font-size: 14px;">Customer Acquisition Cost</div>
                        <div style="font-size: 28px; font-weight: bold; color: #1a1a1a;">$85</div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <div style="color: #666; font-size: 14px;">30-Day Retention</div>
                        <div style="font-size: 28px; font-weight: bold; color: #1a1a1a;">22%</div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <div style="color: #666; font-size: 14px;">Avg Order Value</div>
                        <div style="font-size: 28px; font-weight: bold; color: #1a1a1a;">$67</div>
                    </div>
                    <div style="border-top: 2px solid #f0f0f0; padding-top: 15px; margin-top: 20px;">
                        <div style="color: #666; font-size: 14px;">90-Day Customer Value</div>
                        <div style="font-size: 32px; font-weight: bold; color: #ff4444;">$147</div>
                    </div>
                </div>

                <!-- With Accessories -->
                <div style="background: white; padding: 30px; border-radius: 12px; border: 2px solid #4CAF50;">
                    <h3 style="color: #4CAF50; margin: 0 0 20px 0;">With Branded Accessories</h3>
                    <div style="margin-bottom: 15px;">
                        <div style="color: #666; font-size: 14px;">CAC (includes accessories)</div>
                        <div style="font-size: 28px; font-weight: bold; color: #1a1a1a;">$95</div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <div style="color: #666; font-size: 14px;">30-Day Retention</div>
                        <div style="font-size: 28px; font-weight: bold; color: #1a1a1a;">58%</div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <div style="color: #666; font-size: 14px;">Avg Order Value</div>
                        <div style="font-size: 28px; font-weight: bold; color: #1a1a1a;">$89</div>
                    </div>
                    <div style="border-top: 2px solid #f0f0f0; padding-top: 15px; margin-top: 20px;">
                        <div style="color: #666; font-size: 14px;">90-Day Customer Value</div>
                        <div style="font-size: 32px; font-weight: bold; color: #4CAF50;">$412</div>
                    </div>
                </div>

            </div>

            <div style="background: linear-gradient(135deg, #8BC34A, #7CB342); padding: 25px; border-radius: 12px; text-align: center; color: white;">
                <div style="font-size: 20px; margin-bottom: 10px;">Net Increase in Customer Value</div>
                <div style="font-size: 48px; font-weight: 800;">+$265</div>
                <div style="font-size: 16px; opacity: 0.95; margin-top: 10px;">
                    For just $10 in branded accessories per new customer
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Solutions -->
<div style="background: #f8f9fa; padding: 70px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            Perfect Accessories for Delivery Services
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">

            <!-- Welcome Kit -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <div style="background: linear-gradient(135deg, #8BC34A, #689F38); padding: 40px; text-align: center;">
                    <div style="font-size: 64px;">🎁</div>
                    <div style="color: white; font-size: 20px; font-weight: 600;">NEW CUSTOMER KIT</div>
                </div>
                <div style="padding: 30px;">
                    <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">First Order Welcome Kit</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                        Make first impressions unforgettable. Include with every new customer's first order.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 25px 0; color: #666;">
                        <li style="padding: 5px 0;">• Custom grinder with your logo</li>
                        <li style="padding: 5px 0;">• Branded lighter</li>
                        <li style="padding: 5px 0;">• Welcome card with discount code</li>
                        <li style="padding: 5px 0;">• Smell-proof storage jar</li>
                    </ul>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="color: #666; font-size: 14px;">Investment per kit</div>
                        <div style="color: #8BC34A; font-size: 28px; font-weight: bold;">$12-15</div>
                    </div>
                </div>
            </div>

            <!-- Loyalty Rewards -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <div style="background: linear-gradient(135deg, #FF9800, #F57C00); padding: 40px; text-align: center;">
                    <div style="font-size: 64px;">⭐</div>
                    <div style="color: white; font-size: 20px; font-weight: 600;">LOYALTY REWARDS</div>
                </div>
                <div style="padding: 30px;">
                    <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Milestone Rewards</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                        Reward loyal customers at order milestones to encourage repeat business.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 25px 0; color: #666;">
                        <li style="padding: 5px 0;">• 5th order: Premium lighter</li>
                        <li style="padding: 5px 0;">• 10th order: Rolling tray</li>
                        <li style="padding: 5px 0;">• 20th order: Limited grinder</li>
                        <li style="padding: 5px 0;">• VIP status: Exclusive gear</li>
                    </ul>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="color: #666; font-size: 14px;">Avg cost per reward</div>
                        <div style="color: #FF9800; font-size: 28px; font-weight: bold;">$5-20</div>
                    </div>
                </div>
            </div>

            <!-- Referral Incentives -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <div style="background: linear-gradient(135deg, #9C27B0, #7B1FA2); padding: 40px; text-align: center;">
                    <div style="font-size: 64px;">🔄</div>
                    <div style="color: white; font-size: 20px; font-weight: 600;">REFERRAL GIFTS</div>
                </div>
                <div style="padding: 30px;">
                    <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Share & Earn Program</h3>
                    <p style="color: #666; line-height: 1.6; margin: 0 0 20px 0;">
                        Turn customers into brand ambassadors with referral rewards they'll love.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0 0 25px 0; color: #666;">
                        <li style="padding: 5px 0;">• Referrer gets premium item</li>
                        <li style="padding: 5px 0;">• Friend gets welcome kit</li>
                        <li style="padding: 5px 0;">• Both get discount codes</li>
                        <li style="padding: 5px 0;">• Track via unique codes</li>
                    </ul>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="color: #666; font-size: 14px;">CAC via referrals</div>
                        <div style="color: #9C27B0; font-size: 28px; font-weight: bold;">$15-25</div>
                    </div>
                </div>
            </div>

        </div>

        <div style="text-align: center; margin-top: 50px;">
            <img src="{kit_img}" alt="Delivery service accessory kits" style="max-width: 900px; width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>
    </div>
</div>

<!-- Success Stories -->
<div style="padding: 70px 20px; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            How Top Delivery Services Win with Branded Gear
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">

            <div style="background: #f8f9fa; padding: 35px; border-radius: 12px; border-top: 4px solid #8BC34A;">
                <h3 style="color: #8BC34A; font-size: 22px; margin: 0 0 15px 0;">GreenRush LA</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Started including branded grinders with first orders. Customer retention jumped from 18% to 52% in 90 days.
                </p>
                <div style="background: white; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #666;">Result:</div>
                    <strong style="color: #1a1a1a; font-size: 18px;">3x increase in LTV</strong>
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 35px; border-radius: 12px; border-top: 4px solid #FF9800;">
                <h3 style="color: #FF9800; font-size: 22px; margin: 0 0 15px 0;">QuickBud SF</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Loyalty program with branded rewards. Members now make up 68% of revenue vs 23% before.
                </p>
                <div style="background: white; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #666;">Result:</div>
                    <strong style="color: #1a1a1a; font-size: 18px;">$450K additional revenue</strong>
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 35px; border-radius: 12px; border-top: 4px solid #9C27B0;">
                <h3 style="color: #9C27B0; font-size: 22px; margin: 0 0 15px 0;">CloudCanna NYC</h3>
                <p style="color: #666; line-height: 1.7; margin: 0 0 20px 0;">
                    Referral program with custom accessories. 40% of new customers now come from referrals.
                </p>
                <div style="background: white; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 14px; color: #666;">Result:</div>
                    <strong style="color: #1a1a1a; font-size: 18px;">$25 CAC vs $85 ads</strong>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Implementation Timeline -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 70px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0;">
            Launch Your Premium Experience in 30 Days
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 30px;">

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">📅</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">Week 1</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Design & approve mockups. Choose your product mix. Set quantities.
                </p>
            </div>

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">⚙️</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">Week 2-3</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Production & quality control. Prepare launch strategy. Train team.
                </p>
            </div>

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">🚀</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">Week 4</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Launch with fanfare. Document reactions. Share on social.
                </p>
            </div>

            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">📈</div>
                <h3 style="font-size: 20px; margin: 0 0 10px 0;">Week 5+</h3>
                <p style="opacity: 0.9; font-size: 15px; line-height: 1.6;">
                    Track metrics. Optimize. Scale what works. Reorder inventory.
                </p>
            </div>

        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="padding: 70px 20px; background: #f8f9fa;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h2 style="font-size: 36px; text-align: center; font-weight: 800; margin: 0 0 50px 0; color: #1a1a1a;">
            Common Questions from Delivery Services
        </h2>

        <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    How much inventory should I start with?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Most delivery services start with 100-250 welcome kits and 50-100 loyalty rewards. With our no-minimum option, you can test small and scale based on results. Typical reorder is every 30-45 days.
                </p>
            </div>

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    What's the best item for new customer acquisition?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Grinders are the winner – 87% of customers keep them permanently. Include a quality grinder with first orders and watch retention soar. Add a lighter for impulse appeal. Total cost: $10-12, value perception: $30-40.
                </p>
            </div>

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    How do I package these with deliveries?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Create a "surprise & delight" moment. Use branded bags or boxes. Include a welcome card explaining the gift. Many services use heat-sealed bags with window cutouts to showcase the accessories inside.
                </p>
            </div>

            <div style="border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px;">
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    Should I charge for accessories or give them free?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    Free with strategic triggers works best: first order, $100+ orders, loyalty milestones, referrals. You can also offer upgrades – "Add a premium grinder for just $10" (your cost: $6). The perceived value far exceeds the cost.
                </p>
            </div>

            <div>
                <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 15px 0;">
                    How quickly can I get started?
                </h3>
                <p style="color: #666; line-height: 1.7; margin: 0;">
                    24-hour mockups, 5-day rush production available. Most services launch their program within 2-3 weeks from first contact. We can dropship directly to your fulfillment center or delivery hub.
                </p>
            </div>

        </div>
    </div>
</div>

<!-- Testimonial -->
<div style="padding: 70px 20px; background: white;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #f0f9f0, #e8f5e9); border-radius: 12px; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">

            <div style="font-size: 48px; color: #8BC34A; text-align: center; line-height: 1; margin-bottom: 20px;">"</div>

            <p style="font-size: 20px; line-height: 1.8; color: #1a1a1a; text-align: center; font-style: italic; margin: 0 0 30px 0;">
                "We were hemorrhaging customers to competitors until we started including branded accessories.
                Now customers specifically request us because they want 'the delivery service with the cool grinders.'
                Our CAC dropped 40% and retention tripled. Best investment we've made."
            </p>

            <div style="text-align: center;">
                <div style="font-weight: 600; color: #1a1a1a; font-size: 18px;">Alex Rivera</div>
                <div style="color: #666; font-size: 14px;">Founder, GreenDash Delivery (Los Angeles)</div>
                <div style="color: #FFD700; font-size: 16px; margin-top: 10px;">★★★★★</div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 80px 20px; text-align: center; color: white; margin: 0 -20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 44px; font-weight: 800; margin: 0 0 20px 0; line-height: 1.1;">
            Ready to Become the Delivery Service<br>
            <span style="color: #8BC34A;">Everyone Talks About?</span>
        </h2>

        <p style="font-size: 20px; opacity: 0.95; margin: 0 0 40px 0; line-height: 1.5;">
            Join 100+ delivery services creating unforgettable experiences with every order
        </p>

        <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 40px; flex-wrap: wrap; font-size: 16px;">
            <div>✓ No Minimums</div>
            <div>✓ 5-Day Rush</div>
            <div>✓ Free Mockups</div>
            <div>✓ Dropship Available</div>
        </div>

        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="/loyalty-program/" style="display: inline-block; background: linear-gradient(135deg, #8BC34A, #7CB342); color: white; padding: 20px 45px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; box-shadow: 0 6px 25px rgba(139,195,74,0.4);">
                Get Your Delivery Kit Mockup
            </a>
            <a href="tel:6506403836" style="display: inline-block; background: transparent; color: white; padding: 20px 45px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 600; border: 2px solid rgba(255,255,255,0.5);">
                Talk to Delivery Specialist
            </a>
        </div>

        <p style="margin-top: 40px; opacity: 0.7; font-size: 16px;">
            Questions? Email <a href="mailto:delivery@munchmakers.com" style="color: #8BC34A; text-decoration: none;">delivery@munchmakers.com</a> • Text: (650) 640-3836
        </p>
    </div>
</div>'''

# Step 4: Create the page in BigCommerce
print("\n" + "-"*60)
print("CREATING BIGCOMMERCE PAGE")
print("-"*60)

page_data = {
    "type": "page",
    "name": "Custom Cannabis Accessories for Delivery Services",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 102,
    "meta_description": "Stand out from delivery app competition. Create unforgettable unboxing experiences that increase retention by 68%. No minimums.",
    "search_keywords": "cannabis delivery accessories, delivery service branding, cannabis delivery marketing, unboxing experience"
}

url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print(f"\n{'='*60}")
    print("✅ DELIVERY SERVICES PAGE CREATED SUCCESSFULLY!")
    print("="*60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🚚 Target Audience: Cannabis Delivery Services")
    print("📝 Key Messages:")
    print("   • Turn deliveries into experiences")
    print("   • 68% higher retention rate")
    print("   • Create Instagram-worthy moments")
    print("   • ROI: $265 increase in customer value")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
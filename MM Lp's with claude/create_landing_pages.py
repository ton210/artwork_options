import requests
import json
import time

# Freepik API credentials
FREEPIK_API_KEY = 'FPSX381b01bdceb04b9fa3c51f52816cfacd'

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
base_domain = 'www.munchmakers.com'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Headers for BigCommerce API requests
bc_headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# Landing pages configuration
landing_pages = [
    {
        'name': 'Cannabis Dispensaries',
        'url': 'dispensaries',
        'title': 'Custom Cannabis Accessories for Dispensaries',
        'description': 'Elevate your dispensary brand with custom grinders, rolling trays, and smoking accessories',
        'image_prompt': 'modern cannabis dispensary with professional branding, custom grinders and accessories on display, clean retail environment, green and black color scheme',
        'hero_title': 'Wholesale Custom Cannabis Accessories for Dispensaries',
        'hero_subtitle': 'Build Your Brand with Premium Custom Smoking Accessories',
        'featured_products': ['Custom Grinders', 'Custom Rolling Trays', 'Branded Packaging', 'Custom Lighters'],
        'benefits': [
            'Build brand loyalty with custom branded products',
            'Increase average transaction value',
            'Stand out from competitors',
            'No minimum order quantities available'
        ]
    },
    {
        'name': 'Cannabis Cultivators',
        'url': 'cultivators',
        'title': 'Custom Accessories for Cannabis Growers & Cultivators',
        'description': 'Promote your strains and brand with custom grinders and smoking accessories',
        'image_prompt': 'cannabis cultivation facility with branded grinders and accessories, professional grow operation, green plants background',
        'hero_title': 'Custom Accessories for Cannabis Cultivators & Growers',
        'hero_subtitle': 'Showcase Your Strains with Premium Branded Accessories',
        'featured_products': ['4 Piece Grinders', 'Stash Jars', 'Custom Rolling Papers', 'Storage'],
        'benefits': [
            'Promote your unique strains',
            'Create memorable unboxing experiences',
            'Build brand recognition',
            'Perfect for strain-specific merchandise'
        ]
    },
    {
        'name': 'Cannabis Delivery Services',
        'url': 'delivery-services',
        'title': 'Custom Branded Accessories for Cannabis Delivery',
        'description': 'Make every delivery memorable with custom branded smoking accessories',
        'image_prompt': 'cannabis delivery service with branded packaging and accessories, professional delivery bags, modern design',
        'hero_title': 'Custom Accessories for Cannabis Delivery Services',
        'hero_subtitle': 'Make Every Delivery a Brand Experience',
        'featured_products': ['Joint Cases & Holders', 'Smell Proof Storage', 'Custom Lighters', 'Pre-Rolled Cones'],
        'benefits': [
            'Enhance customer loyalty',
            'Create Instagram-worthy unboxing',
            'Increase repeat orders',
            'Stand out in a competitive market'
        ]
    },
    {
        'name': 'Musicians & Artists',
        'url': 'musicians-artists',
        'title': 'Custom Merch for Musicians, Artists & Bands',
        'description': 'Create unique merchandise with custom grinders and smoking accessories',
        'image_prompt': 'music concert scene with custom branded smoking accessories as merchandise, artistic design, vibrant colors',
        'hero_title': 'Custom Smoking Accessories for Musicians & Artists',
        'hero_subtitle': 'Unique Merch That Fans Actually Want',
        'featured_products': ['Custom Grinders', 'Custom Rolling Trays', 'Custom Lighters', 'Custom Ashtrays'],
        'benefits': [
            'Higher profit margins than traditional merch',
            'Unique items fans can\'t find elsewhere',
            'Build stronger fan connections',
            'Perfect for tour merchandise'
        ]
    },
    {
        'name': 'Merchandise Agencies',
        'url': 'merch-agencies',
        'title': 'Wholesale Custom Cannabis Accessories for Merch Companies',
        'description': 'Expand your product offerings with high-margin custom smoking accessories',
        'image_prompt': 'professional merchandise display with various custom branded smoking accessories, business setting',
        'hero_title': 'Wholesale Custom Accessories for Merchandise Agencies',
        'hero_subtitle': 'High-Margin Products Your Clients Will Love',
        'featured_products': ['No Minimum', 'Promotional Items', 'Custom Apparel', 'Promotional Accessories'],
        'benefits': [
            'Competitive wholesale pricing',
            'Fast turnaround times',
            'No minimum options available',
            'White label solutions'
        ]
    },
    {
        'name': 'Events & Celebrations',
        'url': 'events-celebrations',
        'title': 'Custom Party Favors - Weddings, Bachelor Parties & Events',
        'description': 'Memorable custom smoking accessories for weddings, bachelor parties, and special events',
        'image_prompt': 'elegant wedding reception with custom engraved grinders as party favors, celebration atmosphere',
        'hero_title': 'Custom Party Favors for Weddings & Special Events',
        'hero_subtitle': 'Unique Gifts Your Guests Will Actually Keep',
        'featured_products': ['Custom Grinders', 'Custom Lighters', 'Custom Ashtrays', 'Custom Rolling Trays'],
        'benefits': [
            'Personalized with names and dates',
            'Unique alternative to traditional favors',
            'Bulk pricing for large events',
            'Fast production times'
        ]
    },
    {
        'name': 'Cannabis Competitions',
        'url': 'grow-competitions',
        'title': 'Custom Awards & Prizes for Cannabis Competitions',
        'description': 'Premium custom accessories for cannabis cups, grow competitions, and industry events',
        'image_prompt': 'cannabis competition event with trophy grinders and premium accessories as prizes, professional event setting',
        'hero_title': 'Custom Prizes for Cannabis Competitions & Events',
        'hero_subtitle': 'Premium Awards That Winners Will Treasure',
        'featured_products': ['Specialty Grinders', 'Premium Metal Lighters', 'Wood & Organic', 'Glass Stash Jars'],
        'benefits': [
            'Create prestigious award packages',
            'Engrave winner details',
            'Premium materials available',
            'Commemorate special achievements'
        ]
    }
]

def generate_image(prompt):
    """Generate an image using Freepik API"""
    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json"
    }

    data = {
        "prompt": prompt + ", professional product photography, clean modern design, cannabis industry branding",
        "negative_prompt": "low quality, blurry, distorted, unprofessional",
        "guidance_scale": 7,
        "seed": None,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        }
    }

    try:
        response = requests.post(url, headers=headers, json=data)
        if response.status_code == 200:
            result = response.json()
            if 'data' in result and len(result['data']) > 0:
                return result['data'][0]['base64']
        else:
            print(f"Error generating image: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"Exception generating image: {e}")

    return None

def create_landing_page_html(page_config, image_base64=None):
    """Create HTML for a landing page with inline styles"""

    # Convert featured products to links
    product_links = []
    for product in page_config['featured_products']:
        # Try to map to actual category URLs
        if 'Grinders' in product:
            if '4 Piece' in product:
                product_links.append(f'<a href="https://www.munchmakers.com/product-category/4-piece-grinders/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
            elif '2 Piece' in product:
                product_links.append(f'<a href="https://www.munchmakers.com/product-category/2-piece-grinders/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
            else:
                product_links.append(f'<a href="https://www.munchmakers.com/product-category/custom-grinders/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        elif 'Rolling Trays' in product:
            product_links.append(f'<a href="https://www.munchmakers.com/product-category/custom-rolling-trays/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        elif 'Lighters' in product:
            product_links.append(f'<a href="https://www.munchmakers.com/product-category/custom-lighters/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        elif 'Ashtrays' in product:
            product_links.append(f'<a href="https://www.munchmakers.com/product-category/custom-ashtrays/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        elif 'Stash Jars' in product or 'Storage' in product:
            product_links.append(f'<a href="https://www.munchmakers.com/product-category/custom-weed-stash-jars/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        elif 'Rolling Papers' in product:
            product_links.append(f'<a href="https://www.munchmakers.com/product-category/custom-rolling-papers/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        elif 'No Minimum' in product:
            product_links.append(f'<a href="https://www.munchmakers.com/product-category/no-minimum/" style="color: #8BC34A; text-decoration: none; font-weight: 600;">{product}</a>')
        else:
            product_links.append(f'<span style="color: #8BC34A; font-weight: 600;">{product}</span>')

    # Build benefits HTML
    benefits_html = '\n'.join([f'<li style="margin-bottom: 15px; padding-left: 25px; position: relative;"><span style="position: absolute; left: 0; color: #8BC34A;">✓</span> {benefit}</li>' for benefit in page_config['benefits']])

    # Image section
    image_html = ''
    if image_base64:
        image_html = f'<div style="margin: 40px 0; text-align: center;"><img src="data:image/png;base64,{image_base64}" style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" alt="{page_config["title"]}" /></div>'

    html = f'''
<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px;">

    <!-- Hero Section -->
    <div style="background: linear-gradient(135deg, #2C3E50 0%, #3B4D61 100%); border-radius: 15px; padding: 60px 40px; margin-bottom: 50px; text-align: center; color: white;">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">{page_config['hero_title']}</h1>
        <p style="font-size: 24px; margin-bottom: 30px; color: #8BC34A; font-weight: 500;">{page_config['hero_subtitle']}</p>
        <div style="margin-top: 40px;">
            <a href="https://www.munchmakers.com/loyalty-program/" style="display: inline-block; background-color: #8BC34A; color: white; padding: 18px 40px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 600; margin: 10px; box-shadow: 0 4px 15px rgba(139,195,74,0.4); transition: all 0.3s;">Get Free Mockup</a>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background-color: transparent; color: white; padding: 18px 40px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 600; margin: 10px; border: 2px solid white; transition: all 0.3s;">Contact Sales</a>
        </div>
    </div>

    {image_html}

    <!-- Featured Products Section -->
    <div style="background-color: #F8F9FA; border-radius: 15px; padding: 40px; margin-bottom: 40px;">
        <h2 style="font-size: 32px; color: #2C3E50; margin-bottom: 25px; text-align: center;">Popular Products for {page_config['name']}</h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; font-size: 18px;">
            {' • '.join(product_links)}
        </div>
    </div>

    <!-- Benefits Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px;">
        <div>
            <h2 style="font-size: 28px; color: #2C3E50; margin-bottom: 20px;">Why MunchMakers?</h2>
            <ul style="list-style: none; padding: 0; font-size: 18px; line-height: 1.8; color: #555;">
                {benefits_html}
            </ul>
        </div>
        <div style="background-color: #2C3E50; border-radius: 15px; padding: 30px; color: white;">
            <h3 style="font-size: 24px; margin-bottom: 20px; color: #8BC34A;">Quick Facts</h3>
            <ul style="list-style: none; padding: 0; font-size: 16px; line-height: 2;">
                <li>✓ <strong>No MOQ</strong> on select items</li>
                <li>✓ <strong>5-Day Production</strong> available</li>
                <li>✓ <strong>Free Mockups</strong> before ordering</li>
                <li>✓ <strong>B2B Wholesale</strong> pricing</li>
                <li>✓ <strong>US-Based</strong> customer support</li>
            </ul>
        </div>
    </div>

    <!-- Product Showcase -->
    <div style="margin-bottom: 50px;">
        <h2 style="font-size: 32px; color: #2C3E50; text-align: center; margin-bottom: 40px;">Best Sellers for {page_config['name']}</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">

            <!-- Product Card 1 -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s;">
                <div style="height: 200px; background: linear-gradient(135deg, #8BC34A, #689F38); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                    Custom 4-Piece Grinder
                </div>
                <div style="padding: 20px;">
                    <h3 style="margin: 0 0 10px 0; color: #2C3E50;">4 Piece Aluminum Grinder</h3>
                    <p style="color: #666; margin: 10px 0;">Premium aluminum construction with custom engraving</p>
                    <p style="color: #8BC34A; font-size: 20px; font-weight: bold; margin: 15px 0;">From $35.00</p>
                    <a href="https://www.munchmakers.com/product-category/4-piece-grinders/" style="display: block; text-align: center; background-color: #2C3E50; color: white; padding: 12px; text-decoration: none; border-radius: 5px;">View Options</a>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s;">
                <div style="height: 200px; background: linear-gradient(135deg, #FF9800, #F57C00); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                    Custom Rolling Tray
                </div>
                <div style="padding: 20px;">
                    <h3 style="margin: 0 0 10px 0; color: #2C3E50;">Metal Rolling Tray</h3>
                    <p style="color: #666; margin: 10px 0;">Full-color custom printing on durable metal</p>
                    <p style="color: #8BC34A; font-size: 20px; font-weight: bold; margin: 15px 0;">From $25.00</p>
                    <a href="https://www.munchmakers.com/product-category/custom-rolling-trays/" style="display: block; text-align: center; background-color: #2C3E50; color: white; padding: 12px; text-decoration: none; border-radius: 5px;">View Options</a>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s;">
                <div style="height: 200px; background: linear-gradient(135deg, #9C27B0, #7B1FA2); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                    Custom Lighters
                </div>
                <div style="padding: 20px;">
                    <h3 style="margin: 0 0 10px 0; color: #2C3E50;">BIC Custom Lighters</h3>
                    <p style="color: #666; margin: 10px 0;">Full wrap custom design on quality BIC lighters</p>
                    <p style="color: #8BC34A; font-size: 20px; font-weight: bold; margin: 15px 0;">From $2.50</p>
                    <a href="https://www.munchmakers.com/product-category/custom-lighters/" style="display: block; text-align: center; background-color: #2C3E50; color: white; padding: 12px; text-decoration: none; border-radius: 5px;">View Options</a>
                </div>
            </div>

        </div>
    </div>

    <!-- CTA Section -->
    <div style="background: linear-gradient(135deg, #8BC34A, #689F38); border-radius: 15px; padding: 50px; text-align: center; color: white;">
        <h2 style="font-size: 36px; margin-bottom: 20px;">Ready to Get Started?</h2>
        <p style="font-size: 20px; margin-bottom: 30px;">Join hundreds of {page_config['name'].lower()} already using MunchMakers</p>
        <div>
            <a href="https://www.munchmakers.com/loyalty-program/" style="display: inline-block; background-color: white; color: #8BC34A; padding: 18px 40px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 600; margin: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">Request Free Mockup</a>
            <a href="tel:+16506403836" style="display: inline-block; background-color: transparent; color: white; padding: 18px 40px; text-decoration: none; border-radius: 50px; font-size: 18px; font-weight: 600; margin: 10px; border: 2px solid white;">Call: +1 650-640-3836</a>
        </div>
    </div>

    <!-- Footer Info -->
    <div style="margin-top: 50px; padding: 30px; background-color: #F8F9FA; border-radius: 15px; text-align: center;">
        <p style="color: #666; font-size: 16px; line-height: 1.8;">
            <strong>MunchMakers</strong> - Your trusted partner for custom cannabis accessories<br/>
            B2B Wholesale • No Minimum Orders Available • Fast Production • Free Design Services
        </p>
    </div>

</div>
'''

    return html

def create_bigcommerce_page(page_config, html_content):
    """Create a page in BigCommerce"""

    page_data = {
        "type": "page",
        "name": page_config['title'],
        "link": f"/for-{page_config['url']}/",
        "content": html_content,
        "is_visible": True,
        "parent_id": 0,
        "meta_description": page_config['description'],
        "search_keywords": f"{page_config['name']}, custom cannabis accessories, wholesale, B2B"
    }

    url = f'{api_base_url}/content/pages'

    response = requests.post(url, headers=bc_headers, json=page_data)

    if response.status_code == 201:
        return response.json()
    else:
        print(f"Error creating page for {page_config['name']}: {response.status_code} - {response.text}")
        return None

# Create all landing pages
for i, page_config in enumerate(landing_pages):
    print(f"\n{'='*60}")
    print(f"Creating landing page {i+1}/{len(landing_pages)}: {page_config['name']}")
    print(f"{'='*60}")

    # Generate image
    print(f"Generating image for {page_config['name']}...")
    image_base64 = generate_image(page_config['image_prompt'])

    if image_base64:
        print("✓ Image generated successfully")
    else:
        print("✗ Image generation failed, continuing without image")

    # Create HTML
    print(f"Creating HTML content...")
    html_content = create_landing_page_html(page_config, image_base64)

    # Save HTML locally for reference
    filename = f"landing_page_{page_config['url']}.html"
    with open(filename, 'w') as f:
        f.write(html_content)
    print(f"✓ HTML saved to {filename}")

    # Create page in BigCommerce
    print(f"Creating page in BigCommerce...")
    result = create_bigcommerce_page(page_config, html_content)

    if result:
        print(f"✓ Page created successfully: {result['data']['url']}")
    else:
        print("✗ Failed to create page in BigCommerce")

    # Rate limiting
    time.sleep(2)

print("\n" + "="*60)
print("ALL LANDING PAGES CREATED!")
print("="*60)
print("\nPages should be accessible at:")
for page in landing_pages:
    print(f"  - https://www.munchmakers.com/for-{page['url']}/")
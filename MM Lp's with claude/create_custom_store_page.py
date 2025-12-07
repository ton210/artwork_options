import requests
import json

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Headers for BigCommerce API requests
bc_headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# HTML Content for WYSIWYG (no header/footer, just the main content)
html_content = '''
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333;">

    <!-- Hero Section -->
    <section style="background: linear-gradient(135deg, #2c5f2d 0%, #4CAF50 100%); color: white; padding: 80px 40px; text-align: center; border-radius: 10px; margin-bottom: 40px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h1 style="font-size: 48px; margin-bottom: 20px; font-weight: 700;">Build Your Custom Smoke Accessories Store</h1>
            <p style="font-size: 22px; margin-bottom: 30px; opacity: 0.95;">Launch a fully-branded merchandise store with custom smoke accessories, complete control, and zero inventory hassle</p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; margin-top: 30px;">
                <a href="#contact" style="background: #fff; color: #2c5f2d; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block;">Get Started</a>
                <a href="#example" style="background: transparent; color: white; border: 2px solid white; padding: 12px 30px; border-radius: 5px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block;">See Example Store</a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section style="padding: 60px 20px; background: #f9f9f9; border-radius: 10px; margin-bottom: 40px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; font-size: 36px; margin-bottom: 50px; color: #2c5f2d;">How It Works</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px; margin-top: 40px;">

                <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <div style="width: 60px; height: 60px; background: #4CAF50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin: 0 auto 20px;">1</div>
                    <h3 style="font-size: 24px; margin-bottom: 15px; color: #2c5f2d;">Choose Your Brand</h3>
                    <p style="font-size: 16px; color: #666;">Work with us to design your custom smoke accessories with your branding. From grinders to rolling trays, we'll create your perfect product line.</p>
                </div>

                <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <div style="width: 60px; height: 60px; background: #4CAF50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin: 0 auto 20px;">2</div>
                    <h3 style="font-size: 24px; margin-bottom: 15px; color: #2c5f2d;">We Build Your Store</h3>
                    <p style="font-size: 16px; color: #666;">Get a fully-functional, custom-built online store with your branding. Complete with admin portal, inventory tracking, and payment processing.</p>
                </div>

                <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <div style="width: 60px; height: 60px; background: #4CAF50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin: 0 auto 20px;">3</div>
                    <h3 style="font-size: 24px; margin-bottom: 15px; color: #2c5f2d;">Launch & Manage</h3>
                    <p style="font-size: 16px; color: #666;">Take control with your admin dashboard. Add products, track inventory, manage orders, and watch your brand grow. We handle fulfillment and dropshipping.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section style="padding: 60px 20px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; font-size: 36px; margin-bottom: 50px; color: #2c5f2d;">Everything You Need to Succeed</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-top: 40px;">

                <div style="padding: 30px; border-left: 4px solid #4CAF50; background: #f9f9f9;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50; font-size: 24px;">✓</span> Custom Branded Products</h3>
                    <p style="color: #666; font-size: 16px;">Create your own line of smoke accessories with your branding. From grinders and rolling trays to storage containers and more.</p>
                </div>

                <div style="padding: 30px; border-left: 4px solid #4CAF50; background: #f9f9f9;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50; font-size: 24px;">✓</span> Full Admin Control</h3>
                    <p style="color: #666; font-size: 16px;">Powerful back-end portal gives you complete control. Manage products, pricing, inventory, orders, and customers with ease.</p>
                </div>

                <div style="padding: 30px; border-left: 4px solid #4CAF50; background: #f9f9f9;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50; font-size: 24px;">✓</span> Dropshipping Included</h3>
                    <p style="color: #666; font-size: 16px;">Zero inventory stress. We handle storage, packing, and shipping. Focus on your brand while we handle logistics.</p>
                </div>

                <div style="padding: 30px; border-left: 4px solid #4CAF50; background: #f9f9f9;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50; font-size: 24px;">✓</span> Inventory Tracking</h3>
                    <p style="color: #666; font-size: 16px;">Real-time inventory management. Track stock levels, set low-stock alerts, and never oversell products.</p>
                </div>

                <div style="padding: 30px; border-left: 4px solid #4CAF50; background: #f9f9f9;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50; font-size: 24px;">✓</span> Add Any Products</h3>
                    <p style="color: #666; font-size: 16px;">Not limited to smoke accessories. Add apparel, stickers, or any other merchandise to complement your brand.</p>
                </div>

                <div style="padding: 30px; border-left: 4px solid #4CAF50; background: #f9f9f9;">
                    <h3 style="font-size: 22px; margin-bottom: 15px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50; font-size: 24px;">✓</span> Complete Customization</h3>
                    <p style="color: #666; font-size: 16px;">Your store, your way. Customize design, layout, colors, and functionality to match your brand identity perfectly.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Example Store Section -->
    <section id="example" style="padding: 60px 20px; background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%); color: white; border-radius: 10px; margin-bottom: 40px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; font-size: 36px; margin-bottom: 20px; color: white;">See It In Action</h2>
            <p style="text-align: center; font-size: 18px; margin-bottom: 40px;">
                Check out a live example: <a href="https://bluntslutsmerch.munchmakers.com/" target="_blank" style="color: #4CAF50; font-weight: bold; text-decoration: underline;">bluntslutsmerch.munchmakers.com</a>
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 40px; margin-top: 40px;">

                <div style="text-align: center;">
                    <h4 style="margin-bottom: 20px; font-size: 22px; color: #4CAF50;">Desktop Experience</h4>
                    <div style="background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); min-height: 300px; display: flex; align-items: center; justify-content: center; color: #666; font-style: italic;">
                        Full-featured store with admin dashboard<br>
                        Visit: bluntslutsmerch.munchmakers.com
                    </div>
                </div>

                <div style="text-align: center;">
                    <h4 style="margin-bottom: 20px; font-size: 22px; color: #4CAF50;">Mobile Optimized</h4>
                    <div style="background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); min-height: 300px; display: flex; align-items: center; justify-content: center; color: #666; font-style: italic;">
                        Fully responsive design<br>
                        Perfect mobile shopping experience
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section style="padding: 60px 20px; background: #f9f9f9; border-radius: 10px; margin-bottom: 40px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; font-size: 36px; margin-bottom: 50px; color: #2c5f2d;">Why Choose MunchMakers?</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px;">

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 20px; margin-bottom: 10px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50;">✓</span> No Upfront Inventory</h4>
                    <p style="color: #666;">Start selling without buying bulk inventory. We manufacture and ship as orders come in.</p>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 20px; margin-bottom: 10px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50;">✓</span> Expert Support</h4>
                    <p style="color: #666;">Our team handles product creation, quality control, and customer service for your store's orders.</p>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 20px; margin-bottom: 10px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50;">✓</span> Fast Turnaround</h4>
                    <p style="color: #666;">Quick production and shipping times keep your customers happy and coming back for more.</p>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 20px; margin-bottom: 10px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50;">✓</span> Scalable Solution</h4>
                    <p style="color: #666;">Start small and grow. Our platform scales with your brand from first sale to thousands of orders.</p>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 20px; margin-bottom: 10px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50;">✓</span> Brand Building</h4>
                    <p style="color: #666;">Create lasting brand loyalty with quality merchandise that customers use and see every day.</p>
                </div>

                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h4 style="font-size: 20px; margin-bottom: 10px; color: #2c5f2d; display: flex; align-items: center; gap: 10px;"><span style="color: #4CAF50;">✓</span> Revenue Stream</h4>
                    <p style="color: #666;">Add a new revenue channel to your brand without operational overhead or inventory risk.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section id="contact" style="padding: 60px 40px; background: linear-gradient(135deg, #2c5f2d 0%, #4CAF50 100%); color: white; text-align: center; border-radius: 10px;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2 style="font-size: 42px; margin-bottom: 20px;">Ready to Build Your Store?</h2>
            <p style="font-size: 20px; margin-bottom: 40px; opacity: 0.95;">Let's create something amazing together. Reach out to learn more about how MunchMakers can help grow your brand.</p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="mailto:info@munchmakers.com" style="background: white; color: #2c5f2d; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block;">Email Us</a>
                <a href="https://www.munchmakers.com/contact-us/" style="background: transparent; color: white; border: 2px solid white; padding: 12px 30px; border-radius: 5px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block;">Contact Form</a>
            </div>
        </div>
    </section>

</div>
'''

def create_bigcommerce_page():
    """Create the Custom Store landing page in BigCommerce"""

    page_data = {
        "type": "page",
        "name": "Custom Merchandise Store",
        "body": html_content,
        "is_visible": False,  # Hidden page as requested
        "parent_id": 0,
        "sort_order": 110,
        "meta_description": "Launch your own fully-branded custom smoke accessories store with MunchMakers. We handle dropshipping, provide admin portal, inventory tracking, and complete control. Build your brand today.",
        "search_keywords": "custom merchandise store, branded smoke accessories, dropship store, custom grinders, admin portal, inventory tracking"
    }

    url = f'{api_base_url}/content/pages'

    print("Creating Custom Store landing page in BigCommerce...")
    print(f"URL will be: https://www.munchmakers.com/custom-merchandise-store/")
    print(f"Visible: False (hidden)")
    print()

    response = requests.post(url, headers=bc_headers, json=page_data)

    if response.status_code == 201:
        result = response.json()
        print("✓ Page created successfully!")
        print(f"  Page ID: {result['data']['id']}")
        print(f"  Page URL: {result['data']['url']}")
        print(f"  Name: {result['data']['name']}")
        print(f"  Visible: {result['data']['is_visible']}")
        return result
    else:
        print(f"✗ Error creating page: {response.status_code}")
        print(f"Response: {response.text}")
        return None

if __name__ == "__main__":
    print("="*60)
    print("CREATING CUSTOM STORE LANDING PAGE")
    print("="*60)
    print()

    result = create_bigcommerce_page()

    print()
    print("="*60)
    if result:
        print("SUCCESS!")
        print("="*60)
        print()
        print("Page Details:")
        print(f"  - URL: https://www.munchmakers.com/custom-merchandise-store/")
        print(f"  - Status: Hidden (is_visible = False)")
        print(f"  - You can make it visible later in BigCommerce admin")
    else:
        print("FAILED - Please check the error above")
        print("="*60)

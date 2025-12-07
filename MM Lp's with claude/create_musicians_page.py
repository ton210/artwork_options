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

# HTML content for Musicians & Artists landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            The Merch That <span style="color: #4ade80;">Pays for Your Tour</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            70% profit margins. Fans use them daily. Become tour collectibles.
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🎸 Join 200+ Artists</strong> earning $15,000+ per tour from custom grinders
            </p>
        </div>
    </div>
</div>

<!-- Profit Comparison Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Stop Leaving Money at the Merch Table
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <!-- Traditional Merch Column -->
            <div style="flex: 1; min-width: 300px; background: #f5f5f5; padding: 30px; border-radius: 12px;">
                <h3 style="color: #666; margin: 0 0 20px 0;">Traditional Merch</h3>
                <div style="font-size: 48px; color: #dc2626; font-weight: 900; margin: 20px 0;">20%</div>
                <p style="color: #666; margin: 10px 0;">Average Profit Margin</p>
                <ul style="text-align: left; color: #666; margin: 20px 0;">
                    <li>T-Shirt: $5 profit on $25 sale</li>
                    <li>Sticker Pack: $2 profit on $10 sale</li>
                    <li>Poster: $3 profit on $15 sale</li>
                </ul>
                <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-top: 20px;">
                    Worn once, forgotten forever
                </div>
            </div>

            <!-- Custom Grinders Column -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 30px; border-radius: 12px; border: 2px solid #4ade80;">
                <h3 style="color: #14532d; margin: 0 0 20px 0;">Custom Grinders</h3>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900; margin: 20px 0;">73%</div>
                <p style="color: #14532d; margin: 10px 0;">Average Profit Margin</p>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; font-weight: 600;">
                    <li>Standard: $36 profit on $49 sale</li>
                    <li>Tour Edition: $58 profit on $79 sale</li>
                    <li>VIP Package: $73 profit on $99 sale</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 10px; border-radius: 8px; margin-top: 20px; font-weight: 600;">
                    Used daily, kept forever
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROI Calculator Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Tour Revenue Calculator
        </h2>

        <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="text-align: left; margin-bottom: 30px;">
                <h3 style="color: #1a1a1a; margin: 0 0 20px 0;">Average 20-Date Tour:</h3>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Grinders sold per show:</span>
                    <strong style="color: #1a1a1a;">25 units</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Average price:</span>
                    <strong style="color: #1a1a1a;">$60</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Your profit per unit:</span>
                    <strong style="color: #16a34a;">$44</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Profit per show:</span>
                    <strong style="color: #16a34a;">$1,100</strong>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 25px; border-radius: 12px; margin-top: 20px;">
                <div style="font-size: 20px; color: #14532d; margin-bottom: 10px;">Total Tour Profit:</div>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$22,000</div>
                <div style="font-size: 16px; color: #14532d; margin-top: 10px;">
                    That's 3x more than traditional merch profits
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Artists Making Bank with Custom Grinders
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <div style="font-size: 60px; margin-bottom: 20px;">🎤</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Hip-Hop Artist</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$31,000</div>
                <p style="color: #666; font-size: 14px;">30-date tour • Limited edition designs</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Fans lined up for the tour exclusive colorways. Sold out every night."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <div style="font-size: 60px; margin-bottom: 20px;">🎸</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Rock Band</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$18,500</div>
                <p style="color: #666; font-size: 14px;">15-date tour • VIP package add-on</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Added $75 grinders to VIP packages. 90% uptake rate."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <div style="font-size: 60px; margin-bottom: 20px;">🎵</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">EDM Producer</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$42,000</div>
                <p style="color: #666; font-size: 14px;">Festival season • Glow-in-dark edition</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"The LED light-up grinders were the talk of every festival."</em>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Why Grinders Work Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; font-weight: 800;">
            Why Grinders <span style="color: #4ade80;">Outsell Everything</span>
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    💰
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Premium Price Point</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Fans happily pay $50-100 for functional art they use daily. Compare that to a $25 t-shirt they wear once.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    🎯
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Perfect Audience Fit</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    68% of concert-goers 21-35 use cannabis products. You're selling exactly what your fans already want.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    ⭐
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Instant Collectible Status</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Tour-exclusive designs become valuable collectibles. Fans trade and resell for 2-3x original price.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Product Options Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Design Options That Drive Sales
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Standard Edition -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Standard Edition</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">$49 <span style="font-size: 16px; color: #666; font-weight: 400;">retail</span></div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Your logo laser engraved</li>
                    <li style="padding: 8px 0;">✓ Aircraft-grade aluminum</li>
                    <li style="padding: 8px 0;">✓ 5 color options</li>
                    <li style="padding: 8px 0;">✓ No minimum order</li>
                    <li style="padding: 8px 0;">✓ 5-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong style="color: #16a34a;">Your profit: $36/unit</strong>
                </div>
            </div>

            <!-- Tour Exclusive -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    BEST SELLER
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Tour Exclusive</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">$79 <span style="font-size: 16px; color: #666; font-weight: 400;">retail</span></div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Custom tour artwork</li>
                    <li style="padding: 8px 0;">✓ Date & venue engraving</li>
                    <li style="padding: 8px 0;">✓ Limited edition packaging</li>
                    <li style="padding: 8px 0;">✓ Numbered series</li>
                    <li style="padding: 8px 0;">✓ Certificate of authenticity</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Your profit: $58/unit</strong>
                </div>
            </div>

            <!-- VIP Package -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">VIP Package Add-On</h3>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">$99 <span style="font-size: 16px; color: #666; font-weight: 400;">retail</span></div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Artist signature engraving</li>
                    <li style="padding: 8px 0;">✓ Backstage pass design</li>
                    <li style="padding: 8px 0;">✓ Premium gift box</li>
                    <li style="padding: 8px 0;">✓ Meet & greet exclusive</li>
                    <li style="padding: 8px 0;">✓ Lifetime warranty</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Your profit: $73/unit</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            From Design to Sold Out in 5 Days
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    1
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Send Your Artwork</h3>
                <p style="color: #666;">
                    Logo, tour dates, album art - our designers make it pop on aluminum
                </p>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    2
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Approve Mock-ups</h3>
                <p style="color: #666;">
                    See exactly how your grinders will look. Unlimited revisions included
                </p>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    3
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Production Begins</h3>
                <p style="color: #666;">
                    5-day turnaround. Ship to venue or your warehouse
                </p>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div style="background: #4ade80; color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 900;">
                    4
                </div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Cash In</h3>
                <p style="color: #666;">
                    Watch fans line up. Reorder anytime with 1-click
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Social Proof Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Numbers Don't Lie
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">200+</div>
                <p style="color: #666; margin: 10px 0;">Artists & Bands</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$2.8M</div>
                <p style="color: #666; margin: 10px 0;">Tour Profits Generated</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">73%</div>
                <p style="color: #666; margin: 10px 0;">Average Profit Margin</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">5 Days</div>
                <p style="color: #666; margin: 10px 0;">From Order to Stage</p>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Common Questions
        </h2>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What's the minimum order?</h3>
            <p style="color: #666; margin: 0;">
                No minimums! Order 10 or 1,000. Most artists start with 50-100 for their first tour, then reorder based on demand.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can you ship to different venues?</h3>
            <p style="color: #666; margin: 0;">
                Absolutely. We'll split your order and ship to each venue ahead of your arrival. Many artists have us ship 20-30 units per stop.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What about venue restrictions?</h3>
            <p style="color: #666; margin: 0;">
                Grinders are legal accessories in all 50 states. They're no different than selling t-shirts or posters. We provide venue-friendly packaging.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">How fast can I reorder?</h3>
            <p style="color: #666; margin: 0;">
                Same 5-day turnaround on all reorders. Most artists keep a buffer stock and reorder when they hit 100 units remaining.
            </p>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Your Next Tour Deserves Better Merch
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 200+ artists earning real money from merchandise that matters
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Tour Profit Projection</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Free mockups • Pricing calculator • Tour logistics planning
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Start Your Design →
            </a>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🎸 Special tour pricing available for 20+ date tours
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ No minimums</div>
            <div>✓ 5-day production</div>
            <div>✓ Ship to venues</div>
            <div>✓ Lifetime warranty</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Custom Grinders for Musicians & Artists - Tour Merch",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 200,
    "meta_description": "Earn 70% profit margins on tour merch. Custom grinders outsell traditional merchandise 3:1. No minimums, 5-day production. Join 200+ artists.",
    "search_keywords": "band merch, tour merchandise, custom grinders musicians, artist merchandise, tour merch wholesale"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ MUSICIANS & ARTISTS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🎸 Page Features:")
    print(f"   • 70% profit margin messaging")
    print(f"   • Tour revenue calculator ($22,000 per tour)")
    print(f"   • Success stories with real numbers")
    print(f"   • Comparison with traditional merch")
    print(f"   • Three pricing tiers (Standard/Tour/VIP)")
    print(f"\n🎯 Target Audience:")
    print(f"   • Touring musicians and bands")
    print(f"   • Artists looking for high-margin merch")
    print(f"   • Tour managers seeking revenue optimization")
    print(f"\n📊 Key Selling Points:")
    print(f"   • No minimums")
    print(f"   • 5-day production")
    print(f"   • Ship to individual venues")
    print(f"   • Tour-exclusive designs become collectibles")
    print(f"\n💡 Note: Images can be added later when Freepik API is available")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
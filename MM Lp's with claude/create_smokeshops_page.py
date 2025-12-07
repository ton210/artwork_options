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

# HTML content for Smoke Shops & Head Shops landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Finally. <span style="color: #4ade80;">65% Margins</span> That Never Break
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Custom grinders outsell glass 3:1. Zero breakage. Exclusive to your shop.
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>💰 Join 1,200+ Shops</strong> averaging $3,800/month in grinder profits
            </p>
        </div>
    </div>
</div>

<!-- Problem/Solution Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Current Inventory Reality Check
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <!-- Glass Problems -->
            <div style="flex: 1; min-width: 300px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <h3 style="color: #991b1b; margin: 0 0 20px 0;">Glass Pieces</h3>
                <div style="font-size: 48px; color: #dc2626; font-weight: 900; margin: 20px 0;">35%</div>
                <p style="color: #991b1b; margin: 10px 0; font-weight: 600;">Average Margin</p>
                <ul style="text-align: left; color: #7f1d1d; margin: 20px 0;">
                    <li>10-15% breakage loss</li>
                    <li>$500+ per piece inventory cost</li>
                    <li>Slow turnover (45+ days)</li>
                    <li>Same brands as every shop</li>
                    <li>Storage & display challenges</li>
                </ul>
                <div style="background: #991b1b; color: white; padding: 10px; border-radius: 8px; margin-top: 20px;">
                    High risk, low reward
                </div>
            </div>

            <!-- Grinder Solution -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 30px; border-radius: 12px; border: 2px solid #4ade80;">
                <h3 style="color: #14532d; margin: 0 0 20px 0;">Custom Grinders</h3>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900; margin: 20px 0;">65%</div>
                <p style="color: #14532d; margin: 10px 0; font-weight: 600;">Average Margin</p>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; font-weight: 600;">
                    <li>0% breakage - aluminum construction</li>
                    <li>$15 wholesale, $45 retail</li>
                    <li>Fast turnover (8 days average)</li>
                    <li>Exclusive to your shop</li>
                    <li>Countertop display included</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 10px; border-radius: 8px; margin-top: 20px; font-weight: 600;">
                    Low risk, high reward
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Calculator Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Shop's Profit Potential
        </h2>

        <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="text-align: left; margin-bottom: 30px;">
                <h3 style="color: #1a1a1a; margin: 0 0 20px 0;">Average Smoke Shop Performance:</h3>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Grinders sold per day:</span>
                    <strong style="color: #1a1a1a;">4 units</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Your retail price:</span>
                    <strong style="color: #1a1a1a;">$45</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Your cost:</span>
                    <strong style="color: #1a1a1a;">$15</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Profit per unit:</span>
                    <strong style="color: #16a34a;">$30</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Daily profit:</span>
                    <strong style="color: #16a34a;">$120</strong>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 25px; border-radius: 12px; margin-top: 20px;">
                <div style="font-size: 20px; color: #14532d; margin-bottom: 10px;">Monthly Profit Addition:</div>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$3,600</div>
                <div style="font-size: 16px; color: #14532d; margin-top: 10px;">
                    That's an extra $43,200 per year
                </div>
            </div>

            <div style="background: #fef3c7; padding: 20px; border-radius: 12px; margin-top: 20px;">
                <p style="margin: 0; color: #92400e; font-weight: 600;">
                    💡 Top shops sell 8-10 units/day by placing grinders at checkout
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Real Shops, Real Numbers
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Miami Beach Shop</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$5,400/mo</div>
                <p style="color: #666; font-size: 14px;">180 units • Checkout display</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Replaced our Chinese grinders with custom ones. Tripled our grinder sales in 30 days."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Denver Chain (3 locations)</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$12,800/mo</div>
                <p style="color: #666; font-size: 14px;">425 units • Exclusive designs</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Our shop logo grinders became collectibles. Customers buy multiple colors."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Brooklyn Boutique</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$3,200/mo</div>
                <p style="color: #666; font-size: 14px;">105 units • Limited editions</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Monthly limited drops create urgency. Customers come back just for new designs."</em>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Why Grinders Outsell Everything Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; font-weight: 800;">
            Why Grinders <span style="color: #4ade80;">Dominate Your Sales</span>
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    🎯
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Perfect Add-On Item</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Every flower purchase needs a grinder. It's the easiest upsell in your shop. "You'll need a grinder with that."
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    💎
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Premium Feel, Mid-Range Price</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Aircraft-grade aluminum feels expensive but costs less than a decent pipe. Customers perceive huge value.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    🔄
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Repeat Purchase Driver</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Customers collect different colors and designs. Average customer buys 2.7 grinders per year.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Product Options Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Wholesale Pricing That Makes Sense
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Starter Pack -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Starter Pack</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">24 units minimum</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $18<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Your shop logo engraved</li>
                    <li style="padding: 8px 0;">✓ 5 color options</li>
                    <li style="padding: 8px 0;">✓ Countertop display</li>
                    <li style="padding: 8px 0;">✓ 5-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Suggested retail: $49</strong><br>
                    <span style="color: #16a34a;">Your profit: $31/unit</span>
                </div>
            </div>

            <!-- Best Value -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Shop Exclusive</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">48 units minimum</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $15<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Custom shop design</li>
                    <li style="padding: 8px 0;">✓ Exclusive territory rights</li>
                    <li style="padding: 8px 0;">✓ 10 color options</li>
                    <li style="padding: 8px 0;">✓ Premium display case</li>
                    <li style="padding: 8px 0;">✓ Marketing materials</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Suggested retail: $45</strong><br>
                    Your profit: $30/unit
                </div>
            </div>

            <!-- Volume Dealer -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Volume Dealer</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">100+ units</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $12<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Multiple designs</li>
                    <li style="padding: 8px 0;">✓ Regional exclusive</li>
                    <li style="padding: 8px 0;">✓ Custom packaging</li>
                    <li style="padding: 8px 0;">✓ Quarterly new releases</li>
                    <li style="padding: 8px 0;">✓ Co-op marketing fund</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Suggested retail: $39-45</strong><br>
                    Your profit: $27-33/unit
                </div>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #666;">
                <strong style="color: #1a1a1a;">🚚 Free shipping</strong> on orders over $500 •
                <strong style="color: #1a1a1a;">Net 30 terms</strong> available for established shops
            </p>
        </div>
    </div>
</div>

<!-- Display & Marketing Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Everything You Need to Sell More
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🏪</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Premium Display Unit</h3>
                <p style="color: #666;">
                    Rotating countertop display holds 24 units. LED lighting available. Increases impulse buys by 40%.
                </p>
            </div>

            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">📱</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Social Media Kit</h3>
                <p style="color: #666;">
                    Professional photos, videos, and posts ready to share. Monthly content calendar included.
                </p>
            </div>

            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🏷️</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Smart Pricing Tags</h3>
                <p style="color: #666;">
                    QR codes link to product videos. Shows quality comparisons. Highlights your shop exclusive.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Territory Protection Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Be the Only Shop in Town
        </h2>

        <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 40px; border-radius: 16px; border: 2px solid #4ade80;">
            <h3 style="font-size: 28px; margin: 0 0 20px 0; color: #14532d;">Territory Protection Available</h3>
            <p style="font-size: 18px; color: #14532d; margin: 0 0 30px 0; line-height: 1.6;">
                Lock in exclusive rights to your custom designs within a 3-mile radius. No other shop can order your designs.
            </p>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
                <div style="background: white; padding: 20px; border-radius: 12px; flex: 1; min-width: 200px;">
                    <div style="font-size: 32px; color: #16a34a; font-weight: 900;">3 Mile</div>
                    <p style="color: #666; margin: 10px 0 0 0;">Exclusive Zone</p>
                </div>
                <div style="background: white; padding: 20px; border-radius: 12px; flex: 1; min-width: 200px;">
                    <div style="font-size: 32px; color: #16a34a; font-weight: 900;">$0</div>
                    <p style="color: #666; margin: 10px 0 0 0;">Territory Fee</p>
                </div>
                <div style="background: white; padding: 20px; border-radius: 12px; flex: 1; min-width: 200px;">
                    <div style="font-size: 32px; color: #16a34a; font-weight: 900;">48 Units</div>
                    <p style="color: #666; margin: 10px 0 0 0;">Minimum Order</p>
                </div>
            </div>
        </div>

        <p style="margin: 30px 0 0 0; color: #666; font-size: 16px;">
            ⚡ Limited territories available. First shop to order locks the area.
        </p>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Quick Answers
        </h2>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">How fast can I restock?</h3>
            <p style="color: #666; margin: 0;">
                5-day turnaround on all reorders. Most shops reorder every 2-3 weeks. We can also set up automatic monthly shipments.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What if they don't sell?</h3>
            <p style="color: #666; margin: 0;">
                Never happens, but we offer a 30-day swap program on your first order. Exchange unsold colors for different ones.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can I see samples first?</h3>
            <p style="color: #666; margin: 0;">
                Absolutely. We'll send you a sample pack with 5 different colors and finishes. Just cover $20 shipping.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Do you offer payment terms?</h3>
            <p style="color: #666; margin: 0;">
                First order is prepaid. After that, qualified shops get Net 30 terms. Most shops pay for reorders with profits from the previous order.
            </p>
        </div>
    </div>
</div>

<!-- Urgency Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <div style="background: #fef2f2; border: 2px solid #dc2626; padding: 40px; border-radius: 16px;">
            <h2 style="font-size: 32px; margin: 0 0 20px 0; color: #991b1b; font-weight: 800;">
                Your Competitors Are Already Selling Custom Grinders
            </h2>
            <p style="font-size: 18px; color: #7f1d1d; margin: 0 0 30px 0; line-height: 1.6;">
                Every day you wait, you're losing $120 in profit and sending customers to shops that offer exclusive items. Territory protection is first-come, first-served.
            </p>
            <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap;">
                <div>
                    <div style="font-size: 32px; color: #dc2626; font-weight: 900;">87%</div>
                    <p style="color: #991b1b; margin: 5px 0 0 0;">of territories claimed</p>
                </div>
                <div>
                    <div style="font-size: 32px; color: #dc2626; font-weight: 900;">$3,600</div>
                    <p style="color: #991b1b; margin: 5px 0 0 0;">lost each month waiting</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Start Earning 65% Margins Today
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 1,200+ smoke shops making real profit on grinders
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Sample Pack</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                5 grinders • See the quality • Test with customers
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Claim Your Territory →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                Just $20 shipping • Applied to first order
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            💰 Special: Order 48+ units, get exclusive territory FREE
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ 65% margins</div>
            <div>✓ No breakage</div>
            <div>✓ 5-day restock</div>
            <div>✓ Territory protection</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Wholesale Grinders for Smoke Shops - 65% Margins",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 250,
    "meta_description": "65% profit margins on custom grinders. No breakage, exclusive territory, 5-day restocking. Join 1,200+ smoke shops earning $3,600+ monthly.",
    "search_keywords": "wholesale grinders, smoke shop supplies, head shop wholesale, custom grinders wholesale, smoke shop grinders"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ SMOKE SHOPS & HEAD SHOPS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n💰 Page Features:")
    print(f"   • 65% profit margin focus")
    print(f"   • Glass vs grinder comparison")
    print(f"   • Monthly profit calculator ($3,600/month)")
    print(f"   • Territory protection emphasis")
    print(f"   • Three wholesale tiers")
    print(f"\n🎯 Target Audience:")
    print(f"   • Smoke shop owners")
    print(f"   • Head shop managers")
    print(f"   • Retail buyers")
    print(f"\n📊 Key Selling Points:")
    print(f"   • Zero breakage (vs glass)")
    print(f"   • Exclusive territory rights")
    print(f"   • 5-day restocking")
    print(f"   • Display unit included")
    print(f"   • Net 30 terms available")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
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

# HTML content for CBD Retailers landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Premium Wellness Accessories for <span style="color: #4ade80;">CBD Retailers</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Increase average orders by $75 with custom wellness accessories
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🌿 Join 400+ CBD Stores</strong> offering premium lifestyle accessories
            </p>
        </div>
    </div>
</div>

<!-- Market Opportunity Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The CBD Accessories Market is Exploding
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$2.8B</div>
                <p style="color: #666; margin: 10px 0;">CBD Accessories Market 2024</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">68%</div>
                <p style="color: #666; margin: 10px 0;">Customers Buy Accessories</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">3.2x</div>
                <p style="color: #666; margin: 10px 0;">Higher Customer LTV</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">45%</div>
                <p style="color: #666; margin: 10px 0;">Buy As Gifts</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">
                "Premium accessories position our CBD products as a wellness lifestyle, not just supplements."
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- Sarah Chen, Wellness CBD Boutique</p>
        </div>
    </div>
</div>

<!-- Product Bundle Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Wellness Bundles That Sell Themselves
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Daily Wellness Bundle -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Daily Wellness Kit</h3>
                <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #16a34a; font-weight: 900;">$89 <span style="font-size: 14px; font-weight: 400;">retail bundle</span></div>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Custom herb grinder</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ CBD dosage guide card</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">✓ Aromatherapy storage jar</li>
                    <li style="padding: 8px 0;">✓ Wellness journal insert</li>
                </ul>
                <div style="background: #f0fdf4; padding: 15px; border-radius: 8px;">
                    <p style="margin: 0; color: #14532d; font-weight: 600;">Your cost: $32 | Profit: $57</p>
                </div>
            </div>

            <!-- Sleep Wellness Bundle -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 30px; border-radius: 12px; border: 2px solid #3b82f6;">
                <div style="background: #3b82f6; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    BEST SELLER
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Sleep Wellness Bundle</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #3b82f6; font-weight: 900;">$129 <span style="font-size: 14px; font-weight: 400;">retail bundle</span></div>
                </div>
                <ul style="text-align: left; color: #1e3a8a; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0; border-bottom: 1px solid #dbeafe;">✓ Night-time grinder (glow)</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #dbeafe;">✓ Lavender storage container</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #dbeafe;">✓ Sleep tracking card</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #dbeafe;">✓ Bedside organizer tray</li>
                    <li style="padding: 8px 0;">✓ Premium gift box</li>
                </ul>
                <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 8px;">
                    <p style="margin: 0; font-weight: 600;">Your cost: $48 | Profit: $81</p>
                </div>
            </div>

            <!-- Premium Gift Set -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 12px; border: 2px solid #f59e0b;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Premium Gift Collection</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #f59e0b; font-weight: 900;">$199 <span style="font-size: 14px; font-weight: 400;">retail bundle</span></div>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0; border-bottom: 1px solid #fed7aa;">✓ Engraved premium grinder</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #fed7aa;">✓ Complete storage system</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #fed7aa;">✓ Wellness guide booklet</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #fed7aa;">✓ Essential oil samples</li>
                    <li style="padding: 8px 0; border-bottom: 1px solid #fed7aa;">✓ Luxury presentation box</li>
                    <li style="padding: 8px 0;">✓ Personalized message card</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <p style="margin: 0; font-weight: 600;">Your cost: $72 | Profit: $127</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROI Calculator Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Revenue Growth Calculator
        </h2>

        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 40px; border-radius: 16px; border: 2px solid #4ade80;">
            <div style="text-align: left; margin-bottom: 30px;">
                <h3 style="color: #14532d; margin: 0 0 20px 0;">With Premium Accessories:</h3>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #86efac;">
                    <span style="color: #14532d;">Average CBD order:</span>
                    <strong style="color: #14532d;">$85</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #86efac;">
                    <span style="color: #14532d;">+ Accessory bundle:</span>
                    <strong style="color: #16a34a;">$89</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #86efac;">
                    <span style="color: #14532d;">New average order:</span>
                    <strong style="color: #16a34a; font-size: 20px;">$174</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0;">
                    <span style="color: #14532d;">Bundle attachment rate:</span>
                    <strong style="color: #14532d;">42%</strong>
                </div>
            </div>

            <div style="background: white; padding: 25px; border-radius: 12px;">
                <div style="font-size: 20px; color: #666; margin-bottom: 10px;">Monthly Revenue Increase:</div>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">+$12,400</div>
                <div style="font-size: 16px; color: #666; margin-top: 10px;">
                    Based on 100 customers/month
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compliance & Education Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Compliant Accessories That Educate
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    ✓
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">50-State Legal</h3>
                <p style="color: #666; line-height: 1.6;">
                    All accessories are compliant with federal regulations. No THC references. Perfect for nationwide shipping.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    📚
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Educational Packaging</h3>
                <p style="color: #666; line-height: 1.6;">
                    Each item includes CBD education, dosage guidelines, and wellness tips. Position yourself as the expert.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🌿
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Wellness Focused</h3>
                <p style="color: #666; line-height: 1.6;">
                    Designs emphasize health, wellness, and natural living. No recreational imagery. Professional and approachable.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Gift Market Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Tap Into the $4.2B CBD Gift Market
        </h2>

        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 40px; border-radius: 16px; margin-bottom: 40px;">
            <h3 style="font-size: 28px; margin: 0 0 20px 0; color: #92400e;">Gift Season Revenue Boost</h3>
            <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap;">
                <div>
                    <div style="font-size: 36px; color: #f59e0b; font-weight: 900;">45%</div>
                    <p style="color: #92400e; margin: 5px 0 0 0;">of CBD purchases are gifts</p>
                </div>
                <div>
                    <div style="font-size: 36px; color: #f59e0b; font-weight: 900;">2.3x</div>
                    <p style="color: #92400e; margin: 5px 0 0 0;">higher value for gift sets</p>
                </div>
                <div>
                    <div style="font-size: 36px; color: #f59e0b; font-weight: 900;">67%</div>
                    <p style="color: #92400e; margin: 5px 0 0 0;">prefer pre-made bundles</p>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h4 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Mother's Day</h4>
                <p style="color: #666;">Wellness & self-care bundles. Average sale: $149</p>
            </div>
            <div style="flex: 1; min-width: 250px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h4 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Holiday Season</h4>
                <p style="color: #666;">Premium gift sets. Average sale: $189</p>
            </div>
            <div style="flex: 1; min-width: 250px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h4 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Valentine's Day</h4>
                <p style="color: #666;">Couples wellness kits. Average sale: $225</p>
            </div>
        </div>
    </div>
</div>

<!-- Customization Options Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Custom Branding That Builds Trust
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🎨</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Your Brand, Your Design</h3>
                <p style="color: #666;">
                    Custom engraving with your logo. Choose from wellness-focused designs. Match your brand aesthetic perfectly.
                </p>
            </div>

            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">📦</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Premium Packaging</h3>
                <p style="color: #666;">
                    Eco-friendly gift boxes. Custom tissue paper. Educational inserts. QR codes for dosage guides.
                </p>
            </div>

            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🏷️</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Private Label Program</h3>
                <p style="color: #666;">
                    Full white-label options. Your brand exclusively. Custom SKUs and barcodes. Dropship available.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            CBD Retailers Winning With Accessories
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px; background: #f0fdf4; padding: 30px; border-radius: 12px; border: 1px solid #86efac;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #14532d;">Natural Wellness Shop</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">+127%</div>
                <p style="color: #14532d; font-size: 14px; font-weight: 600;">Average order value increase</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"The wellness bundles transformed our business. Customers love the complete experience."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; background: #f0f9ff; padding: 30px; border-radius: 12px; border: 1px solid #93c5fd;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1e3a8a;">CBD Boutique Chain</h3>
                <div style="font-size: 32px; color: #3b82f6; font-weight: 900; margin: 10px 0;">$84,000</div>
                <p style="color: #1e3a8a; font-size: 14px; font-weight: 600;">Q4 accessory revenue</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Gift sets flew off the shelves. Best holiday season we've ever had."</em>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px; background: #fef3c7; padding: 30px; border-radius: 12px; border: 1px solid #fcd34d;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #92400e;">Online CBD Store</h3>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 10px 0;">3.2x</div>
                <p style="color: #92400e; font-size: 14px; font-weight: 600;">Customer lifetime value</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Accessories create repeat customers. They come back for new designs monthly."</em>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Wholesale Pricing for CBD Retailers
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Starter -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Starter Collection</h3>
                <div style="font-size: 16px; color: #666; margin: 10px 0;">Min order: 24 units</div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">Grinders: $16/unit (MSRP $45)</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">Storage jars: $8/unit (MSRP $22)</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">Gift boxes: $3/unit</li>
                    <li style="padding: 10px 0;">Bundle pricing available</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Average margin: 64%</strong>
                </div>
            </div>

            <!-- Premium -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    BEST VALUE
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Premium Partner</h3>
                <div style="font-size: 16px; color: #14532d; margin: 10px 0; font-weight: 600;">Min order: 48 units</div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #bbf7d0;">Grinders: $14/unit (MSRP $45)</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #bbf7d0;">Storage jars: $7/unit (MSRP $22)</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #bbf7d0;">Gift boxes: FREE</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #bbf7d0;">Custom branding included</li>
                    <li style="padding: 10px 0;">Dropship available</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Average margin: 69%</strong>
                </div>
            </div>

            <!-- Enterprise -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Enterprise</h3>
                <div style="font-size: 16px; color: #92400e; margin: 10px 0; font-weight: 600;">100+ units/month</div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #fed7aa;">Custom pricing</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fed7aa;">White label program</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fed7aa;">Exclusive designs</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fed7aa;">Marketing support</li>
                    <li style="padding: 10px 0;">Net 30 terms</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Margins up to 75%</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Common Questions
        </h2>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Are these products compliant for CBD retailers?</h3>
            <p style="color: #666; margin: 0;">
                Yes, 100% compliant in all 50 states. No THC references, no drug paraphernalia imagery. Positioned as wellness lifestyle accessories.
            </p>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can I create custom bundles?</h3>
            <p style="color: #666; margin: 0;">
                Absolutely! We'll help you design bundles specific to your customer base. Most successful retailers offer 3-5 different bundle options.
            </p>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Do you offer dropshipping?</h3>
            <p style="color: #666; margin: 0;">
                Yes, for Premium Partners and above. We handle fulfillment directly to your customers with your branding. Perfect for online CBD stores.
            </p>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What about seasonal designs?</h3>
            <p style="color: #666; margin: 0;">
                We release quarterly seasonal collections and holiday-specific designs. Mother's Day and holiday season bundles are huge sellers.
            </p>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Elevate Your CBD Business Today
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 400+ CBD retailers offering premium wellness accessories
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Wellness Bundle Samples</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                3 complete bundles • Test with customers • See the quality
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Request Sample Kit →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                $35 shipping • Credit applied to first order
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🌿 Special: Free custom branding on orders of 48+ units
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ 50-state compliant</div>
            <div>✓ Wellness focused</div>
            <div>✓ 69% margins</div>
            <div>✓ Dropship available</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Premium CBD Accessories - Wellness Bundles That Sell",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 300,
    "meta_description": "Increase CBD sales by $75 per order with premium wellness accessories. Compliant in 50 states, 69% margins, dropship available. Join 400+ CBD retailers.",
    "search_keywords": "CBD accessories, wellness bundles, CBD retail supplies, hemp accessories, CBD gift sets"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ CBD RETAILERS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🌿 Page Features:")
    print(f"   • Wellness-focused messaging")
    print(f"   • Three bundle tiers ($89/$129/$199)")
    print(f"   • Revenue calculator (+$12,400/month)")
    print(f"   • Gift market emphasis (45% of purchases)")
    print(f"   • Compliance and education focus")
    print(f"\n🎯 Target Audience:")
    print(f"   • CBD boutique owners")
    print(f"   • Wellness store managers")
    print(f"   • Online CBD retailers")
    print(f"\n📊 Key Selling Points:")
    print(f"   • 50-state compliant")
    print(f"   • Educational packaging")
    print(f"   • 69% profit margins")
    print(f"   • Dropship available")
    print(f"   • Seasonal collections")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
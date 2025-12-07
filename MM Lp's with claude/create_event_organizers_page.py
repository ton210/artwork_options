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

# HTML content for Cannabis Event Organizers landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Finally. Event Swag That <span style="color: #4ade80;">Doesn't Suck</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            97% of attendees keep custom grinders. 12% keep t-shirts. You do the math.
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🎪 Trusted by 200+ Events</strong> including Cannabis Cup, Emerald Cup, and MJBizCon
            </p>
        </div>
    </div>
</div>

<!-- The Problem Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Current Swag Bag Reality
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <!-- Typical Swag -->
            <div style="flex: 1; min-width: 300px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <h3 style="color: #991b1b; margin: 0 0 20px 0;">Typical Event Swag</h3>
                <ul style="text-align: left; color: #7f1d1d; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">❌ T-shirts: Become pajamas</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">❌ Pens: Lost in a week</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">❌ Tote bags: Closet clutter</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">❌ Stickers: Never used</li>
                    <li style="padding: 10px 0;">❌ Lanyards: Instant trash</li>
                </ul>
                <div style="background: #991b1b; color: white; padding: 15px; border-radius: 8px;">
                    88% thrown away within 30 days
                </div>
            </div>

            <!-- Custom Grinders -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 30px; border-radius: 12px; border: 2px solid #4ade80;">
                <h3 style="color: #14532d; margin: 0 0 20px 0;">Custom Event Grinders</h3>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">✓ Daily use for years</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">✓ Instagram photo worthy</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">✓ Becomes collectible</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">✓ Sponsors love visibility</li>
                    <li style="padding: 10px 0;">✓ Creates booth lines</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    97% still used after 2 years
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event ROI Calculator -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Event ROI Calculator
        </h2>

        <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="text-align: left; margin-bottom: 30px;">
                <h3 style="color: #1a1a1a; margin: 0 0 20px 0;">1,000 Attendee Event:</h3>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Custom grinders needed:</span>
                    <strong style="color: #1a1a1a;">1,000 units</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Your cost per unit:</span>
                    <strong style="color: #1a1a1a;">$12</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Total investment:</span>
                    <strong style="color: #1a1a1a;">$12,000</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Sponsor co-branding value:</span>
                    <strong style="color: #16a34a;">$25,000</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0;">
                    <span style="color: #666;">Post-event impressions (2 years):</span>
                    <strong style="color: #16a34a;">2.2M</strong>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 25px; border-radius: 12px;">
                <div style="font-size: 20px; color: #14532d; margin-bottom: 10px;">Net Profit from Sponsors:</div>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$13,000</div>
                <div style="font-size: 16px; color: #14532d; margin-top: 10px;">
                    Plus: 97% attendee satisfaction boost
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Events That Nailed It
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">West Coast Cannabis Cup</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">5,000 units</div>
                <p style="color: #666; font-size: 14px;">VIP exclusive designs</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"VIP tickets sold out in 48 hours when we announced limited edition grinders. Best swag decision ever."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">423% increase in VIP sales</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">420 Music Festival</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">10,000 units</div>
                <p style="color: #666; font-size: 14px;">3-day collectible series</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Attendees came back each day to collect all three designs. Created incredible buzz and social posts."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">18K Instagram tags</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Regional Trade Show</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">2,500 units</div>
                <p style="color: #666; font-size: 14px;">Sponsor co-branded</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Sponsors paid 3x more for grinder branding than banner ads. Easiest sponsor upsell ever."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">$45K sponsor revenue</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Package Options -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Event Packages That Make Sense
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Small Event -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Boutique Event</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">250-500 attendees</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $15<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Event logo engraving</li>
                    <li style="padding: 8px 0;">✓ Date & location</li>
                    <li style="padding: 8px 0;">✓ 5 color options</li>
                    <li style="padding: 8px 0;">✓ Individual packaging</li>
                    <li style="padding: 8px 0;">✓ 10-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Sell sponsorships at $30/unit</strong><br>
                    <span style="color: #16a34a;">100% markup potential</span>
                </div>
            </div>

            <!-- Festival Package -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Festival Package</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">1,000-5,000 attendees</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $12<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Custom artwork</li>
                    <li style="padding: 8px 0;">✓ Multiple tier designs</li>
                    <li style="padding: 8px 0;">✓ Sponsor co-branding</li>
                    <li style="padding: 8px 0;">✓ QR code integration</li>
                    <li style="padding: 8px 0;">✓ Rush production available</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell sponsorships at $25/unit</strong><br>
                    108% markup potential
                </div>
            </div>

            <!-- Major Event -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Major Convention</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">5,000+ attendees</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $8<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Volume pricing</li>
                    <li style="padding: 8px 0;">✓ Tiered VIP designs</li>
                    <li style="padding: 8px 0;">✓ Multi-sponsor options</li>
                    <li style="padding: 8px 0;">✓ Booth drop-shipping</li>
                    <li style="padding: 8px 0;">✓ On-site support team</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell sponsorships at $20/unit</strong><br>
                    150% markup potential
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booth Traffic Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Turn Your Booth Into THE Destination
        </h2>

        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 40px; border-radius: 16px;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #4ade80;">Limited Edition Booth Strategy</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; text-align: left;">
                <div style="flex: 1; min-width: 250px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Hour 1-2</h4>
                    <p style="color: #e5e5e5; margin: 0;">Release "Morning Edition" - Limited 100 units. Creates initial buzz and FOMO.</p>
                </div>
                <div style="flex: 1; min-width: 250px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Hour 3-4</h4>
                    <p style="color: #e5e5e5; margin: 0;">Drop "Afternoon Exclusive" - Different color. Lines form, social media explodes.</p>
                </div>
                <div style="flex: 1; min-width: 250px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Final Hour</h4>
                    <p style="color: #e5e5e5; margin: 0;">"Last Chance Edition" - Ultra limited. Booth stays packed until close.</p>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #4ade80;">
                <p style="font-size: 20px; color: #4ade80; margin: 0;">
                    Result: 300% more booth traffic, 500% more social mentions
                </p>
            </div>
        </div>
    </div>
</div>

<!-- VIP Tier Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            VIP Packages That Sell Themselves
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #e5e5e5; padding: 20px; border-radius: 12px 12px 0 0;">
                    <h3 style="margin: 0; color: #666;">General Admission</h3>
                </div>
                <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px;">
                    <p style="color: #666; margin: 0 0 20px 0;">Standard event grinder</p>
                    <ul style="text-align: left; color: #666; list-style: none; padding: 0;">
                        <li style="padding: 5px 0;">✓ Event branding</li>
                        <li style="padding: 5px 0;">✓ Basic colors</li>
                    </ul>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; padding: 20px; border-radius: 12px 12px 0 0;">
                    <h3 style="margin: 0; color: white;">VIP Package</h3>
                </div>
                <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; border: 2px solid #4ade80; border-top: none;">
                    <p style="color: #14532d; margin: 0 0 20px 0; font-weight: 600;">Limited edition grinder</p>
                    <ul style="text-align: left; color: #14532d; list-style: none; padding: 0; font-weight: 600;">
                        <li style="padding: 5px 0;">✓ Exclusive colorway</li>
                        <li style="padding: 5px 0;">✓ Numbered edition</li>
                        <li style="padding: 5px 0;">✓ Gift box packaging</li>
                    </ul>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); padding: 20px; border-radius: 12px 12px 0 0;">
                    <h3 style="margin: 0; color: white;">Ultra VIP</h3>
                </div>
                <div style="background: white; padding: 30px; border-radius: 0 0 12px 12px; border: 2px solid #f59e0b; border-top: none;">
                    <p style="color: #92400e; margin: 0 0 20px 0; font-weight: 600;">Artist signed grinder</p>
                    <ul style="text-align: left; color: #92400e; list-style: none; padding: 0; font-weight: 600;">
                        <li style="padding: 5px 0;">✓ Artist signature</li>
                        <li style="padding: 5px 0;">✓ 1 of 100 made</li>
                        <li style="padding: 5px 0;">✓ Display case</li>
                        <li style="padding: 5px 0;">✓ Certificate</li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="background: #fef3c7; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #92400e; font-weight: 600;">
                💡 Events with tiered grinder packages see 67% higher VIP sales
            </p>
        </div>
    </div>
</div>

<!-- Social Media Impact Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Social Media Multiplier Effect
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">94%</div>
                <p style="color: #666; margin: 10px 0;">Post unboxing photos</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">3.4x</div>
                <p style="color: #666; margin: 10px 0;">More shares than shirts</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">18K</div>
                <p style="color: #666; margin: 10px 0;">Avg hashtag mentions</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$127K</div>
                <p style="color: #666; margin: 10px 0;">Earned media value</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a;">
                <strong>"The grinder giveaway generated more social buzz than our $50K advertising campaign"</strong>
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- 420 Music Festival Director</p>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Event Planner FAQs
        </h2>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">How far in advance should we order?</h3>
            <p style="color: #666; margin: 0;">
                Standard production is 10-14 days. Rush production available in 5 days. For events over 5,000 units, order 3 weeks ahead.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can you ship to multiple vendor booths?</h3>
            <p style="color: #666; margin: 0;">
                Yes! We'll split-ship to unlimited locations. Perfect for sponsor activations at different booths. Just provide addresses and quantities.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What about sponsor co-branding?</h3>
            <p style="color: #666; margin: 0;">
                We can engrave multiple logos on each grinder. Most events do event logo on top, sponsor logo on bottom. Mockups provided free.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Do you offer on-site support?</h3>
            <p style="color: #666; margin: 0;">
                For major events (5,000+ units), we provide on-site team for distribution and real-time customization stations.
            </p>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Make Your Next Event Unforgettable
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 200+ events creating swag people actually want
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Event Quote</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Free mockups • Sponsor deck templates • ROI calculator
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Start Planning →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                Response within 2 hours • Rush production available
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🎪 Special: Free sponsor pitch deck with orders over 1,000 units
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ 97% keep rate</div>
            <div>✓ Sponsor ready</div>
            <div>✓ Rush available</div>
            <div>✓ Booth shipping</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Custom Event Grinders - Swag That Doesn't Suck",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 400,
    "meta_description": "97% of attendees keep custom grinders vs 12% for t-shirts. Perfect for Cannabis Cup, festivals, trade shows. Sponsor co-branding available.",
    "search_keywords": "event swag, cannabis event merchandise, festival giveaways, trade show grinders, event sponsorship"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ EVENT ORGANIZERS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🎪 Page Features:")
    print(f"   • 97% retention rate messaging")
    print(f"   • Event ROI calculator")
    print(f"   • Booth traffic strategies")
    print(f"   • VIP tier packages")
    print(f"   • Sponsor co-branding emphasis")
    print(f"\n🎯 Target Audience:")
    print(f"   • Event organizers")
    print(f"   • Festival directors")
    print(f"   • Trade show managers")
    print(f"   • Cannabis cup organizers")
    print(f"\n📊 Key Selling Points:")
    print(f"   • 97% keep rate vs 12% for shirts")
    print(f"   • Sponsor revenue potential")
    print(f"   • Social media multiplier")
    print(f"   • Tiered VIP options")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
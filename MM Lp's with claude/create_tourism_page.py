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

# HTML content for Cannabis Tourism landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Premium Souvenirs Worth <span style="color: #4ade80;">$89 Each</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Cannabis tourists spend 4x more on functional souvenirs than typical trinkets
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🗺️ Trusted by 75+ Cannabis Tourism Companies</strong> across legal states
            </p>
        </div>
    </div>
</div>

<!-- Tourism Revenue Opportunity -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The $1.8B Cannabis Tourism Market Needs You
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">2.5M</div>
                <p style="color: #666; margin: 10px 0;">Cannabis tourists annually</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$680</div>
                <p style="color: #666; margin: 10px 0;">Avg tourist spend/trip</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">73%</div>
                <p style="color: #666; margin: 10px 0;">Want location souvenirs</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">91%</div>
                <p style="color: #666; margin: 10px 0;">Share trip on social</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">
                "Custom grinders became our #1 revenue source, surpassing tour tickets themselves"
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- Denver Cannabis Tours</p>
        </div>
    </div>
</div>

<!-- Tour Package Enhancement Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Transform Your Tour Packages
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Basic Tour -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Dispensary Tour</h3>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #666; font-weight: 900;">$75 <span style="font-size: 14px; font-weight: 400;">standard price</span></div>
                </div>
                <p style="color: #dc2626; font-weight: 600; margin: 15px 0;">With generic souvenir: $75</p>
                <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 20px; border-radius: 8px; margin-top: 20px;">
                    <p style="color: #14532d; font-weight: 600; margin: 0 0 10px 0;">With custom grinder:</p>
                    <div style="font-size: 32px; color: #16a34a; font-weight: 900;">$149</div>
                    <p style="color: #14532d; margin: 10px 0 0 0;">98% book premium option</p>
                </div>
            </div>

            <!-- Premium Tour -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 30px; border-radius: 12px; border: 2px solid #3b82f6;">
                <div style="background: #3b82f6; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    BEST SELLER
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Farm & Facility Tour</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #1e3a8a; font-weight: 900;">$225 <span style="font-size: 14px; font-weight: 400;">standard price</span></div>
                </div>
                <p style="color: #1e3a8a; font-weight: 600; margin: 15px 0;">Includes:</p>
                <ul style="text-align: left; color: #1e3a8a; list-style: none; padding: 0; margin: 15px 0;">
                    <li style="padding: 5px 0;">✓ Limited edition grinder</li>
                    <li style="padding: 5px 0;">✓ Tour date engraving</li>
                    <li style="padding: 5px 0;">✓ Farm coordinates</li>
                </ul>
                <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600;">Your profit: $165/guest</p>
                </div>
            </div>

            <!-- VIP Experience -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 12px; border: 2px solid #f59e0b;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">VIP Experience</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #92400e; font-weight: 900;">$450 <span style="font-size: 14px; font-weight: 400;">all-inclusive</span></div>
                </div>
                <p style="color: #92400e; font-weight: 600; margin: 15px 0;">Ultimate package:</p>
                <ul style="text-align: left; color: #92400e; list-style: none; padding: 0; margin: 15px 0; font-weight: 600;">
                    <li style="padding: 5px 0;">✓ Numbered collector grinder</li>
                    <li style="padding: 5px 0;">✓ Signed by master grower</li>
                    <li style="padding: 5px 0;">✓ Display case included</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600;">Your profit: $380/guest</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Location Exclusive Strategy -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Location Exclusives Drive Demand
        </h2>

        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 40px; border-radius: 16px;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #4ade80;">The "Only Here" Strategy</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; text-align: left;">
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Denver Exclusive</h4>
                    <p style="color: #e5e5e5; margin: 0;">"Mile High Edition" with elevation marker. Only available on Denver tours. Tourists buy 3-4 as gifts.</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">California Coast</h4>
                    <p style="color: #e5e5e5; margin: 0;">"Pacific Premium" with beach coordinates. Instagram photos drive 50% of bookings.</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Vegas Limited</h4>
                    <p style="color: #e5e5e5; margin: 0;">"What Happens in Vegas" edition. Sells out every weekend at $89 each.</p>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #4ade80;">
                <p style="font-size: 20px; color: #4ade80; margin: 0;">
                    Location exclusives increase purchase rate by 340%
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Tourism Companies Winning Big
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Colorado Cannabis Tours</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$285K/year</div>
                <p style="color: #666; font-size: 14px;">Additional revenue from grinders</p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"We make more from grinders than tour tickets. Tourists buy multiple as gifts. Game changer."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">Average 2.3 grinders per tourist</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Emerald Triangle Tours</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">180%</div>
                <p style="color: #666; font-size: 14px;">Increase in premium bookings</p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Adding exclusive grinders to packages let us double prices. Nobody books basic anymore."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">$149 average ticket (was $75)</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Vegas Cannabis Concierge</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$2,100</div>
                <p style="color: #666; font-size: 14px;">Average weekend grinder sales</p>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Bachelor parties buy entire sets. 'Vegas Edition' grinders are perfect group souvenirs."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">Groups buy 8-12 units</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bud & Breakfast Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Bud & Breakfast Welcome Gifts That Wow
        </h2>

        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 40px; border-radius: 16px; border: 2px solid #4ade80;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #14532d;">Turn One-Night Stays Into Lifetime Memories</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; margin-bottom: 30px;">
                <div style="flex: 1; min-width: 250px; background: white; padding: 25px; border-radius: 12px;">
                    <h4 style="color: #14532d; margin: 0 0 15px 0;">Welcome Package</h4>
                    <p style="color: #666; margin: 0 0 10px 0;">Custom grinder with check-in date</p>
                    <div style="color: #16a34a; font-weight: 600;">Cost: $18 | Perceived value: $75</div>
                </div>
                <div style="flex: 1; min-width: 250px; background: white; padding: 25px; border-radius: 12px;">
                    <h4 style="color: #14532d; margin: 0 0 15px 0;">Room Upgrade</h4>
                    <p style="color: #666; margin: 0 0 10px 0;">Limited edition with room number</p>
                    <div style="color: #16a34a; font-weight: 600;">Drives $89 upsells</div>
                </div>
                <div style="flex: 1; min-width: 250px; background: white; padding: 25px; border-radius: 12px;">
                    <h4 style="color: #14532d; margin: 0 0 15px 0;">Loyalty Program</h4>
                    <p style="color: #666; margin: 0 0 10px 0;">Collect all room designs</p>
                    <div style="color: #16a34a; font-weight: 600;">67% return rate</div>
                </div>
            </div>

            <p style="font-size: 18px; color: #14532d; margin: 0; font-weight: 600;">
                "The grinder welcome gift gets more social posts than our actual property" - Colorado B&B Owner
            </p>
        </div>
    </div>
</div>

<!-- Tourism Packages Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Tourism Package Options
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Small Tours -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Boutique Tours</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">10-50 guests/month</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $18<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Tour branding</li>
                    <li style="padding: 8px 0;">✓ Date engraving</li>
                    <li style="padding: 8px 0;">✓ Gift packaging</li>
                    <li style="padding: 8px 0;">✓ 7-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $59 = $41 profit</strong>
                </div>
            </div>

            <!-- Growing Business -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Growth Package</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">50-200 guests/month</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $15<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Location exclusive</li>
                    <li style="padding: 8px 0;">✓ Multiple designs</li>
                    <li style="padding: 8px 0;">✓ Display case</li>
                    <li style="padding: 8px 0;">✓ Marketing photos</li>
                    <li style="padding: 8px 0;">✓ Rush available</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $69 = $54 profit</strong>
                </div>
            </div>

            <!-- High Volume -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">High Volume</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">200+ guests/month</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $12<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Bulk pricing</li>
                    <li style="padding: 8px 0;">✓ Seasonal designs</li>
                    <li style="padding: 8px 0;">✓ API integration</li>
                    <li style="padding: 8px 0;">✓ Dropship option</li>
                    <li style="padding: 8px 0;">✓ Co-marketing</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $79+ = $67+ profit</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Media Impact Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Instagram Gold: Tourist Photos That Sell Tours
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">87%</div>
                <p style="color: #666; margin: 10px 0;">Post grinder photos</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">2.8K</div>
                <p style="color: #666; margin: 10px 0;">Avg reach per post</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">34%</div>
                <p style="color: #666; margin: 10px 0;">Book from social</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$0</div>
                <p style="color: #666; margin: 10px 0;">Marketing cost</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a;">
                <strong>"Location-tagged grinder photos drive 50% of our new bookings"</strong>
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- California Cannabis Tours</p>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Tourism Company FAQs
        </h2>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can we do same-day customization?</h3>
            <p style="color: #666; margin: 0;">
                Yes! For high-volume partners, we can set up on-site engraving stations. Tourists love watching their souvenir being made.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Do tourists really pay $59-89 for these?</h3>
            <p style="color: #666; margin: 0;">
                Absolutely. Cannabis tourists spend 4x more than regular tourists. They want premium, functional souvenirs, not cheap trinkets.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can we create city-exclusive designs?</h3>
            <p style="color: #666; margin: 0;">
                Yes! Location exclusivity drives massive demand. We'll create designs that can only be purchased through your tours.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What about international tourists?</h3>
            <p style="color: #666; margin: 0;">
                Grinders are TSA-compliant when clean. International visitors love them as they can't get cannabis accessories in their home countries.
            </p>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Give Tourists Souvenirs They'll Actually Keep
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 75+ tourism companies creating unforgettable experiences
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Get Your Tourism Package</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Location exclusive designs • Sample kit • Marketing photos
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Create Your Exclusive →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                7-day turnaround • Location protection available
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🗺️ Special: Free display case with first 100 units
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ Location exclusive</div>
            <div>✓ 87% social sharing</div>
            <div>✓ TSA compliant</div>
            <div>✓ $54+ profit per unit</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Cannabis Tourism Souvenirs - Premium Custom Grinders",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 500,
    "meta_description": "Cannabis tourists spend 4x more on functional souvenirs. Custom grinders with location exclusives. 87% social sharing rate. Join 75+ tourism companies.",
    "search_keywords": "cannabis tourism, bud and breakfast, cannabis tours, dispensary tours, cannabis souvenirs"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ CANNABIS TOURISM LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🗺️ Page Features:")
    print(f"   • Location exclusive designs")
    print(f"   • Tour package enhancement")
    print(f"   • Bud & Breakfast focus")
    print(f"   • Social media impact metrics")
    print(f"   • Premium pricing justification")
    print(f"\n🎯 Target Audience:")
    print(f"   • Cannabis tour operators")
    print(f"   • Bud & breakfast owners")
    print(f"   • Cannabis concierge services")
    print(f"   • Dispensary tour guides")
    print(f"\n📊 Key Selling Points:")
    print(f"   • 87% social sharing rate")
    print(f"   • $54+ profit per unit")
    print(f"   • Location exclusivity")
    print(f"   • TSA compliant")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
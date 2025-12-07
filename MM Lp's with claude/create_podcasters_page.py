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

# HTML content for Cannabis Podcasters & Influencers landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Merch That Makes <span style="color: #4ade80;">10x More</span> Than Affiliate Links
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            $30 profit per grinder vs $3 per affiliate sale. Do the math.
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🎙️ Join 150+ Cannabis Creators</strong> monetizing with premium merch
            </p>
        </div>
    </div>
</div>

<!-- Income Comparison Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Current Monetization Reality Check
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <!-- Current Methods -->
            <div style="flex: 1; min-width: 300px; background: #fee2e2; padding: 30px; border-radius: 12px;">
                <h3 style="color: #991b1b; margin: 0 0 20px 0;">Current Income Streams</h3>
                <ul style="text-align: left; color: #7f1d1d; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">
                        <strong>YouTube ads:</strong> $2-5 per 1,000 views
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">
                        <strong>Affiliate links:</strong> 3-8% commission
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">
                        <strong>Sponsorships:</strong> Unpredictable
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #fecaca;">
                        <strong>Patreon:</strong> 5% actually subscribe
                    </li>
                    <li style="padding: 10px 0;">
                        <strong>POD merch:</strong> $5 profit per shirt
                    </li>
                </ul>
                <div style="background: #991b1b; color: white; padding: 15px; border-radius: 8px;">
                    Need 100K views for $500
                </div>
            </div>

            <!-- Custom Grinders -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 30px; border-radius: 12px; border: 2px solid #4ade80;">
                <h3 style="color: #14532d; margin: 0 0 20px 0;">Custom Grinder Drops</h3>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">
                        <strong>Profit per unit:</strong> $30-40
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">
                        <strong>Fan price point:</strong> $49-69
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">
                        <strong>No inventory:</strong> Dropship
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #86efac;">
                        <strong>Limited drops:</strong> Sell out fast
                    </li>
                    <li style="padding: 10px 0;">
                        <strong>Content gold:</strong> Unboxing videos
                    </li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    17 sales = $500+ profit
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Calculator Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Your Audience Monetization Calculator
        </h2>

        <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="text-align: left; margin-bottom: 30px;">
                <h3 style="color: #1a1a1a; margin: 0 0 20px 0;">Based on 10K followers:</h3>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Typical conversion rate:</span>
                    <strong style="color: #1a1a1a;">2-3%</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Expected sales:</span>
                    <strong style="color: #1a1a1a;">200-300 units</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Your cost:</span>
                    <strong style="color: #1a1a1a;">$19/unit</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #e5e5e5;">
                    <span style="color: #666;">Retail price:</span>
                    <strong style="color: #1a1a1a;">$49</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px 0;">
                    <span style="color: #666;">Profit per unit:</span>
                    <strong style="color: #16a34a;">$30</strong>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #dcfce7 0%, #d9f99d 100%); padding: 25px; border-radius: 12px;">
                <div style="font-size: 20px; color: #14532d; margin-bottom: 10px;">Per Drop Revenue:</div>
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$6,000-$9,000</div>
                <div style="font-size: 16px; color: #14532d; margin-top: 10px;">
                    Quarterly drops = $24,000-$36,000/year
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Creators Crushing It With Custom Merch
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Cannabis Podcast (25K subs)</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$42,000/year</div>
                <p style="color: #666; font-size: 14px;">Monthly limited drops</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Grinder drops make 5x more than all my affiliate links combined. Fans love the exclusivity."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">100 units sell out in 48 hours</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Instagram Influencer (50K)</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$18K</div>
                <p style="color: #666; font-size: 14px;">First drop (420 edition)</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Story swipe-ups converted at 8%. Never seen engagement like this on merch before."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">500 units in 5 days</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">YouTube Channel (100K)</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$75K/year</div>
                <p style="color: #666; font-size: 14px;">Patreon tier rewards</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Added grinders to $25+ Patreon tiers. Subscriptions jumped 340%."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">2,500 Patreon members</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drop Strategy Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; font-weight: 800;">
            The Perfect <span style="color: #4ade80;">Drop Strategy</span>
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    📢
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Build Hype (Week 1)</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Tease the design. Show behind-the-scenes. Create waitlist. Build FOMO with "only 100 made."
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    🚀
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">Launch Day (48 hours)</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Go live at peak engagement time. Email blast. Story takeover. Live unboxing. First 50 get bonus.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: rgba(74, 222, 128, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px;">
                    📸
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #4ade80;">User Content (Week 2+)</h3>
                <p style="color: #e5e5e5; line-height: 1.6;">
                    Repost fan unboxings. Share collection photos. Announce next drop date. Keep momentum going.
                </p>
            </div>
        </div>

        <div style="background: rgba(74, 222, 128, 0.1); padding: 30px; border-radius: 12px; margin-top: 40px; text-align: center;">
            <p style="margin: 0; font-size: 20px; color: #4ade80;">
                Result: 85% sell-through in first 48 hours, 15% saved for "last chance" push
            </p>
        </div>
    </div>
</div>

<!-- Creator Programs Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Creator Programs That Scale With You
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Starter -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Micro Influencer</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">5K-25K followers</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $22<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ No minimums</li>
                    <li style="padding: 8px 0;">✓ Dropship fulfillment</li>
                    <li style="padding: 8px 0;">✓ Your branding</li>
                    <li style="padding: 8px 0;">✓ 48-hour production</li>
                    <li style="padding: 8px 0;">✓ Link tracking</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $49 = $27 profit</strong>
                </div>
            </div>

            <!-- Growth -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Growth Creator</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">25K-100K followers</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $19<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Limited edition designs</li>
                    <li style="padding: 8px 0;">✓ Numbered series</li>
                    <li style="padding: 8px 0;">✓ Custom packaging</li>
                    <li style="padding: 8px 0;">✓ QR fan engagement</li>
                    <li style="padding: 8px 0;">✓ Content kit included</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $49 = $30 profit</strong>
                </div>
            </div>

            <!-- Pro -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Pro Creator</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">100K+ followers</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $15<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Multiple SKUs</li>
                    <li style="padding: 8px 0;">✓ Collab designs</li>
                    <li style="padding: 8px 0;">✓ Merch store setup</li>
                    <li style="padding: 8px 0;">✓ Analytics dashboard</li>
                    <li style="padding: 8px 0;">✓ Revenue share option</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Sell at $49-69 = $34-54 profit</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Creation Opportunities Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Content That Creates Itself
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">📦</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Unboxing Videos</h3>
                <p style="color: #666;">
                    Premium packaging designed for viral unboxings. Average 3x more views than regular content.
                </p>
            </div>

            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🎨</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Design Process</h3>
                <p style="color: #666;">
                    Behind-the-scenes of creating your custom design. Fans love seeing the creative process.
                </p>
            </div>

            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">🏆</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Fan Collections</h3>
                <p style="color: #666;">
                    Showcase super fans with complete collections. User-generated content goldmine.
                </p>
            </div>

            <div style="flex: 1; min-width: 250px; background: white; padding: 30px; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 20px;">💬</div>
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Review Reactions</h3>
                <p style="color: #666;">
                    React to fan reviews and photos. Builds community and provides endless content.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Patreon Integration Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Supercharge Your Patreon Tiers
        </h2>

        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 40px; border-radius: 16px; border: 2px solid #3b82f6;">
            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; text-align: left;">
                <div style="flex: 1; min-width: 250px; background: white; padding: 25px; border-radius: 12px;">
                    <h4 style="color: #1e3a8a; margin: 0 0 15px 0;">$25 Tier</h4>
                    <p style="color: #666; margin: 0;">Standard grinder + early access to drops</p>
                    <div style="color: #3b82f6; font-weight: 600; margin-top: 10px;">240% increase in signups</div>
                </div>
                <div style="flex: 1; min-width: 250px; background: white; padding: 25px; border-radius: 12px;">
                    <h4 style="color: #1e3a8a; margin: 0 0 15px 0;">$50 Tier</h4>
                    <p style="color: #666; margin: 0;">Exclusive colorway + signed packaging</p>
                    <div style="color: #3b82f6; font-weight: 600; margin-top: 10px;">180% increase in upgrades</div>
                </div>
                <div style="flex: 1; min-width: 250px; background: white; padding: 25px; border-radius: 12px;">
                    <h4 style="color: #1e3a8a; margin: 0 0 15px 0;">$100 Tier</h4>
                    <p style="color: #666; margin: 0;">Custom engraving + video shoutout</p>
                    <div style="color: #3b82f6; font-weight: 600; margin-top: 10px;">67% retention improvement</div>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #3b82f6;">
                <p style="font-size: 20px; color: #1e3a8a; margin: 0; font-weight: 600;">
                    "Adding grinders to tiers increased my Patreon revenue by $4,200/month" - Cannabis Comedy Podcast
                </p>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Creator FAQs
        </h2>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Do I need to hold inventory?</h3>
            <p style="color: #666; margin: 0;">
                Nope! We handle everything. You promote, we produce and ship directly to your fans. You never touch the product.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">What's the minimum order?</h3>
            <p style="color: #666; margin: 0;">
                Zero minimum! We can fulfill one order or one thousand. Most creators start with 50-100 unit limited drops.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">How fast can drops happen?</h3>
            <p style="color: #666; margin: 0;">
                48-hour production for pre-orders. We can have your drop ready in 2 days after design approval.
            </p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Can I do numbered/signed editions?</h3>
            <p style="color: #666; margin: 0;">
                Yes! We laser engrave numbers on each piece. You can sign packaging or we can engrave your signature.
            </p>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Ready to 10x Your Merch Revenue?
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 150+ creators making real money with premium merch
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Start Your First Drop</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Free design mockups • Content kit • Drop strategy guide
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Launch Your Merch →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                No inventory • No risk • 48-hour production
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🎙️ Special: First drop design free for creators with 10K+ followers
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ Dropship fulfillment</div>
            <div>✓ No minimums</div>
            <div>✓ 48-hour production</div>
            <div>✓ Content kit included</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Custom Cannabis Merch for Podcasters & Influencers",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 450,
    "meta_description": "Make 10x more than affiliate links. $30 profit per grinder vs $3 commissions. Dropship fulfillment, no inventory. Join 150+ cannabis creators.",
    "search_keywords": "cannabis podcast merch, influencer merchandise, creator merch dropship, cannabis content creator"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ PODCASTERS & INFLUENCERS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
    print(f"\n🎙️ Page Features:")
    print(f"   • 10x revenue vs affiliate links")
    print(f"   • Audience monetization calculator")
    print(f"   • Drop strategy framework")
    print(f"   • Patreon tier integration")
    print(f"   • Content creation opportunities")
    print(f"\n🎯 Target Audience:")
    print(f"   • Cannabis podcasters")
    print(f"   • YouTube creators")
    print(f"   • Instagram influencers")
    print(f"   • Content creators")
    print(f"\n📊 Key Selling Points:")
    print(f"   • $30 profit per unit")
    print(f"   • Dropship fulfillment")
    print(f"   • No minimums")
    print(f"   • 48-hour production")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
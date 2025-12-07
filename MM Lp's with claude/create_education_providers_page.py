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

# HTML content for Cannabis Education Providers landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Professional Training Tools That <span style="color: #4ade80;">Students Keep Forever</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Turn every graduate into a walking advertisement for your program
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🎓 Trusted by 85+ Cannabis Education Programs</strong> nationwide
            </p>
        </div>
    </div>
</div>

<!-- Education Market Opportunity -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Cannabis Education Boom
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">50,000+</div>
                <p style="color: #666; margin: 10px 0;">Students annually</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$2,500</div>
                <p style="color: #666; margin: 10px 0;">Avg course price</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">300+</div>
                <p style="color: #666; margin: 10px 0;">Cannabis schools</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">89%</div>
                <p style="color: #666; margin: 10px 0;">Want pro tools</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">
                "Custom grinders with graduation dates became our most valued alumni gift. Better than diplomas!"
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- Cannabis Training University</p>
        </div>
    </div>
</div>

<!-- Student Kit Integration Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Student Kits That Justify Premium Tuition
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Budtender Training -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Budtender Certification</h3>
                <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #16a34a; font-weight: 900;">$399 <span style="font-size: 14px; font-weight: 400;">course price</span></div>
                </div>
                <p style="color: #666; margin: 15px 0;">Student kit includes:</p>
                <ul style="text-align: left; color: #666; list-style: none; padding: 0;">
                    <li style="padding: 5px 0;">✓ Professional grinder with terpene guide</li>
                    <li style="padding: 5px 0;">✓ School logo & certification date</li>
                    <li style="padding: 5px 0;">✓ QR code to course materials</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>Kit cost: $25 | Perceived value: $150</strong>
                </div>
            </div>

            <!-- Master Grower -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 30px; border-radius: 12px; border: 2px solid #3b82f6;">
                <div style="background: #3b82f6; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    PREMIUM PROGRAM
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Master Grower Course</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #3b82f6; font-weight: 900;">$2,499 <span style="font-size: 14px; font-weight: 400;">certification</span></div>
                </div>
                <p style="color: #1e3a8a; margin: 15px 0; font-weight: 600;">Graduation gift:</p>
                <ul style="text-align: left; color: #1e3a8a; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 5px 0;">✓ Limited edition numbered grinder</li>
                    <li style="padding: 5px 0;">✓ Strain identification engravings</li>
                    <li style="padding: 5px 0;">✓ Alumni network access code</li>
                </ul>
                <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>Creates lifetime brand ambassadors</strong>
                </div>
            </div>

            <!-- Cannabis MBA -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 12px; border: 2px solid #f59e0b;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Cannabis MBA Program</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #f59e0b; font-weight: 900;">$15,000 <span style="font-size: 14px; font-weight: 400;">degree program</span></div>
                </div>
                <p style="color: #92400e; margin: 15px 0; font-weight: 600;">Executive gift set:</p>
                <ul style="text-align: left; color: #92400e; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 5px 0;">✓ Titanium-finish grinder</li>
                    <li style="padding: 5px 0;">✓ Class year & honors engraving</li>
                    <li style="padding: 5px 0;">✓ Display case with diploma</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>87% display in office</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Educational Tool Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            More Than Swag: Active Learning Tools
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🔬
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Terpene Training</h3>
                <p style="color: #666; line-height: 1.6;">
                    Engraved with terpene profiles and effects. Students use during sensory training exercises.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    📊
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Grind Consistency</h3>
                <p style="color: #666; line-height: 1.6;">
                    Teaching proper preparation techniques. Different tooth patterns for extraction methods.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🏆
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Certification Badge</h3>
                <p style="color: #666; line-height: 1.6;">
                    QR codes link to verified certifications. Employers can verify graduate credentials instantly.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Alumni Network Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Build Powerful Alumni Networks
        </h2>

        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 40px; border-radius: 16px;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #4ade80;">The Alumni Connection Strategy</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; text-align: left;">
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Year 1: Foundation</h4>
                    <p style="color: #e5e5e5; margin: 0;">Students receive grinder with graduation year. Creates instant class identity and pride.</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Year 2-5: Growth</h4>
                    <p style="color: #e5e5e5; margin: 0;">Alumni display grinders at work. New students see them and ask about your program.</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <h4 style="color: #4ade80; margin: 0 0 15px 0;">Ongoing: Network</h4>
                    <p style="color: #e5e5e5; margin: 0;">Grinders become networking tools. Alumni recognize each other's school instantly.</p>
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #4ade80;">
                <p style="font-size: 20px; color: #4ade80; margin: 0;">
                    Result: 67% of new enrollments come from alumni referrals
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Education Programs Seeing Results
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Oaksterdam University</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">2,500</div>
                <p style="color: #666; font-size: 14px;">Alumni with custom grinders</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Our graduation grinders are displayed in dispensaries nationwide. Best marketing investment ever."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">43% enrollment increase</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">THC University Online</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">$127K</div>
                <p style="color: #666; font-size: 14px;">Additional revenue from premium kits</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Adding professional grinders let us increase course prices by $100. Zero complaints."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">Course value perception +85%</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Cannabis Training Institute</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">89%</div>
                <p style="color: #666; font-size: 14px;">Job placement with grinder</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Employers recognize our grinders. Graduates get hired faster when they show their training tool."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">Industry recognition boost</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Education Packages Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Education Program Packages
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Small School -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Workshop Series</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">25-100 students/year</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $20<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ School branding</li>
                    <li style="padding: 8px 0;">✓ Certificate date</li>
                    <li style="padding: 8px 0;">✓ Student materials</li>
                    <li style="padding: 8px 0;">✓ 10-day production</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Add $75 to course price</strong>
                </div>
            </div>

            <!-- Growing School -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Certification Program</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">100-500 students/year</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $16<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Class year editions</li>
                    <li style="padding: 8px 0;">✓ QR verification</li>
                    <li style="padding: 8px 0;">✓ Alumni tracking</li>
                    <li style="padding: 8px 0;">✓ Display materials</li>
                    <li style="padding: 8px 0;">✓ Rush available</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Add $100 to tuition</strong>
                </div>
            </div>

            <!-- University -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">University Program</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">500+ students/year</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $12<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Multiple programs</li>
                    <li style="padding: 8px 0;">✓ Honors editions</li>
                    <li style="padding: 8px 0;">✓ Faculty gifts</li>
                    <li style="padding: 8px 0;">✓ Bulk pricing</li>
                    <li style="padding: 8px 0;">✓ API integration</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Premium program positioning</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Give Your Graduates Tools That Matter
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 85+ education programs creating industry professionals
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Design Your Student Kit</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Educational engravings • Alumni features • Sample kit
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Create Your Program →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                Volume discounts • 10-day production
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🎓 Special: Free instructor samples with 100+ unit orders
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ Educational tools</div>
            <div>✓ Alumni network</div>
            <div>✓ QR verification</div>
            <div>✓ Career boost</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Cannabis Education Training Tools - Student Grinder Kits",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 600,
    "meta_description": "Professional training tools for cannabis education. Student kits, graduation gifts, alumni network builders. Join 85+ schools. Justify premium tuition.",
    "search_keywords": "cannabis education, budtender training, cannabis school supplies, student kits, cannabis certification"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ EDUCATION PROVIDERS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
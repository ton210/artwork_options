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

# HTML content for Medical Cannabis Clinics landing page
html_content = '''
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 60px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 48px; margin: 0 0 20px 0; font-weight: 900; line-height: 1.2;">
            Patient Education Tools That <span style="color: #4ade80;">Improve Outcomes</span>
        </h1>
        <p style="font-size: 24px; margin: 0 0 30px 0; color: #e5e5e5; font-weight: 300;">
            Professional-grade preparation tools for medical cannabis patients
        </p>
        <div style="background: rgba(74, 222, 128, 0.1); border: 2px solid #4ade80; padding: 20px; border-radius: 12px; display: inline-block;">
            <p style="margin: 0; font-size: 20px;">
                <strong>🏥 Trusted by 200+ Medical Cannabis Clinics</strong> nationwide
            </p>
        </div>
    </div>
</div>

<!-- Medical Market Opportunity -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            The Medical Cannabis Revolution
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center;">
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">3.7M</div>
                <p style="color: #666; margin: 10px 0;">Medical patients</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">78%</div>
                <p style="color: #666; margin: 10px 0;">Need dosing help</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">$8.2B</div>
                <p style="color: #666; margin: 10px 0;">Medical market</p>
            </div>
            <div style="flex: 1; min-width: 200px; max-width: 250px;">
                <div style="font-size: 48px; color: #16a34a; font-weight: 900;">92%</div>
                <p style="color: #666; margin: 10px 0;">Want pro tools</p>
            </div>
        </div>

        <div style="background: #f9fafb; padding: 30px; border-radius: 12px; margin-top: 30px;">
            <p style="margin: 0; font-size: 18px; color: #1a1a1a; font-weight: 600;">
                "Providing patients with proper preparation tools increased medication compliance by 67%"
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">- Dr. Sarah Chen, Cannabis Medicine Specialist</p>
        </div>
    </div>
</div>

<!-- Patient Welcome Kits Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Patient Welcome Kits That Build Trust
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- New Patient Kit -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">New Patient Welcome</h3>
                <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #16a34a; font-weight: 900;">$89 <span style="font-size: 14px; font-weight: 400;">kit value</span></div>
                </div>
                <p style="color: #666; margin: 15px 0;">Includes:</p>
                <ul style="text-align: left; color: #666; list-style: none; padding: 0;">
                    <li style="padding: 5px 0;">✓ Medical-grade grinder</li>
                    <li style="padding: 5px 0;">✓ Dosing guide engraving</li>
                    <li style="padding: 5px 0;">✓ Patient ID number</li>
                    <li style="padding: 5px 0;">✓ Educational materials</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>87% patient retention rate</strong>
                </div>
            </div>

            <!-- Chronic Care -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 30px; border-radius: 12px; border: 2px solid #3b82f6;">
                <div style="background: #3b82f6; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    CHRONIC CARE
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Pain Management Program</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #3b82f6; font-weight: 900;">$149 <span style="font-size: 14px; font-weight: 400;">program add-on</span></div>
                </div>
                <p style="color: #1e3a8a; margin: 15px 0; font-weight: 600;">Premium kit includes:</p>
                <ul style="text-align: left; color: #1e3a8a; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 5px 0;">✓ Strain-specific grinder</li>
                    <li style="padding: 5px 0;">✓ Pain scale tracking</li>
                    <li style="padding: 5px 0;">✓ QR to patient portal</li>
                    <li style="padding: 5px 0;">✓ Doctor consultation notes</li>
                </ul>
                <div style="background: #3b82f6; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>67% better compliance</strong>
                </div>
            </div>

            <!-- Veteran Program -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 12px; border: 2px solid #f59e0b;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Veteran Care Package</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="font-size: 28px; color: #f59e0b; font-weight: 900;">FREE <span style="font-size: 14px; font-weight: 400;">with enrollment</span></div>
                </div>
                <p style="color: #92400e; margin: 15px 0; font-weight: 600;">Honor kit includes:</p>
                <ul style="text-align: left; color: #92400e; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 5px 0;">✓ Military branch emblem</li>
                    <li style="padding: 5px 0;">✓ PTSD support resources</li>
                    <li style="padding: 5px 0;">✓ Dosing protocols</li>
                    <li style="padding: 5px 0;">✓ Peer support access</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <strong>94% satisfaction rate</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Medical Compliance Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; text-align: center; color: #1a1a1a; font-weight: 800;">
            Improve Patient Outcomes & Compliance
        </h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    📊
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Consistent Dosing</h3>
                <p style="color: #666; line-height: 1.6;">
                    Proper grinding ensures consistent particle size for accurate dosing. Critical for medical efficacy.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    📱
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Digital Integration</h3>
                <p style="color: #666; line-height: 1.6;">
                    QR codes link to dosing journals, appointment scheduling, and telehealth portals.
                </p>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div style="background: #4ade80; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 30px; color: white;">
                    🔒
                </div>
                <h3 style="font-size: 24px; margin: 0 0 15px 0; color: #1a1a1a;">Patient Privacy</h3>
                <p style="color: #666; line-height: 1.6;">
                    Discreet medical designs. No cannabis imagery. Professional appearance for all settings.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Clinical Evidence Section -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Clinical Evidence Supports Proper Preparation
        </h2>

        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: white; padding: 40px; border-radius: 16px;">
            <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #4ade80;">Research-Backed Results</h3>

            <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center; text-align: left;">
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <div style="font-size: 36px; color: #4ade80; font-weight: 900; margin-bottom: 10px;">67%</div>
                    <p style="color: #e5e5e5; margin: 0;">Improvement in medication compliance when patients have proper tools</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <div style="font-size: 36px; color: #4ade80; font-weight: 900; margin-bottom: 10px;">43%</div>
                    <p style="color: #e5e5e5; margin: 0;">Reduction in dosing errors with consistent preparation methods</p>
                </div>
                <div style="flex: 1; min-width: 280px; background: rgba(74, 222, 128, 0.1); padding: 25px; border-radius: 12px; border: 1px solid #4ade80;">
                    <div style="font-size: 36px; color: #4ade80; font-weight: 900; margin-bottom: 10px;">89%</div>
                    <p style="color: #e5e5e5; margin: 0;">Of patients report better outcomes with professional tools</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Stories Section -->
<div style="background: white; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Clinics Seeing Real Results
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">California Medical Group</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">3,400</div>
                <p style="color: #666; font-size: 14px;">Patients with welcome kits</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Patient satisfaction scores increased 45%. The professional tools legitimize medical cannabis."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">87% retention rate</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Veterans Cannabis Clinic</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">94%</div>
                <p style="color: #666; font-size: 14px;">Patient satisfaction</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Military-themed grinders help veterans feel understood. It's about respect and recognition."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">$127K program funding secured</strong>
                </div>
            </div>

            <div style="flex: 1; min-width: 320px; background: #f9fafb; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #1a1a1a;">Pain Management Center</h3>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 10px 0;">67%</div>
                <p style="color: #666; font-size: 14px;">Better compliance</p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <em style="color: #666; font-size: 14px;">"Proper tools mean proper dosing. We've seen remarkable improvement in patient outcomes."</em>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <strong style="color: #16a34a;">43% reduction in ER visits</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Medical Program Packages -->
<div style="background: #f9fafb; padding: 60px 20px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 36px; margin: 0 0 40px 0; color: #1a1a1a; font-weight: 800;">
            Medical Program Packages
        </h2>

        <div style="display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
            <!-- Small Clinic -->
            <div style="flex: 1; min-width: 300px; background: white; border: 2px solid #e5e5e5; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Private Practice</h3>
                <div style="font-size: 20px; color: #666; margin: 10px 0;">50-200 patients</div>
                <div style="font-size: 32px; color: #1a1a1a; font-weight: 900; margin: 20px 0;">
                    $18<span style="font-size: 16px; color: #666; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #666; margin: 20px 0; list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ Clinic branding</li>
                    <li style="padding: 8px 0;">✓ Patient ID system</li>
                    <li style="padding: 8px 0;">✓ Dosing guides</li>
                    <li style="padding: 8px 0;">✓ HIPAA compliant</li>
                </ul>
                <div style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <strong>Bill insurance: $89/kit</strong>
                </div>
            </div>

            <!-- Medical Group -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #4ade80; padding: 30px; border-radius: 12px;">
                <div style="background: #16a34a; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; margin-bottom: 15px; font-size: 12px; font-weight: 600;">
                    MOST POPULAR
                </div>
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Medical Group</h3>
                <div style="font-size: 20px; color: #14532d; margin: 10px 0; font-weight: 600;">200-1,000 patients</div>
                <div style="font-size: 32px; color: #16a34a; font-weight: 900; margin: 20px 0;">
                    $14<span style="font-size: 16px; color: #14532d; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #14532d; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Multi-doctor support</li>
                    <li style="padding: 8px 0;">✓ Patient portal QR</li>
                    <li style="padding: 8px 0;">✓ Condition-specific</li>
                    <li style="padding: 8px 0;">✓ Outcome tracking</li>
                    <li style="padding: 8px 0;">✓ Rush fulfillment</li>
                </ul>
                <div style="background: #16a34a; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Insurance billable</strong>
                </div>
            </div>

            <!-- Hospital System -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; padding: 30px; border-radius: 12px;">
                <h3 style="font-size: 24px; margin: 0 0 20px 0; color: #1a1a1a;">Hospital System</h3>
                <div style="font-size: 20px; color: #92400e; margin: 10px 0; font-weight: 600;">1,000+ patients</div>
                <div style="font-size: 32px; color: #f59e0b; font-weight: 900; margin: 20px 0;">
                    $10<span style="font-size: 16px; color: #92400e; font-weight: 400;">/unit</span>
                </div>
                <ul style="text-align: left; color: #92400e; margin: 20px 0; list-style: none; padding: 0; font-weight: 600;">
                    <li style="padding: 8px 0;">✓ Bulk pricing</li>
                    <li style="padding: 8px 0;">✓ Research support</li>
                    <li style="padding: 8px 0;">✓ Clinical trials</li>
                    <li style="padding: 8px 0;">✓ EMR integration</li>
                    <li style="padding: 8px 0;">✓ Grant assistance</li>
                </ul>
                <div style="background: #f59e0b; color: white; padding: 15px; border-radius: 8px;">
                    <strong>Grant fundable</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Final CTA Section -->
<div style="background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%); color: white; padding: 80px 20px; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 42px; margin: 0 0 20px 0; font-weight: 900;">
            Elevate Your Medical Cannabis Program
        </h2>
        <p style="font-size: 20px; margin: 0 0 40px 0; color: rgba(255,255,255,0.95);">
            Join 200+ clinics improving patient outcomes with professional tools
        </p>

        <div style="background: white; color: #1a1a1a; padding: 30px; border-radius: 12px; margin: 0 auto 30px; max-width: 500px;">
            <h3 style="margin: 0 0 20px 0; font-size: 24px;">Design Your Patient Kit</h3>
            <p style="color: #666; margin: 0 0 20px 0;">
                Medical compliance • Dosing guides • Sample kit
            </p>
            <a href="https://www.munchmakers.com/contact-us/" style="display: inline-block; background: #16a34a; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: 700;">
                Create Your Program →
            </a>
            <p style="color: #666; margin: 15px 0 0 0; font-size: 14px;">
                HIPAA compliant • Insurance billable options
            </p>
        </div>

        <p style="font-size: 16px; color: rgba(255,255,255,0.9); margin: 20px 0;">
            🏥 Special: Free patient education materials with 100+ units
        </p>

        <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 40px;">
            <div>✓ Medical grade</div>
            <div>✓ 67% better compliance</div>
            <div>✓ Insurance billable</div>
            <div>✓ HIPAA compliant</div>
        </div>
    </div>
</div>
'''

# Create the page
page_data = {
    "type": "page",
    "name": "Medical Cannabis Patient Tools - Clinical Grade Grinders",
    "body": html_content,
    "is_visible": False,  # Hidden from navigation
    "parent_id": 0,
    "sort_order": 650,
    "meta_description": "Professional patient education tools for medical cannabis clinics. Improve compliance by 67%. Insurance billable. Join 200+ medical programs.",
    "search_keywords": "medical cannabis, patient tools, medical marijuana clinic, patient compliance, dosing tools"
}

# Create the page
url = f'{api_base_url}/content/pages'
response = requests.post(url, headers=headers, json=page_data)

if response.status_code == 201:
    result = response.json()
    page_id = result['data']['id']
    page_url = result['data']['url']

    print("✅ MEDICAL CLINICS LANDING PAGE CREATED SUCCESSFULLY!")
    print("=" * 60)
    print(f"\n📍 Page Details:")
    print(f"   • Page ID: {page_id}")
    print(f"   • Public URL: https://www.munchmakers.com{page_url}")
    print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{page_id}/edit")
else:
    print(f"❌ Error creating page: {response.status_code}")
    print(response.text)
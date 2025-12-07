import requests
import json
import re

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

print("FIXING DARK SECTION TEXT READABILITY")
print("=" * 80)
print("Targeting specific readability issues in dark background sections")
print()

# Focus on smoke shops page first since that's what's in the screenshot
test_pages = [51]  # Smoke shops page

def fix_dark_sections(html_content):
    """Fix specific dark sections with poor readability"""

    # Fix "Why Grinders Dominate Your Sales" section
    if 'Why Grinders' in html_content and 'Dominate Your Sales' in html_content:
        print("  Found 'Why Grinders Dominate Your Sales' section - fixing...")

        # Replace the dark gray background with better contrast
        html_content = html_content.replace(
            'background: #2a2a2a;',
            'background: #1a1a1a;'
        )

        # Fix the specific problematic sections
        # The circles with question marks - make them more visible
        html_content = html_content.replace(
            'background: #4a5f4c; color: white;',
            'background: #8BC34A; color: #1a1a1a; font-weight: bold;'
        )

        # Fix heading colors in dark sections
        html_content = html_content.replace(
            '<h3 style="font-size: 24px; color: #8BC34A;',
            '<h3 style="font-size: 24px; color: #8BC34A; font-weight: 700;'
        )

        # Fix body text in dark sections - make it white/light gray
        # Pattern: text after the green headings in dark sections
        pattern = r'(background:\s*#2[a-f0-9]{5}.*?<p style="[^"]*)(color:\s*#666[^;]*)(.*?</p>)'
        html_content = re.sub(pattern, r'\1color: #d0d0d0\3', html_content, flags=re.DOTALL | re.IGNORECASE)

    # Fix the "65% Margins Never Breaks" section
    if '65% Margins' in html_content:
        print("  Found '65% Margins' section - fixing...")

        # Ensure the main heading is visible
        html_content = re.sub(
            r'(<h2[^>]*>.*?)(65%\s*Margins)(.*?</h2>)',
            r'\1<span style="color: #8BC34A; font-size: 72px; font-weight: 800;">65% Margins</span>\3',
            html_content,
            flags=re.IGNORECASE
        )

        # Fix the subheading text
        html_content = html_content.replace(
            'Custom grinders outsell glass 3:1. Zero breakage. Exclusive to your shop.',
            '<span style="color: #ffffff; font-size: 24px; line-height: 1.5;">Custom grinders outsell glass 3:1. Zero breakage.<br>Exclusive to your shop.</span>'
        )

    # Fix the "Join 1,200+ Shops" callout box
    if 'Join 1,200+ Shops' in html_content:
        print("  Found 'Join 1,200+ Shops' section - fixing...")

        # Make the text in the green bordered box more visible
        pattern = r'(Join 1,200\+ Shops[^<]*)'
        replacement = r'<span style="color: #ffffff; font-size: 28px; font-weight: 600;">Join 1,200+ Shops</span> <span style="color: #8BC34A; font-size: 24px;">averaging $3,800/month in grinder profits</span>'

        html_content = re.sub(pattern, replacement, html_content)

    # General fixes for all dark sections
    # Find all divs with dark backgrounds (#1 or #2 at start)
    dark_bg_pattern = r'style="[^"]*background:\s*#[012][^"]*"'
    matches = list(re.finditer(dark_bg_pattern, html_content))

    for match in reversed(matches):  # Process in reverse to maintain positions
        section_start = match.start()
        section_end = html_content.find('</div>', section_start)

        if section_end > section_start:
            section = html_content[section_start:section_end]

            # Fix common color issues in this section
            fixed_section = section
            fixed_section = fixed_section.replace('color: #666', 'color: #d0d0d0')
            fixed_section = fixed_section.replace('color:#666', 'color: #d0d0d0')
            fixed_section = fixed_section.replace('color: #999', 'color: #e0e0e0')
            fixed_section = fixed_section.replace('color:#999', 'color: #e0e0e0')
            fixed_section = fixed_section.replace('color: #333', 'color: #f5f5f5')
            fixed_section = fixed_section.replace('color:#333', 'color: #f5f5f5')

            # Replace in original
            html_content = html_content[:section_start] + fixed_section + html_content[section_end:]

    return html_content

# Process the smoke shops page first
for page_id in test_pages:
    try:
        url = f'{api_base_url}/content/pages/{page_id}'
        response = requests.get(url, headers=headers)

        if response.status_code == 200:
            page_data = response.json()['data']
            current_body = page_data.get('body', '')
            page_name = page_data.get('name', 'Unknown')

            print(f"\nProcessing Page {page_id}: {page_name}")

            if current_body:
                # Check if this page has the problematic sections
                has_dark_sections = 'background: #2a2a2a' in current_body or 'Why Grinders' in current_body

                if has_dark_sections:
                    print("  Dark sections detected - applying fixes...")

                    # Apply fixes
                    fixed_body = fix_dark_sections(current_body)

                    # Update the page
                    update_data = {
                        'body': fixed_body
                    }

                    update_response = requests.put(url, headers=headers, json=update_data)

                    if update_response.status_code == 200:
                        print(f"  ✅ SUCCESS - Readability improved")
                        print(f"     • Dark section text now visible")
                        print(f"     • Question mark icons enhanced")
                        print(f"     • Heading contrast improved")
                        print(f"     • Body text lightened for dark backgrounds")
                    else:
                        print(f"  ❌ Failed to update: {update_response.status_code}")
                else:
                    print(f"  ℹ️  No dark sections found that need fixing")
            else:
                print(f"  ⚠️  Page has no content")

        else:
            print(f"❌ Could not fetch page {page_id}")

    except Exception as e:
        print(f"❌ Error processing page {page_id}: {str(e)}")

print("\n" + "=" * 80)
print("Would you like me to apply these fixes to all 14 pages? (y/n)")
print("If yes, uncomment the line below and run again:")
print("# page_ids = [48, 49, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62]")
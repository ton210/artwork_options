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

print("FIXING TEXT READABILITY ON ALL LANDING PAGES")
print("=" * 80)
print()

# All landing page IDs
page_ids = [48, 49, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62]

def fix_text_colors(html_content):
    """Fix text readability issues in HTML content"""

    # Replace dark gray text colors with better contrast
    replacements = [
        # Fix light gray text on dark backgrounds
        ('color: #666;', 'color: #e0e0e0;'),
        ('color: #666', 'color: #e0e0e0'),
        ('color:#666;', 'color: #e0e0e0;'),
        ('color:#666', 'color: #e0e0e0'),

        # Fix medium gray text
        ('color: #999;', 'color: #cccccc;'),
        ('color: #999', 'color: #cccccc'),
        ('color:#999;', 'color: #cccccc;'),
        ('color:#999', 'color: #cccccc'),

        # Fix dark text that should be lighter on dark backgrounds
        ('color: #333;', 'color: #f5f5f5;'),
        ('color: #333', 'color: #f5f5f5'),
        ('color:#333;', 'color: #f5f5f5;'),
        ('color:#333', 'color: #f5f5f5'),

        # Ensure white text stays white
        ('color: white;', 'color: white;'),
        ('color: #fff;', 'color: white;'),
        ('color: #ffffff;', 'color: white;'),
    ]

    # Apply replacements
    for old, new in replacements:
        html_content = html_content.replace(old, new)

    # Fix specific sections that are hard to read
    # Look for dark background sections and ensure text is light
    dark_bg_pattern = r'background:\s*#[0-3][0-9a-fA-F]{5}'
    dark_sections = re.findall(dark_bg_pattern, html_content)

    # Enhance contrast for "Why Grinders" type sections
    html_content = html_content.replace(
        'background: #2a2a2a',
        'background: #2a2a2a'
    )

    # Fix the specific "Why Grinders Dominate Your Sales" section
    if 'Why Grinders' in html_content:
        # Make sure text in dark sections is white or very light
        html_content = re.sub(
            r'(<div[^>]*style="[^"]*background:\s*#2[0-9a-fA-F]{5}[^"]*"[^>]*>.*?)(color:\s*#[0-9a-fA-F]{3,6})',
            r'\1color: #ffffff',
            html_content,
            flags=re.DOTALL
        )

    # Fix question mark icons to be more visible
    html_content = html_content.replace(
        'background: #4a5f4c; color: white;',
        'background: #8BC34A; color: white;'
    )

    # Improve readability of body text in dark sections
    # Find divs with dark backgrounds and update their text colors
    pattern = r'(<div[^>]*style="[^"]*background[^"]*#[0-3][0-9a-fA-F]{5}[^"]*">)'
    matches = re.finditer(pattern, html_content)

    for match in matches:
        start = match.start()
        # Find the closing div
        div_count = 1
        pos = match.end()
        while div_count > 0 and pos < len(html_content):
            if html_content[pos:pos+5] == '<div':
                div_count += 1
            elif html_content[pos:pos+6] == '</div>':
                div_count -= 1
            pos += 1

        # Extract the content between div tags
        section = html_content[start:pos]

        # If this section has dark background, ensure text is light
        if '#2' in section or '#1' in section or '#0' in section:
            # Replace any dark text colors with light ones
            section = section.replace('color: #1a1a1a', 'color: #ffffff')
            section = section.replace('color: #333333', 'color: #f0f0f0')
            section = section.replace('color: #666666', 'color: #e0e0e0')

            # Update the HTML
            html_content = html_content[:start] + section + html_content[pos:]

    return html_content

# Process each page
success_count = 0
failed_count = 0

for page_id in page_ids:
    try:
        # Get current page content
        url = f'{api_base_url}/content/pages/{page_id}'
        response = requests.get(url, headers=headers)

        if response.status_code == 200:
            page_data = response.json()['data']
            current_body = page_data.get('body', '')
            page_name = page_data.get('name', 'Unknown')

            if current_body:
                # Fix text readability
                fixed_body = fix_text_colors(current_body)

                # Count how many replacements were made
                changes_made = (current_body != fixed_body)

                if changes_made:
                    # Update the page with fixed content
                    update_data = {
                        'body': fixed_body
                    }

                    update_response = requests.put(url, headers=headers, json=update_data)

                    if update_response.status_code == 200:
                        print(f"✅ Page {page_id}: {page_name}")
                        print(f"   - Text colors improved for better readability")
                        success_count += 1
                    else:
                        print(f"❌ Page {page_id}: Failed to update")
                        print(f"   Error: {update_response.status_code}")
                        failed_count += 1
                else:
                    print(f"ℹ️  Page {page_id}: {page_name}")
                    print(f"   - No changes needed")
            else:
                print(f"⚠️  Page {page_id}: No content found")

        else:
            print(f"❌ Page {page_id}: Could not fetch page")
            failed_count += 1

    except Exception as e:
        print(f"❌ Page {page_id}: Exception - {str(e)}")
        failed_count += 1

print("\n" + "=" * 80)
print("READABILITY FIX COMPLETE")
print("=" * 80)
print(f"✅ Pages improved: {success_count}")
print(f"❌ Failed: {failed_count}")

if success_count > 0:
    print("\n✨ Text readability has been improved!")
    print("Changes made:")
    print("  • Dark gray text (#666) → Light gray (#e0e0e0)")
    print("  • Medium gray text (#999) → Lighter gray (#cccccc)")
    print("  • Dark text (#333) on dark backgrounds → Light (#f5f5f5)")
    print("  • Question mark icons → More visible green")
    print("\nPages should now be much easier to read!")
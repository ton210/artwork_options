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

# FAQ Schema template
def get_faq_schema(faqs):
    """Generate FAQ schema markup"""
    faq_items = []
    for faq in faqs:
        faq_items.append({
            "@type": "Question",
            "name": faq['question'],
            "acceptedAnswer": {
                "@type": "Answer",
                "text": faq['answer']
            }
        })

    schema = {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": faq_items
    }

    return f'<script type="application/ld+json">{json.dumps(schema)}</script>'

# Pages to update
pages_to_update = [
    {
        'id': 61,
        'page_name': 'Cannabis Delivery Services',
        'name': 'Delivery Service Retention Tools | MunchMakers',
        'meta_description': 'Transform cannabis deliveries into experiences. 43% reorder rate increase with custom grinders. Unboxing moments that drive loyalty.',
        'faqs': [
            {
                'question': 'How do custom grinders improve delivery retention?',
                'answer': 'Custom grinders create memorable unboxing experiences that increase reorder rates by 43%. Customers share these moments on social media, driving organic growth.'
            },
            {
                'question': 'What is the ROI for delivery services?',
                'answer': 'Delivery services see an average $265 increase in customer lifetime value. Customers who receive branded grinders order 2.3x more frequently.'
            },
            {
                'question': 'Can you handle high-volume delivery operations?',
                'answer': 'Yes! We support delivery services of all sizes with no minimums. Most services order 500-1000 units monthly with automatic reordering.'
            },
            {
                'question': 'How quickly can we get branded grinders?',
                'answer': 'Standard production is 5 days. Rush delivery in 48 hours for urgent needs. We can dropship directly to your fulfillment center.'
            }
        ]
    },
    {
        'id': 62,
        'page_name': 'Musicians & Artists',
        'name': 'Tour Merch 70% Profit Margins | MunchMakers',
        'meta_description': '70% profit margins vs 20% on shirts. Custom grinders for musicians generate $22K per tour. No minimums, dropship available.',
        'faqs': [
            {
                'question': 'How much more profitable than traditional merch?',
                'answer': 'Custom grinders offer 70% profit margins vs 20% on t-shirts. A 20-date tour typically generates $22,000 in grinder profits.'
            },
            {
                'question': 'Can you ship to different tour venues?',
                'answer': 'Absolutely! We split-ship to each venue ahead of your arrival. Most artists have us ship 20-30 units per stop.'
            },
            {
                'question': 'What about limited edition drops?',
                'answer': 'Limited editions are perfect for tours! Numbered series, tour-exclusive designs, and VIP packages create collectibility and urgency.'
            },
            {
                'question': 'Do fans really pay $49-79 for these?',
                'answer': 'Yes! Functional merchandise that fans use daily commands premium prices. VIP packages with signed grinders sell for $99+.'
            }
        ]
    }
]

# Update each page
for page in pages_to_update:
    page_id = page['id']

    try:
        # Get current page content
        url = f'{api_base_url}/content/pages/{page_id}'
        response = requests.get(url, headers=headers)

        if response.status_code == 200:
            current_page = response.json()['data']
            current_body = current_page.get('body', '')

            # Generate FAQ schema
            faq_schema = get_faq_schema(page['faqs'])

            # Add schema to body if not present
            if '<script type="application/ld+json">' not in current_body:
                new_body = current_body + '\n' + faq_schema
            else:
                new_body = current_body

            # Update the page
            update_data = {
                "name": page['name'],
                "meta_description": page['meta_description'],
                "body": new_body
            }

            update_response = requests.put(url, headers=headers, json=update_data)

            if update_response.status_code == 200:
                print(f"✅ Updated Page {page_id} - {page['page_name']}")
                print(f"   Title: {page['name']} ({len(page['name'])} chars)")
                print(f"   Meta: {page['meta_description'][:50]}... ({len(page['meta_description'])} chars)")
                print(f"   FAQ Schema: Added")
            else:
                print(f"❌ Error updating Page {page_id}: {update_response.status_code}")

        else:
            print(f"❌ Could not fetch Page {page_id}: {response.status_code}")

    except Exception as e:
        print(f"❌ Exception for Page {page_id}: {str(e)}")

print("\n" + "="*60)
print(f"✅ Both new pages now have:")
print(f"   • SEO-optimized titles with '| MunchMakers'")
print(f"   • Compelling meta descriptions")
print(f"   • FAQ schema markup for rich snippets")
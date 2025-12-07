#!/usr/bin/env python3
"""
Investigate how blog posts are managed on munchmakers.com
"""
import requests
import re

def check_blog_system(blog_url):
    """Check a blog post to see what system is being used"""
    print("="*70)
    print("INVESTIGATING BLOG SYSTEM")
    print("="*70)

    try:
        print(f"\nFetching: {blog_url}")
        response = requests.get(blog_url, timeout=10)

        if response.status_code == 200:
            print(f"✓ Blog post loaded successfully")

            # Get HTML content
            html_text = response.text.lower()

            # Check for common blog platform indicators
            indicators = {
                'WordPress': ['wp-content', 'wp-includes', 'wordpress'],
                'BigCommerce Blog': ['bigcommerce-blog', 'bc-blog'],
                'Shopify Blog': ['shopify'],
                'Stencil (BigCommerce)': ['stencil', 'bigcommerce'],
            }

            print("\nDetected platform indicators:")
            detected = False
            for platform, keywords in indicators.items():
                if any(keyword in html_text for keyword in keywords):
                    print(f"  ✓ {platform}: Detected")
                    detected = True

            if not detected:
                print("  ? Could be custom implementation or headless CMS")

            # Check for meta generator
            generator_match = re.search(r'<meta name="generator" content="([^"]+)"', html_text, re.IGNORECASE)
            if generator_match:
                print(f"\nMeta Generator: {generator_match.group(1)}")

            # Check for page builder indicators
            page_builders = ['elementor', 'beaver-builder', 'divi', 'gutenberg']
            for builder in page_builders:
                if builder in html_text:
                    print(f"  ✓ Page builder detected: {builder}")

            # Check for article tag
            if '<article' in html_text:
                print(f"\n✓ Found <article> tag (standard blog structure)")

            # Check for blog-specific classes
            blog_classes = ['blog-post', 'post-content', 'article-content', 'entry-content']
            for cls in blog_classes:
                if cls in html_text:
                    print(f"  ✓ Found blog class: {cls}")

            return html_text

        else:
            print(f"✗ Failed to load blog post: {response.status_code}")
            return None

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return None

def check_bigcommerce_blog_api():
    """Check if BigCommerce has a blog API endpoint"""
    print("\n" + "="*70)
    print("CHECKING FOR BIGCOMMERCE BLOG API")
    print("="*70)

    BC_STORE_HASH = 'tqjrceegho'
    BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

    HEADERS = {
        'X-Auth-Token': BC_ACCESS_TOKEN,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }

    # Try different potential blog endpoints
    endpoints = [
        f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3/content/blog/posts',
        f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2/blog/posts',
        f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3/blog',
    ]

    for endpoint in endpoints:
        print(f"\nTrying: {endpoint}")
        try:
            response = requests.get(endpoint, headers=HEADERS, timeout=5)
            print(f"  Status: {response.status_code}")

            if response.status_code == 200:
                print(f"  ✓ FOUND BLOG API!")
                data = response.json()
                print(f"  Response: {json.dumps(data, indent=2)[:500]}")
                return endpoint
            elif response.status_code == 404:
                print(f"  ✗ Not found")
            else:
                print(f"  Response: {response.text[:200]}")

        except Exception as e:
            print(f"  ✗ Error: {str(e)}")

    print(f"\n✗ No blog API endpoint found in BigCommerce")
    return None

if __name__ == '__main__':
    # Test with a blog URL from SEMrush data
    test_blog_url = "https://munchmakers.com/blog/the-ultimate-guide-to-weed-grinders-with-kief-catchers-2/"

    check_blog_system(test_blog_url)
    check_bigcommerce_blog_api()

    print("\n" + "="*70)
    print("INVESTIGATION COMPLETE")
    print("="*70)

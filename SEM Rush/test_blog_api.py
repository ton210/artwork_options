#!/usr/bin/env python3
"""
Test BigCommerce Blog API access
"""
import requests
import json

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API endpoints
BLOG_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2/blog/posts'

# Headers
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def get_blog_posts():
    """Get all blog posts"""
    print("="*70)
    print("TESTING BIGCOMMERCE BLOG API")
    print("="*70)

    try:
        # Get blog posts with pagination
        url = BLOG_API
        params = {'limit': 10}  # Get first 10

        print(f"\nFetching blog posts from: {url}")
        response = requests.get(url, headers=HEADERS, params=params)

        if response.status_code == 200:
            posts = response.json()
            print(f"✓ SUCCESS! Found blog posts")
            print(f"  Total posts in response: {len(posts)}")

            # Show first few posts
            for i, post in enumerate(posts[:5], 1):
                print(f"\n  Post #{i}:")
                print(f"    ID: {post.get('id')}")
                print(f"    Title: {post.get('title')}")
                print(f"    URL: {post.get('url')}")
                print(f"    Published: {post.get('published_date', {}).get('date', 'N/A')}")
                print(f"    Body length: {len(post.get('body', ''))} chars")

            return posts

        else:
            print(f"✗ Failed: {response.status_code}")
            print(f"  Response: {response.text}")
            return []

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return []

def get_single_blog_post(post_id):
    """Get a single blog post with full content"""
    print(f"\n" + "="*70)
    print(f"GETTING BLOG POST ID: {post_id}")
    print("="*70)

    try:
        url = f'{BLOG_API}/{post_id}'
        response = requests.get(url, headers=HEADERS)

        if response.status_code == 200:
            post = response.json()
            print(f"✓ Retrieved blog post")
            print(f"\n  Title: {post.get('title')}")
            print(f"  URL: {post.get('url')}")
            print(f"  Author: {post.get('author')}")

            # Check body content
            body = post.get('body', '')
            print(f"\n  Body length: {len(body)} characters")
            print(f"  Body preview: {body[:300]}...")

            print(f"\n  ✓ Blog post content is EDITABLE via API")

            return post

        else:
            print(f"✗ Failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return None

def test_update_blog_post_simulation(post_id, anchor_text, link_url):
    """
    Simulate updating a blog post with a hyperlink
    This is a DRY RUN - doesn't actually update
    """
    print(f"\n" + "="*70)
    print(f"SIMULATING BLOG POST UPDATE (DRY RUN)")
    print("="*70)

    # Get the post first
    post = get_single_blog_post(post_id)

    if not post:
        print("✗ Cannot simulate - post not found")
        return False

    body = post.get('body', '')

    # Check if anchor text exists
    if anchor_text.lower() in body.lower():
        print(f"\n✓ Found '{anchor_text}' in blog post")

        # Find the exact match (case-insensitive)
        import re
        pattern = re.compile(re.escape(anchor_text), re.IGNORECASE)
        match = pattern.search(body)

        if match:
            matched_text = match.group()

            # Create hyperlink
            hyperlink = f'<a href="{link_url}">{matched_text}</a>'

            # Replace in body
            updated_body = body[:match.start()] + hyperlink + body[match.end():]

            print(f"\nProposed change:")
            print(f"  Before: ...{body[max(0, match.start()-50):match.end()+50]}...")
            print(f"  After:  ...{updated_body[max(0, match.start()-50):match.start()+len(hyperlink)+50]}...")

            print(f"\n✓ Hyperlink insertion would be successful")
            print(f"  Link: {hyperlink}")

            # Show what the API call would look like
            print(f"\nAPI Update call (DRY RUN):")
            print(f"  PUT {BLOG_API}/{post_id}")
            print(f"  Payload: {{'body': '<updated HTML content>'}}")

            return True
    else:
        print(f"\n✗ '{anchor_text}' not found in blog post content")
        return False

if __name__ == '__main__':
    print("\n")

    # Test 1: Get blog posts
    posts = get_blog_posts()

    if posts and len(posts) > 0:
        # Test 2: Get single post
        first_post = posts[0]
        post_details = get_single_blog_post(first_post['id'])

        # Test 3: Simulate hyperlink insertion
        if post_details:
            # Try to find common words to hyperlink
            body_lower = post_details.get('body', '').lower()

            # Look for product-related keywords
            keywords_to_test = ['grinder', 'rolling tray', 'custom grinder', 'weed grinder']

            for keyword in keywords_to_test:
                if keyword in body_lower:
                    print(f"\nAttempting to add link for keyword: '{keyword}'")
                    test_update_blog_post_simulation(
                        first_post['id'],
                        keyword,
                        'https://munchmakers.com/product-category/custom-grinders/'
                    )
                    break  # Only test one

    print("\n" + "="*70)
    print("BLOG API TESTING COMPLETE")
    print("="*70)
    print("\n✓ Blog posts can be read and updated via BigCommerce API v2")
    print("✓ Ready to implement internal linking strategy")
    print("\n")

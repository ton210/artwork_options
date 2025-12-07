import requests
import os
from requests.auth import HTTPDigestAuth

# WebDAV credentials
webdav_username = 'billing@greenlunar.com'
webdav_password = 'a81686b5cc9da9afcf1fb528e86e5349'
webdav_base = 'https://store-tqjrceegho.mybigcommerce.com/dav'

# Use digest auth for WebDAV
auth = HTTPDigestAuth(webdav_username, webdav_password)

def test_webdav_connection():
    """Test WebDAV connection and list available directories"""

    print("Testing WebDAV connection...")
    print(f"Base URL: {webdav_base}")

    # Try to access the root
    response = requests.request('PROPFIND', webdav_base, auth=auth, headers={'Depth': '1'})

    print(f"Response status: {response.status_code}")
    if response.status_code in [200, 207]:
        print("✓ Connected successfully!")
        print("\nAvailable paths in WebDAV:")
        # Parse the response to show available directories
        if 'product_images' in response.text:
            print("  - /product_images/ (found)")
        if 'product_downloads' in response.text:
            print("  - /product_downloads/ (found)")
        if 'import_files' in response.text:
            print("  - /import_files/ (found)")
        if 'exports' in response.text:
            print("  - /exports/ (found)")
        if 'content' in response.text:
            print("  - /content/ (found)")
        # Show a snippet of the response for debugging
        print(f"\nRaw response preview (first 500 chars):\n{response.text[:500]}")
    else:
        print(f"✗ Connection failed: {response.status_code}")
        print(f"Response: {response.text}")

def upload_image(local_path, remote_filename):
    """Upload an image to WebDAV"""

    if not os.path.exists(local_path):
        print(f"✗ File not found: {local_path}")
        return None

    # Read the image
    with open(local_path, 'rb') as f:
        image_data = f.read()

    # Try different paths
    paths_to_try = [
        f'{webdav_base}/product_images/{remote_filename}',
        f'{webdav_base}/product_downloads/{remote_filename}',
        f'{webdav_base}/{remote_filename}'
    ]

    for upload_url in paths_to_try:
        print(f"\nTrying to upload to: {upload_url}")

        headers = {
            'Content-Type': 'image/png'
        }

        response = requests.put(upload_url, data=image_data, auth=auth, headers=headers)

        if response.status_code in [200, 201, 204]:
            print(f"✓ Upload successful!")
            # Generate public URL
            if 'product_images' in upload_url:
                public_url = f"https://store-tqjrceegho.mybigcommerce.com/product_images/{remote_filename}"
            elif 'product_downloads' in upload_url:
                public_url = f"https://store-tqjrceegho.mybigcommerce.com/product_downloads/{remote_filename}"
            else:
                public_url = f"https://store-tqjrceegho.mybigcommerce.com/{remote_filename}"

            print(f"  Public URL: {public_url}")
            return public_url
        else:
            print(f"  ✗ Failed: {response.status_code}")
            if response.status_code == 404:
                print(f"    Path doesn't exist")
            elif response.status_code == 401:
                print(f"    Authentication failed")
            else:
                print(f"    Response: {response.text[:200]}")

    return None

# First test the connection
print("="*60)
print("TESTING WEBDAV CONNECTION")
print("="*60)
test_webdav_connection()

# Now try to upload images
print("\n" + "="*60)
print("UPLOADING DISPENSARY IMAGES")
print("="*60)

images_to_upload = [
    {
        'local': 'dispensary_hero_image_20251115_201521.png',
        'remote': 'dispensary_hero.png'
    },
    {
        'local': 'dispensary_v2_customer_experience_20251115_202140.png',
        'remote': 'dispensary_customer.png'
    },
    {
        'local': 'dispensary_v2_profit_concept_20251115_202147.png',
        'remote': 'dispensary_profit.png'
    }
]

uploaded_urls = []

for img in images_to_upload:
    print(f"\n--- Uploading {img['local']} ---")
    url = upload_image(img['local'], img['remote'])
    if url:
        uploaded_urls.append({
            'name': img['remote'],
            'url': url
        })

print("\n" + "="*60)
print("SUMMARY")
print("="*60)

if uploaded_urls:
    print(f"Successfully uploaded {len(uploaded_urls)} images:")
    for item in uploaded_urls:
        print(f"  • {item['name']}: {item['url']}")
else:
    print("No images were successfully uploaded.")
    print("\nAlternative: You can upload images manually through:")
    print("1. BigCommerce Admin > Storefront > Image Manager")
    print("2. Or use external image hosting (Imgur, Cloudinary, etc.)")
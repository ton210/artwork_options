import requests
import json
import base64
import time
from datetime import datetime
from requests.auth import HTTPDigestAuth

FREEPIK_API_KEY = 'FPSX381b01bdceb04b9fa3c51f52816cfacd'

# WebDAV credentials
webdav_username = 'billing@greenlunar.com'
webdav_password = 'a81686b5cc9da9afcf1fb528e86e5349'
webdav_base = 'https://store-tqjrceegho.mybigcommerce.com/dav'
auth = HTTPDigestAuth(webdav_username, webdav_password)

def generate_professional_image(prompt, image_name):
    """Generate a professional, high-quality image with Freepik API"""

    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    # Much more specific and professional prompt
    data = {
        "prompt": prompt,
        "negative_prompt": "cartoon, illustration, drawing, anime, low quality, blurry, distorted, ugly, deformed, amateur, bad lighting",
        "guidance_scale": 7.5,
        "seed": None,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        }
    }

    print(f"\nGenerating {image_name}...")
    print(f"Prompt: {prompt[:100]}...")

    try:
        response = requests.post(url, headers=headers, json=data)

        if response.status_code == 200:
            result = response.json()
            if 'data' in result and len(result['data']) > 0:
                image_data = result['data'][0]

                if 'base64' in image_data:
                    # Save the image locally
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    filename = f"pro_{image_name}_{timestamp}.png"

                    with open(filename, "wb") as f:
                        f.write(base64.b64decode(image_data['base64']))

                    print(f"✓ Generated and saved as {filename}")
                    return image_data['base64'], filename

        else:
            print(f"✗ Error: {response.status_code}")

    except Exception as e:
        print(f"✗ Exception: {e}")

    return None, None

def upload_to_webdav(local_file, remote_name):
    """Upload image to BigCommerce WebDAV"""

    if not local_file:
        return None

    with open(local_file, 'rb') as f:
        image_data = f.read()

    upload_url = f'{webdav_base}/product_images/{remote_name}'

    headers = {
        'Content-Type': 'image/png'
    }

    response = requests.put(upload_url, data=image_data, auth=auth, headers=headers)

    if response.status_code in [200, 201, 204]:
        public_url = f"https://store-tqjrceegho.mybigcommerce.com/product_images/{remote_name}"
        print(f"✓ Uploaded to: {public_url}")
        return public_url
    else:
        print(f"✗ Upload failed: {response.status_code}")
        return None

# Define professional image prompts
professional_images = [
    {
        "name": "dispensary_hero_v2",
        "remote": "dispensary_hero_v2.png",
        "prompt": """Ultra modern cannabis dispensary interior, professional photography, bright white walls,
        organized display shelves with rows of black aluminum herb grinders with green logo accents,
        premium glass display cases, professional retail lighting, clean minimalist design,
        high-end boutique aesthetic, shallow depth of field, commercial product photography"""
    },
    {
        "name": "happy_customer",
        "remote": "dispensary_customer_v2.png",
        "prompt": """Professional photo of smiling dispensary customer at checkout counter,
        holding premium black aluminum grinder with green accents in hands, examining the product,
        bright modern dispensary interior background softly blurred, natural lighting,
        genuine happy expression, professional commercial photography, high quality"""
    },
    {
        "name": "profit_display",
        "remote": "dispensary_profit_v2.png",
        "prompt": """Professional product photography of premium black aluminum grinders arranged on white surface,
        next to neat stack of hundred dollar bills and modern calculator showing numbers,
        bright studio lighting, clean minimal composition, business success concept,
        sharp focus on products, commercial photography style, no text or logos"""
    },
    {
        "name": "product_showcase",
        "remote": "dispensary_showcase.png",
        "prompt": """Professional product photography of cannabis accessories collection on white background,
        featuring black aluminum grinders, metal rolling trays, and premium lighters arranged aesthetically,
        studio lighting with soft shadows, commercial catalog photography, ultra sharp details,
        high-end product presentation, clean and organized layout"""
    }
]

print("="*60)
print("GENERATING PROFESSIONAL DISPENSARY IMAGES")
print("="*60)

generated_images = []

for img_config in professional_images:
    base64_data, local_file = generate_professional_image(img_config['prompt'], img_config['name'])

    if local_file:
        generated_images.append({
            'local': local_file,
            'remote': img_config['remote'],
            'name': img_config['name']
        })

    time.sleep(3)  # Rate limiting

print("\n" + "="*60)
print("UPLOADING TO WEBDAV")
print("="*60)

uploaded_urls = []

for img in generated_images:
    print(f"\nUploading {img['name']}...")
    url = upload_to_webdav(img['local'], img['remote'])
    if url:
        uploaded_urls.append({
            'name': img['name'],
            'url': url
        })

print("\n" + "="*60)
print("SUMMARY")
print("="*60)

if uploaded_urls:
    print(f"\n✓ Successfully generated and uploaded {len(uploaded_urls)} professional images:")
    for item in uploaded_urls:
        print(f"\n  {item['name']}:")
        print(f"  {item['url']}")
else:
    print("\n✗ No images were successfully uploaded")

print("\nNOTE: Check the generated images locally to verify quality before using.")
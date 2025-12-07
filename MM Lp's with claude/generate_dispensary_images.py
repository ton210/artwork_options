import requests
import json
import base64
import time
from datetime import datetime

FREEPIK_API_KEY = 'FPSX381b01bdceb04b9fa3c51f52816cfacd'

def generate_image(prompt, image_name, style_preset="photo"):
    """Generate a single image with Freepik API"""

    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    data = {
        "prompt": prompt,
        "negative_prompt": "low quality, blurry, distorted, unprofessional, cartoon, illustration, anime",
        "guidance_scale": 8,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        },
        "styling": {
            "style": style_preset,
            "color": "colorful",
            "lightning": "warm"
        }
    }

    print(f"\nGenerating {image_name}...")
    print(f"Prompt preview: {prompt[:100]}...")

    try:
        response = requests.post(url, headers=headers, json=data)

        if response.status_code == 200:
            result = response.json()
            if 'data' in result and len(result['data']) > 0:
                image_data = result['data'][0]

                if 'base64' in image_data:
                    # Save the image
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    filename = f"dispensary_{image_name}_{timestamp}.png"

                    with open(filename, "wb") as f:
                        f.write(base64.b64decode(image_data['base64']))

                    print(f"✓ Saved as {filename}")
                    return image_data['base64'], filename

        else:
            print(f"✗ Error: {response.status_code}")

    except Exception as e:
        print(f"✗ Exception: {e}")

    return None, None

# Define all images we want for the dispensary page
dispensary_images = [
    {
        "name": "budtender_interaction",
        "prompt": "Professional budtender in modern cannabis dispensary showing premium black aluminum grinder to happy customer across glass counter, bright clean retail space, professional photography, warm lighting, both people smiling and engaged in conversation, grinder clearly visible in hands"
    },
    {
        "name": "profit_visualization",
        "prompt": "Modern dispensary cash register display showing sale transaction on screen with cannabis accessories, stacks of money and calculator on counter, professional product photography showing ROI and profit margins, bright commercial lighting"
    },
    {
        "name": "loyalty_display",
        "prompt": "Premium dispensary loyalty program display with custom branded black grinders and rolling trays arranged on white shelves as rewards, point redemption sign, clean modern dispensary interior, professional retail photography"
    },
    {
        "name": "checkout_upsell",
        "prompt": "Dispensary checkout counter with impulse buy display of colorful custom lighters and small grinders near register, customer reaching for accessory, professional retail environment, bright lighting"
    },
    {
        "name": "brand_wall",
        "prompt": "Dispensary brand wall display featuring organized rows of custom branded smoking accessories, grinders, rolling trays, and lighters with dispensary logo, premium retail presentation, professional product photography"
    },
    {
        "name": "unboxing_moment",
        "prompt": "Hands opening premium white dispensary bag revealing custom branded black grinder and accessories inside, Instagram-worthy unboxing moment, clean background, professional product photography, dramatic lighting"
    }
]

# Store results
results = []

print("="*60)
print("GENERATING DISPENSARY LANDING PAGE IMAGES")
print("="*60)

for i, image_config in enumerate(dispensary_images, 1):
    print(f"\n[{i}/{len(dispensary_images)}] {image_config['name']}")

    base64_data, filename = generate_image(
        image_config['prompt'],
        image_config['name']
    )

    if base64_data:
        results.append({
            "name": image_config['name'],
            "filename": filename,
            "base64": base64_data[:50] + "..." # Store truncated for summary
        })

        # Save full base64 to individual file
        with open(f"{image_config['name']}_base64.txt", "w") as f:
            f.write(base64_data)

    # Rate limiting
    time.sleep(2)

# Summary
print("\n" + "="*60)
print("GENERATION COMPLETE")
print("="*60)
print(f"\nSuccessfully generated {len(results)}/{len(dispensary_images)} images:")
for result in results:
    print(f"  ✓ {result['name']}: {result['filename']}")

# Save summary
with open("dispensary_images_manifest.json", "w") as f:
    json.dump(results, f, indent=2)

print(f"\nManifest saved to dispensary_images_manifest.json")
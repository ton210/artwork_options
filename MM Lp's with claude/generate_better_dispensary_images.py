import requests
import json
import base64
import time
from datetime import datetime

FREEPIK_API_KEY = 'FPSX381b01bdceb04b9fa3c51f52816cfacd'

def generate_image(prompt, image_name):
    """Generate a single high-quality image with Freepik API"""

    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    # Enhanced data for better quality
    data = {
        "prompt": prompt + ", bright white background, professional product photography, commercial lighting, ultra high quality, sharp focus, clean minimal style",
        "negative_prompt": "dark, dim, low light, night, shadow, blurry, text, logo, watermark, brand names, low quality, cartoon, illustration",
        "guidance_scale": 7.5,
        "seed": None,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        },
        "styling": {
            "style": "photo",
            "color": "vibrant",
            "lightning": "studio",
            "framing": "medium_shot",
            "photography": "commercial"
        }
    }

    print(f"\nGenerating {image_name}...")
    print(f"Prompt: {prompt[:80]}...")

    try:
        response = requests.post(url, headers=headers, json=data)

        if response.status_code == 200:
            result = response.json()
            if 'data' in result and len(result['data']) > 0:
                image_data = result['data'][0]

                if 'base64' in image_data:
                    # Save the image
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    filename = f"dispensary_v2_{image_name}_{timestamp}.png"

                    with open(filename, "wb") as f:
                        f.write(base64.b64decode(image_data['base64']))

                    print(f"✓ Saved as {filename}")
                    return image_data['base64'], filename

        else:
            print(f"✗ Error: {response.status_code}")

    except Exception as e:
        print(f"✗ Exception: {e}")

    return None, None

# Better, more specific prompts for dispensary images
dispensary_images_v2 = [
    {
        "name": "customer_experience",
        "prompt": "Happy customer in upscale cannabis dispensary holding premium black aluminum herb grinder with green accents, bright modern retail store interior, white walls, professional lighting"
    },
    {
        "name": "product_display",
        "prompt": "Premium display case in modern dispensary showing organized rows of black aluminum herb grinders and colorful rolling trays on white shelves, bright commercial lighting, clean aesthetic"
    },
    {
        "name": "profit_concept",
        "prompt": "Stack of hundred dollar bills next to premium black herb grinders on white counter, calculator showing profits, bright office lighting, business success concept"
    },
    {
        "name": "social_media_moment",
        "prompt": "Hands holding smartphone taking photo of premium black herb grinder with custom green logo on white background, Instagram post concept, bright natural lighting"
    }
]

# Store results
results = []

print("="*60)
print("GENERATING IMPROVED DISPENSARY IMAGES")
print("="*60)

for i, image_config in enumerate(dispensary_images_v2, 1):
    print(f"\n[{i}/{len(dispensary_images_v2)}] {image_config['name']}")

    base64_data, filename = generate_image(
        image_config['prompt'],
        image_config['name']
    )

    if base64_data:
        results.append({
            "name": image_config['name'],
            "filename": filename,
            "base64_preview": base64_data[:50] + "..."
        })

        # Save full base64 to individual file
        with open(f"dispensary_v2_{image_config['name']}_base64.txt", "w") as f:
            f.write(base64_data)

    # Rate limiting
    time.sleep(2)

# Summary
print("\n" + "="*60)
print("GENERATION COMPLETE")
print("="*60)
print(f"\nSuccessfully generated {len(results)}/{len(dispensary_images_v2)} images:")
for result in results:
    print(f"  ✓ {result['name']}: {result['filename']}")

# Save manifest
with open("dispensary_images_v2_manifest.json", "w") as f:
    json.dump(results, f, indent=2)

print(f"\nManifest saved to dispensary_images_v2_manifest.json")
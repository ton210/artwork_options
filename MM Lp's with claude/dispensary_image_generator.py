import requests
import json
import base64
import os
from datetime import datetime

# Freepik API credentials
FREEPIK_API_KEY = 'FPSX381b01bdceb04b9fa3c51f52816cfacd'

def generate_dispensary_image():
    """Generate a specific, thoughtful image for dispensaries"""

    # Very specific prompt based on what would resonate with dispensary owners
    prompt = """Modern upscale cannabis dispensary interior with professional budtender showing
    custom branded black aluminum grinders and rolling trays to a customer at a sleek glass display case.
    Clean white walls, professional lighting, organized product shelves in background.
    The accessories have subtle green branding. Professional retail environment,
    similar to an Apple Store aesthetic but for cannabis. Bright, welcoming, professional photography.
    Focus on the branded accessories being presented. No cannabis plants or leaves visible."""

    url = "https://api.freepik.com/v1/ai/text-to-image"

    headers = {
        "x-freepik-api-key": FREEPIK_API_KEY,
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    data = {
        "prompt": prompt,
        "negative_prompt": "marijuana leaves, weed, drugs, smoke, low quality, dark, dingy, unprofessional, cluttered",
        "guidance_scale": 8,
        "seed": 2024,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        },
        "styling": {
            "style": "photo",
            "color": "colorful",
            "lightning": "warm",
            "framing": "medium_shot"
        }
    }

    print("Generating dispensary image with Freepik API...")
    print(f"Prompt: {prompt[:100]}...")

    try:
        response = requests.post(url, headers=headers, json=data)
        print(f"Response status: {response.status_code}")

        if response.status_code == 200:
            result = response.json()
            if 'data' in result and len(result['data']) > 0:
                image_data = result['data'][0]

                # Save the image
                if 'base64' in image_data:
                    image_base64 = image_data['base64']

                    # Save as file
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    filename = f"dispensary_hero_image_{timestamp}.png"

                    with open(filename, "wb") as f:
                        f.write(base64.b64decode(image_base64))

                    print(f"✓ Image saved as {filename}")
                    return image_base64
                elif 'url' in image_data:
                    print(f"Image URL: {image_data['url']}")
                    return image_data['url']
        else:
            print(f"Error: {response.text}")

    except Exception as e:
        print(f"Exception: {e}")

    return None

# Generate the image
image_result = generate_dispensary_image()

if image_result:
    print("\n✓ Image generation successful!")
    if isinstance(image_result, str) and image_result.startswith('http'):
        print(f"Image URL: {image_result}")
    else:
        print("Image saved locally and base64 data captured")
        # Save base64 for use in HTML
        with open('dispensary_image_base64.txt', 'w') as f:
            f.write(image_result)
else:
    print("\n✗ Image generation failed")
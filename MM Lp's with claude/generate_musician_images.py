import requests
import json
import time
from datetime import datetime

# Freepik API key
api_key = 'Frpk-91ecd652-abaa-475e-9a3f-f89d4fb43417'

# API endpoint
api_url = 'https://api.freepik.com/v1/ai/text-to-image'

# Headers for API requests
headers = {
    'x-freepik-api-key': api_key,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# Create 4 images for the Musicians & Artists landing page
images_to_generate = [
    {
        'name': 'musician_hero',
        'prompt': 'Professional product photography of custom engraved black aluminum herb grinders arranged on a concert venue merch table, band posters in background, stage lighting creating dramatic shadows, rock and hip-hop aesthetic, premium merchandise display, photorealistic, high detail, commercial photography style',
        'description': 'Hero image showing custom grinders as premium band merchandise'
    },
    {
        'name': 'musician_profit',
        'prompt': 'Split screen comparison chart showing profit margins, left side showing low margin t-shirts and stickers, right side showing high margin premium aluminum grinders, clean infographic style, green dollar signs, professional business presentation, photorealistic render',
        'description': 'Profit margin comparison visualization'
    },
    {
        'name': 'musician_tour',
        'prompt': 'Concert venue merchandise booth with fans purchasing custom black aluminum grinders, excited customers, tour exclusive designs visible, backstage pass aesthetic, evening concert lighting, crowded merch table, photorealistic, commercial photography',
        'description': 'Tour merch table with fans buying custom grinders'
    },
    {
        'name': 'musician_showcase',
        'prompt': 'Collection of custom engraved aluminum grinders with different band logos and tour dates, arranged artistically on black velvet, studio lighting, collectible items display, premium packaging visible, photorealistic product photography, high-end merchandise',
        'description': 'Showcase of various artist custom grinder designs'
    }
]

print("🎸 GENERATING IMAGES FOR MUSICIANS & ARTISTS LANDING PAGE")
print("=" * 60)

generated_images = []

for img in images_to_generate:
    print(f"\n📸 Generating: {img['name']}")
    print(f"   Description: {img['description']}")

    # Prepare the request
    data = {
        "prompt": img['prompt'],
        "negative_prompt": "cartoon, illustration, low quality, blurry, amateur, stock photo watermark, text overlay, bad lighting, plastic materials, cheap looking, unprofessional",
        "guidance_scale": 8,
        "seed": None,
        "num_images": 1,
        "image": {
            "size": "landscape_16_9"
        }
    }

    # Make the API request
    try:
        response = requests.post(api_url, headers=headers, json=data)

        if response.status_code == 200:
            result = response.json()
            if result.get('data') and len(result['data']) > 0:
                image_data = result['data'][0]
                generated_images.append({
                    'name': img['name'],
                    'url': image_data.get('base64') or image_data.get('url'),
                    'description': img['description']
                })
                print(f"   ✓ Successfully generated!")
            else:
                print(f"   ✗ No image data in response")
        else:
            print(f"   ✗ API Error: {response.status_code}")
            print(f"   Response: {response.text}")

    except Exception as e:
        print(f"   ✗ Error: {str(e)}")

    # Rate limiting - wait between requests
    if img != images_to_generate[-1]:
        print("   ⏳ Waiting before next generation...")
        time.sleep(3)

# Save the results
print("\n" + "=" * 60)
print("📊 GENERATION SUMMARY:")
print(f"   • Total requested: {len(images_to_generate)}")
print(f"   • Successfully generated: {len(generated_images)}")

if generated_images:
    # Save to JSON for the upload script
    output_file = f'musician_images_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    with open(output_file, 'w') as f:
        json.dump(generated_images, f, indent=2)

    print(f"\n💾 Image data saved to: {output_file}")
    print("\n🎸 Ready for upload to WebDAV!")
else:
    print("\n⚠️  No images were successfully generated")
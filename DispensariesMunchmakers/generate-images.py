#!/usr/bin/env python3
"""
Generate images for dispensary site using Gemini Image Generation
"""

import time
import os
from google import genai
from google.genai import types

API_KEY = "AQ.Ab8RN6KCplH4yy6tuE2ulMGTcnrBOEUUOwX1OnStyEcoxmqJXw"

# Initialize client with vertexai=True
client = genai.Client(vertexai=True, api_key=API_KEY)

# Configuration
config = types.GenerateContentConfig(
    response_modalities=["IMAGE", "TEXT"]
)

def generate_image(prompt, filename):
    """Generate an image from a text prompt"""
    print(f"Generating: {filename}...")
    print(f"Prompt: {prompt}\n")

    try:
        response = client.models.generate_content(
            model="gemini-2.0-flash-preview-image-generation",
            contents=[types.Content(
                role="user",
                parts=[types.Part.from_text(text=prompt)]
            )],
            config=config,
        )

        # Save image
        for part in response.candidates[0].content.parts:
            if hasattr(part, 'inline_data') and part.inline_data:
                output_path = os.path.join('src/public/images', filename)
                with open(output_path, "wb") as f:
                    f.write(part.inline_data.data)
                print(f"✓ Saved: {output_path}\n")
                return True

        print(f"✗ No image data in response for {filename}\n")
        return False

    except Exception as e:
        print(f"✗ Error generating {filename}: {str(e)}\n")
        return False

def main():
    # Create images directory if it doesn't exist
    os.makedirs('src/public/images', exist_ok=True)
    os.makedirs('src/public/images/blog', exist_ok=True)

    images = [
        # Homepage hero image
        {
            "prompt": "A modern, welcoming cannabis dispensary storefront with large windows, green signage, and professional aesthetic. Bright daylight, clean architectural design, inviting entrance. Photorealistic, high quality, professional photography style.",
            "filename": "homepage-hero.jpg"
        },

        # Homepage features background
        {
            "prompt": "Abstract pattern of cannabis leaves in gradient green colors, subtle and professional, modern design, suitable as a website background. Soft focus, calming aesthetic, light and airy.",
            "filename": "features-bg.jpg"
        },

        # Blog category images
        {
            "prompt": "Stack of educational books about cannabis with a diploma, graduation cap, and cannabis leaf on a clean white desk. Professional educational setting, bright natural lighting, photorealistic.",
            "filename": "blog/education-category.jpg"
        },

        {
            "prompt": "Modern cannabis dispensary interior with clean glass display cases, organized products, professional lighting, and welcoming atmosphere. Photorealistic, high-end retail design.",
            "filename": "blog/guides-category.jpg"
        },

        {
            "prompt": "Person's hands counting money and cannabis products on a table with a calculator, budgeting concept. Clean, professional photography, warm lighting.",
            "filename": "blog/tips-category.jpg"
        },

        # Specific blog post images
        {
            "prompt": "Person standing in front of multiple cannabis dispensary storefronts, comparing options, making a decision. Professional photography, bright daylight, modern dispensaries.",
            "filename": "blog/choosing-dispensary.jpg"
        },

        {
            "prompt": "Friendly budtender behind a counter helping a customer, professional dispensary setting, both people smiling, welcoming atmosphere. Clean, bright, photorealistic.",
            "filename": "blog/dispensary-etiquette.jpg"
        },

        {
            "prompt": "Three different cannabis plant types side by side: short bushy indica, tall thin sativa, and medium hybrid plant. Botanical illustration style, detailed, educational, professional.",
            "filename": "blog/strains-guide.jpg"
        },

        {
            "prompt": "Colorful cannabis gummies and chocolate edibles arranged artistically on a white plate, vibrant colors, professional food photography, well-lit, appetizing presentation.",
            "filename": "blog/edibles-guide.jpg"
        },

        {
            "prompt": "Close-up of cannabis flower in a magnifying glass showing trichomes and plant details. Macro photography, detailed, scientific, educational.",
            "filename": "blog/thc-cbd-explained.jpg"
        },

        {
            "prompt": "Cannabis concentrates: shatter, wax, and live resin in glass containers on a clean surface. Professional product photography, good lighting, detailed textures.",
            "filename": "blog/concentrates-guide.jpg"
        },

        {
            "prompt": "Vape pen and cartridges on a modern minimalist background, sleek product photography, professional lighting, tech aesthetic.",
            "filename": "blog/vaping-guide.jpg"
        },

        {
            "prompt": "Glass mason jars with cannabis flower, humidity packs, and labels in an organized storage setup. Clean, professional, organized aesthetic.",
            "filename": "blog/storage-guide.jpg"
        },

        {
            "prompt": "Cannabis accessories: grinder, rolling tray, storage jar, and smell-proof bag arranged neatly on a wood surface. Professional product photography, good lighting.",
            "filename": "blog/accessories-guide.jpg"
        },

        # State/location background
        {
            "prompt": "Abstract map of United States with location pins and connecting lines, modern infographic style, green and blue color scheme, clean professional design.",
            "filename": "states-background.jpg"
        }
    ]

    print(f"Starting generation of {len(images)} images...\n")
    print("=" * 60)
    print()

    successful = 0
    failed = 0

    for i, img in enumerate(images, 1):
        print(f"[{i}/{len(images)}]")

        success = generate_image(img['prompt'], img['filename'])

        if success:
            successful += 1
        else:
            failed += 1

        # Rate limiting: wait 2-3 seconds between requests
        if i < len(images):
            print("Waiting 2.5 seconds...\n")
            time.sleep(2.5)

    print("=" * 60)
    print(f"\nGeneration complete!")
    print(f"✓ Successful: {successful}")
    print(f"✗ Failed: {failed}")
    print(f"\nImages saved to: src/public/images/")

if __name__ == "__main__":
    main()

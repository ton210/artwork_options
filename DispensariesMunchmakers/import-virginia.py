#!/usr/bin/env python3
"""Import Virginia dispensaries"""

import psycopg2
import os
import re

# Virginia dispensaries from Weedmaps research
VIRGINIA_DISPENSARIES = [
    {"name": "RISE Dispensary Lynchburg", "city": "Lynchburg", "address": "3405 Candlers Mountain Rd", "website": "https://risecannabis.com/dispensary/virginia/lynchburg/"},
    {"name": "Cannabist Henrico", "city": "Henrico", "address": "8804 W Broad St", "website": "https://gocannabist.com/locations/henrico/"},
    {"name": "RISE Dispensary Salem", "city": "Salem", "address": "1321 W Main St", "website": "https://risecannabis.com/dispensary/virginia/salem/"},
    {"name": "RISE Dispensary Danville", "city": "Danville", "address": "1191 Piney Forest Rd", "website": "https://risecannabis.com/dispensary/virginia/danville/"},
    {"name": "Beyond Hello Manassas", "city": "Manassas", "address": "8384 Sudley Rd", "website": "https://beyond-hello.com/virginia-dispensaries/manassas/"},
    {"name": "Beyond Hello Woodbridge", "city": "Woodbridge", "address": "14221 Smoketown Rd", "website": "https://beyond-hello.com/virginia-dispensaries/woodbridge/"},
    {"name": "RISE Dispensary Christiansburg", "city": "Christiansburg", "address": "2550 Market St NE", "website": "https://risecannabis.com/dispensary/virginia/christiansburg/"},
    {"name": "Beyond Hello Fairfax", "city": "Fairfax", "address": "10780 Lee Hwy", "website": "https://beyond-hello.com/virginia-dispensaries/fairfax/"},
    {"name": "Beyond Hello Sterling", "city": "Sterling", "address": "46301 Potomac Run Plaza", "website": "https://beyond-hello.com/virginia-dispensaries/sterling/"},
    {"name": "Beyond Hello Alexandria", "city": "Alexandria", "address": "6303 Richmond Hwy", "website": "https://beyond-hello.com/virginia-dispensaries/alexandria/"},
    {"name": "gLeaf Dispensary Richmond", "city": "Richmond", "address": "5305 W Broad St", "website": "https://gleaf.com/virginia/"},
    {"name": "Green Leaf Medical Dispensary", "city": "Portsmouth", "address": "3101 Frederick Blvd", "website": "https://greenleafmedical.com/"},
    {"name": "Columbia Care Portsmouth", "city": "Portsmouth", "address": "3617 High St", "website": "https://col-care.com/"},
    {"name": "Dharma Cannabis Abingdon", "city": "Abingdon", "address": "870 E Main St", "website": "https://dharmacannabis.com/"},
    {"name": "RISE Dispensary Roanoke", "city": "Roanoke", "address": "1942 Electric Rd SW", "website": "https://risecannabis.com/dispensary/virginia/roanoke/"},
]

def slugify(text):
    """Convert text to slug"""
    text = text.lower()
    text = re.sub(r'[^a-z0-9]+', '-', text)
    text = text.strip('-')
    return text

def main():
    conn = psycopg2.connect(os.environ['DATABASE_URL'])
    cur = conn.cursor()

    # Get Virginia state id
    cur.execute("SELECT id FROM states WHERE abbreviation = 'VA'")
    row = cur.fetchone()
    if not row:
        print("Virginia not found in states table!")
        return

    state_id = row[0]
    print(f"Virginia state_id: {state_id}")

    # Get or create counties for each city
    added = 0
    skipped = 0

    for disp in VIRGINIA_DISPENSARIES:
        city = disp['city']

        # Check if county exists for this city (using city name as county for simplicity)
        cur.execute("""
            SELECT id FROM counties
            WHERE state_id = %s AND (name ILIKE %s OR name ILIKE %s)
        """, (state_id, city, f"{city} County"))

        county_row = cur.fetchone()

        if not county_row:
            # Create county
            county_slug = slugify(city)
            cur.execute("""
                INSERT INTO counties (name, slug, state_id)
                VALUES (%s, %s, %s)
                RETURNING id
            """, (city, county_slug, state_id))
            county_id = cur.fetchone()[0]
            print(f"  Created county: {city}")
        else:
            county_id = county_row[0]

        # Check if dispensary already exists
        cur.execute("""
            SELECT id FROM dispensaries
            WHERE name ILIKE %s AND county_id = %s
        """, (disp['name'], county_id))

        if cur.fetchone():
            print(f"  Skipping (exists): {disp['name']}")
            skipped += 1
            continue

        # Insert dispensary
        slug = slugify(disp['name'])
        cur.execute("""
            INSERT INTO dispensaries (name, slug, city, address_street, website, county_id, is_active)
            VALUES (%s, %s, %s, %s, %s, %s, true)
            RETURNING id
        """, (disp['name'], slug, city, disp.get('address', ''), disp.get('website', ''), county_id))

        new_id = cur.fetchone()[0]
        print(f"  Added: {disp['name']} (ID: {new_id})")
        added += 1

    conn.commit()
    print(f"\nSummary: Added {added}, Skipped {skipped}")

    # Count Virginia dispensaries
    cur.execute("""
        SELECT COUNT(*) FROM dispensaries d
        JOIN counties c ON d.county_id = c.id
        WHERE c.state_id = %s AND d.is_active = true
    """, (state_id,))
    total = cur.fetchone()[0]
    print(f"Total Virginia dispensaries: {total}")

    conn.close()

if __name__ == "__main__":
    main()

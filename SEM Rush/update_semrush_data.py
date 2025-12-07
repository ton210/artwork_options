#!/usr/bin/env python3
"""
Update SEMrush CSV data:
1. Remove 404 international pages
2. Update redirected URLs with canonical URLs
"""
import csv
import json
from datetime import datetime

# Input files
original_csv = "/Users/tomernahumi/Documents/Plugins/SEM Rush/munchmakers.com-organic.Positions-us-20251121-2025-11-22T07_15_44Z.csv"
status_json = "/Users/tomernahumi/Documents/Plugins/SEM Rush/url_status_report.json"

# Output files
updated_csv = "/Users/tomernahumi/Documents/Plugins/SEM Rush/munchmakers.com-organic-UPDATED.csv"
changes_log = "/Users/tomernahumi/Documents/Plugins/SEM Rush/phase2_changes_log.md"

def load_url_status():
    """Load URL status data"""
    with open(status_json, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # Create lookup dictionaries
    url_status = {}
    url_redirects = {}

    for result in data['results']:
        url = result['url']
        url_status[url] = result['status_code']

        # If it redirects, store the final URL
        if result['redirect_chain']:
            url_redirects[url] = result['final_url']

    return url_status, url_redirects

def is_international_404(url, status_code):
    """Check if URL is an international 404"""
    if status_code != 404:
        return False

    # International language codes
    intl_codes = ['/de/', '/fr/', '/it/', '/ja/', '/iw/', '/es/', '/pl/', '/fi/', '/cs/', '/da/', '/hu/', '/lt/']

    return any(code in url for code in intl_codes)

def update_semrush_data():
    """Update SEMrush CSV with canonical URLs and remove 404s"""

    print("="*70)
    print("UPDATING SEMRUSH DATA - PHASE 2")
    print("="*70)

    # Load URL status
    print("\nLoading URL status data...")
    url_status, url_redirects = load_url_status()

    # Track changes
    stats = {
        'total_rows': 0,
        'removed_404': 0,
        'updated_redirects': 0,
        'kept': 0
    }

    removed_urls = []
    updated_urls = []

    # Read original CSV and write updated version
    print(f"Reading original CSV: {original_csv}")

    with open(original_csv, 'r', encoding='utf-8') as infile, \
         open(updated_csv, 'w', newline='', encoding='utf-8') as outfile:

        reader = csv.DictReader(infile)
        fieldnames = reader.fieldnames
        writer = csv.DictWriter(outfile, fieldnames=fieldnames)
        writer.writeheader()

        for row in reader:
            stats['total_rows'] += 1
            url = row['URL'].strip()

            # Check if URL exists in our status data
            status_code = url_status.get(url)

            # Remove international 404s
            if is_international_404(url, status_code):
                stats['removed_404'] += 1
                removed_urls.append({
                    'url': url,
                    'keyword': row.get('Keyword', ''),
                    'position': row.get('Position', ''),
                    'traffic': row.get('Traffic', '')
                })
                continue  # Skip this row

            # Update redirected URLs with canonical URL
            if url in url_redirects:
                original_url = url
                canonical_url = url_redirects[url]

                # Only update if URLs are different
                if original_url != canonical_url:
                    row['URL'] = canonical_url
                    stats['updated_redirects'] += 1
                    updated_urls.append({
                        'original': original_url,
                        'canonical': canonical_url,
                        'keyword': row.get('Keyword', ''),
                        'position': row.get('Position', '')
                    })

            # Write the row
            writer.writerow(row)
            stats['kept'] += 1

    print(f"\n✓ Updated CSV saved to: {updated_csv}")

    # Generate changes log
    generate_changes_log(stats, removed_urls, updated_urls)

    # Print summary
    print("\n" + "="*70)
    print("SUMMARY")
    print("="*70)
    print(f"Total rows processed: {stats['total_rows']}")
    print(f"Rows kept: {stats['kept']}")
    print(f"International 404s removed: {stats['removed_404']}")
    print(f"Redirects updated: {stats['updated_redirects']}")
    print(f"\nFinal row count: {stats['kept']}")
    print("="*70)

def generate_changes_log(stats, removed_urls, updated_urls):
    """Generate a markdown log of changes"""

    log = []
    log.append("# PHASE 2 - SEMRUSH DATA UPDATE LOG")
    log.append(f"**Update Date:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.append("")

    # Summary
    log.append("## SUMMARY")
    log.append("")
    log.append(f"- **Total rows processed:** {stats['total_rows']}")
    log.append(f"- **Rows kept:** {stats['kept']}")
    log.append(f"- **International 404s removed:** {stats['removed_404']}")
    log.append(f"- **Redirects updated to canonical URLs:** {stats['updated_redirects']}")
    log.append("")

    # Removed URLs
    if removed_urls:
        log.append("## REMOVED URLS (International 404s)")
        log.append("")
        log.append(f"Removed {len(removed_urls)} international pages that returned 404 errors:")
        log.append("")

        # Group by language
        by_lang = {}
        for item in removed_urls:
            url = item['url']
            # Determine language
            for lang in ['/de/', '/fr/', '/it/', '/ja/', '/iw/', '/es/', '/pl/', '/fi/', '/cs/', '/da/', '/hu/', '/lt/']:
                if lang in url:
                    lang_code = lang.strip('/')
                    if lang_code not in by_lang:
                        by_lang[lang_code] = []
                    by_lang[lang_code].append(item)
                    break

        for lang, items in sorted(by_lang.items()):
            log.append(f"### {lang.upper()} - {len(items)} URLs")
            log.append("")
            for item in items[:10]:  # Show first 10
                log.append(f"- `{item['url']}`")
                log.append(f"  - Keyword: {item['keyword']}")
                log.append(f"  - Position: {item['position']}")
                log.append("")

            if len(items) > 10:
                log.append(f"... and {len(items) - 10} more")
                log.append("")

    # Updated URLs
    if updated_urls:
        log.append("## UPDATED URLS (Redirects → Canonical)")
        log.append("")
        log.append(f"Updated {len(updated_urls)} URLs that were redirecting:")
        log.append("")
        log.append("| Original URL | Canonical URL | Keyword | Position |")
        log.append("|--------------|---------------|---------|----------|")

        for item in updated_urls[:20]:  # Show first 20
            orig = item['original'][:40] + '...' if len(item['original']) > 40 else item['original']
            canon = item['canonical'][:40] + '...' if len(item['canonical']) > 40 else item['canonical']
            keyword = item['keyword'][:30] + '...' if len(item['keyword']) > 30 else item['keyword']
            log.append(f"| {orig} | {canon} | {keyword} | {item['position']} |")

        if len(updated_urls) > 20:
            log.append(f"| ... | ... | ... | ... |")
            log.append(f"| **+{len(updated_urls) - 20} more** | | | |")
        log.append("")

    # Save log
    with open(changes_log, 'w', encoding='utf-8') as f:
        f.write('\n'.join(log))

    print(f"✓ Changes log saved to: {changes_log}")

if __name__ == '__main__':
    update_semrush_data()

import requests
from bs4 import BeautifulSoup
import json
import time

def scrape_exhibitors_page(session, page_num):
    """Scrape exhibitors from a specific page"""
    # The site uses AJAX-style pagination with page parameter
    url = f"https://mjbizcon2025.smallworldlabs.com/exhibitors?page={page_num}"

    response = session.get(url)
    response.raise_for_status()
    soup = BeautifulSoup(response.content, 'html.parser')

    exhibitors = []

    # Find all table cells with exhibitor links
    exhibitor_links = soup.find_all('a', class_='generic-option-link')

    for link in exhibitor_links:
        name = link.get_text(strip=True)
        relative_url = link.get('href')

        # Skip if this doesn't look like a company link (e.g., booth numbers)
        if not relative_url or not relative_url.startswith('/co/'):
            continue

        full_url = f"https://mjbizcon2025.smallworldlabs.com{relative_url}"

        exhibitors.append({
            'name': name,
            'url': full_url
        })

    return exhibitors

def main():
    """Main function to scrape all 11 pages"""
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    })

    all_exhibitors = []

    print("Scraping MJ Bizcon 2025 Exhibitors...")

    for page in range(1, 12):  # Pages 1 through 11
        print(f"Scraping page {page}/11...")
        try:
            exhibitors = scrape_exhibitors_page(session, page)
            all_exhibitors.extend(exhibitors)
            print(f"  Found {len(exhibitors)} exhibitors on page {page}")

            # Be polite - add a small delay between requests
            time.sleep(1)
        except Exception as e:
            print(f"Error scraping page {page}: {e}")

    # Save to JSON file
    output_file = 'mjbizcon_exhibitors.json'
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(all_exhibitors, f, indent=2, ensure_ascii=False)

    print(f"\nScraping complete!")
    print(f"Total exhibitors found: {len(all_exhibitors)}")
    print(f"Results saved to: {output_file}")

    # Also save as CSV for easy viewing
    csv_file = 'mjbizcon_exhibitors.csv'
    with open(csv_file, 'w', encoding='utf-8') as f:
        f.write("Name,URL\n")
        for exhibitor in all_exhibitors:
            # Escape commas in names
            name = exhibitor['name'].replace('"', '""')
            f.write(f'"{name}",{exhibitor["url"]}\n')

    print(f"CSV file saved to: {csv_file}")

if __name__ == "__main__":
    main()
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException
import csv
import time
import json

def scrape_all_pages():
    """Scrape all exhibitor pages using Selenium"""

    # Setup Chrome in headless mode
    chrome_options = Options()
    chrome_options.add_argument('--headless')
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')
    chrome_options.add_argument('--disable-gpu')
    chrome_options.add_argument('--window-size=1920,1080')
    chrome_options.add_argument('user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36')

    driver = webdriver.Chrome(options=chrome_options)

    try:
        print("Opening MJ Bizcon exhibitors page...")
        driver.get("https://mjbizcon2025.smallworldlabs.com/exhibitors")

        # Wait for initial page load
        wait = WebDriverWait(driver, 10)
        wait.until(EC.presence_of_element_located((By.CLASS_NAME, "generic-option-link")))

        all_exhibitors = {}  # Use dict to automatically deduplicate by name
        current_page = 1
        max_pages = 11

        print(f"\nStarting to scrape {max_pages} pages...\n")

        while current_page <= max_pages:
            print(f"Scraping page {current_page}/{max_pages}...")

            # Wait for table to be visible
            time.sleep(2)  # Give time for page to fully load

            # Find all exhibitor links on current page
            exhibitor_links = driver.find_elements(By.CLASS_NAME, "generic-option-link")

            page_count = 0
            for link in exhibitor_links:
                try:
                    name = link.text.strip()
                    url = link.get_attribute('href')

                    # Only get company pages (not booth numbers)
                    if url and '/co/' in url and name:
                        all_exhibitors[name] = url
                        page_count += 1
                except:
                    continue

            print(f"  Found {page_count} unique exhibitors on page {current_page}")
            print(f"  Total unique so far: {len(all_exhibitors)}")

            # Try to click next page button
            if current_page < max_pages:
                try:
                    # Find and click the "Next" button
                    next_button = wait.until(EC.element_to_be_clickable((
                        By.CSS_SELECTOR,
                        "a.pager-right-next[aria-label*='Next Page']"
                    )))

                    driver.execute_script("arguments[0].scrollIntoView(true);", next_button)
                    time.sleep(0.5)
                    driver.execute_script("arguments[0].click();", next_button)

                    # Wait for page to change
                    time.sleep(2)

                    current_page += 1

                except TimeoutException:
                    print(f"  Could not find next button, stopping at page {current_page}")
                    break
                except Exception as e:
                    print(f"  Error clicking next: {e}")
                    break
            else:
                current_page += 1

        print(f"\n{'='*60}")
        print(f"Scraping complete!")
        print(f"Total unique exhibitors found: {len(all_exhibitors)}")
        print(f"{'='*60}\n")

        # Convert to list format
        exhibitors_list = [
            {'Name': name, 'URL': url}
            for name, url in sorted(all_exhibitors.items())
        ]

        # Save to CSV
        csv_file = 'mjbizcon_exhibitors.csv'
        with open(csv_file, 'w', encoding='utf-8', newline='') as f:
            writer = csv.DictWriter(f, fieldnames=['Name', 'URL'])
            writer.writeheader()
            writer.writerows(exhibitors_list)

        print(f"Saved to: {csv_file}")

        # Also save to JSON
        json_file = 'mjbizcon_exhibitors.json'
        with open(json_file, 'w', encoding='utf-8') as f:
            json.dump(exhibitors_list, f, indent=2, ensure_ascii=False)

        print(f"Saved to: {json_file}")

        # Show sample
        print(f"\nFirst 10 exhibitors:")
        for i, exhibitor in enumerate(exhibitors_list[:10], 1):
            print(f"  {i}. {exhibitor['Name']}")

        return exhibitors_list

    finally:
        driver.quit()

if __name__ == "__main__":
    exhibitors = scrape_all_pages()
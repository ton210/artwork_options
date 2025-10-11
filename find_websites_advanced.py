import csv
import json
import time
import re
from concurrent.futures import ThreadPoolExecutor, as_completed
from urllib.parse import urlparse, quote_plus, unquote
import requests
from bs4 import BeautifulSoup
import random

class AdvancedWebsiteFinder:
    def __init__(self, max_workers=8):
        self.max_workers = max_workers
        self.processed = 0
        self.found = 0
        self.total = 0
        self.user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]

    def get_session(self):
        """Create a new session with random user agent"""
        session = requests.Session()
        session.headers.update({
            'User-Agent': random.choice(self.user_agents),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.9',
            'Accept-Encoding': 'gzip, deflate, br',
            'DNT': '1',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1'
        })
        return session

    def search_brave(self, company_name):
        """Search using Brave Search (no API key needed)"""
        try:
            session = self.get_session()
            query = f"{company_name} official website"
            url = f"https://search.brave.com/search?q={quote_plus(query)}"

            response = session.get(url, timeout=10)
            if response.status_code != 200:
                return []

            soup = BeautifulSoup(response.content, 'html.parser')
            links = []

            # Brave search results
            for result in soup.find_all('a', href=True):
                href = result.get('href', '')
                if href.startswith('http') and self.is_valid_url(href):
                    links.append(href)

            return links[:5]
        except Exception as e:
            return []

    def search_startpage(self, company_name):
        """Search using Startpage (privacy-focused search)"""
        try:
            session = self.get_session()
            query = f"{company_name} website"
            url = f"https://www.startpage.com/do/search?q={quote_plus(query)}"

            response = session.get(url, timeout=10)
            if response.status_code != 200:
                return []

            soup = BeautifulSoup(response.content, 'html.parser')
            links = []

            for result in soup.find_all('a', class_='w-gl__result-url'):
                href = result.get('href', '')
                if href and self.is_valid_url(href):
                    links.append(href)

            return links[:5]
        except Exception as e:
            return []

    def search_bing(self, company_name):
        """Search using Bing"""
        try:
            session = self.get_session()
            query = f"{company_name} cannabis official website"
            url = f"https://www.bing.com/search?q={quote_plus(query)}"

            response = session.get(url, timeout=10)
            if response.status_code != 200:
                return []

            soup = BeautifulSoup(response.content, 'html.parser')
            links = []

            # Bing organic results
            for result in soup.find_all('li', class_='b_algo'):
                a_tag = result.find('a')
                if a_tag and a_tag.get('href'):
                    href = a_tag.get('href')
                    if self.is_valid_url(href):
                        links.append(href)

            return links[:5]
        except Exception as e:
            return []

    def search_duckduckgo_lite(self, company_name):
        """Search DuckDuckGo Lite version (easier to parse)"""
        try:
            session = self.get_session()
            query = f"{company_name} website"
            url = f"https://lite.duckduckgo.com/lite/?q={quote_plus(query)}"

            response = session.get(url, timeout=10)
            soup = BeautifulSoup(response.content, 'html.parser')

            links = []
            for link in soup.find_all('a', class_='result-link'):
                href = link.get('href', '')
                if href and self.is_valid_url(href):
                    links.append(href)

            return links[:5]
        except Exception as e:
            return []

    def search_searx(self, company_name):
        """Search using public SearX instance"""
        try:
            session = self.get_session()
            query = f"{company_name} official website"
            # Using a public SearX instance
            url = f"https://searx.be/search?q={quote_plus(query)}&format=json"

            response = session.get(url, timeout=10)
            if response.status_code != 200:
                return []

            data = response.json()
            links = []

            for result in data.get('results', [])[:5]:
                url = result.get('url', '')
                if self.is_valid_url(url):
                    links.append(url)

            return links
        except Exception as e:
            return []

    def guess_domain_variations(self, company_name):
        """Generate and test common domain variations"""
        # Clean company name
        clean_name = re.sub(r'[^a-zA-Z0-9\s]', '', company_name).lower()
        clean_name = re.sub(r'\s+', '', clean_name)

        # Remove common business suffixes
        suffixes = ['inc', 'llc', 'ltd', 'corp', 'corporation', 'company', 'co']
        for suffix in suffixes:
            if clean_name.endswith(suffix):
                clean_name = clean_name[:-len(suffix)]

        clean_name = clean_name.strip()

        # Generate variations
        variations = [
            f"https://{clean_name}.com",
            f"https://www.{clean_name}.com",
            f"https://{clean_name}.co",
            f"https://www.{clean_name}.co",
            f"https://{clean_name}.net",
            f"https://{clean_name}cannabis.com",
            f"https://{clean_name}global.com",
        ]

        valid_urls = []
        for url in variations:
            if len(clean_name) > 3 and self.check_url_accessible(url):
                valid_urls.append(url)
                if len(valid_urls) >= 1:  # Only need one working URL
                    break

        return valid_urls

    def is_valid_url(self, url):
        """Check if URL is valid and not excluded"""
        if not url or len(url) < 10:
            return False

        try:
            parsed = urlparse(url)
            if not parsed.scheme or not parsed.netloc:
                return False

            # Exclude certain domains
            excluded_keywords = [
                'google.com', 'facebook.com', 'twitter.com', 'linkedin.com',
                'instagram.com', 'youtube.com', 'pinterest.com', 'tiktok.com',
                'mjbizcon', 'smallworldlabs', 'wikipedia', 'yelp.com',
                'yellowpages', 'bbb.org', 'indeed.com', 'glassdoor',
                'bing.com', 'duckduckgo.com', 'brave.com', 'startpage.com',
                'searx', 'reddit.com', 'quora.com'
            ]

            domain = parsed.netloc.lower()
            for excluded in excluded_keywords:
                if excluded in domain:
                    return False

            return True
        except:
            return False

    def extract_domain(self, url):
        """Extract clean domain from URL"""
        try:
            parsed = urlparse(url)
            return f"{parsed.scheme}://{parsed.netloc}"
        except:
            return url

    def check_url_accessible(self, url):
        """Quick check if URL is accessible"""
        try:
            session = self.get_session()
            response = session.head(url, timeout=5, allow_redirects=True)
            if response.status_code < 400:
                return True
            # Try GET if HEAD fails
            response = session.get(url, timeout=5, allow_redirects=True)
            return response.status_code < 400
        except:
            return False

    def calculate_confidence(self, company_name, website_url):
        """Calculate confidence score for a match"""
        confidence = 0
        company_clean = re.sub(r'[^a-z0-9]', '', company_name.lower())
        website_clean = re.sub(r'[^a-z0-9]', '', website_url.lower())

        # Check if significant words from company name appear in domain
        words = [w for w in company_name.lower().split() if len(w) > 3]
        significant_words = [w for w in words if w not in ['corp', 'company', 'inc', 'llc', 'ltd', 'cannabis', 'international']]

        matches = 0
        for word in significant_words[:3]:  # Check first 3 significant words
            if word in website_clean:
                matches += 1
                confidence += 30

        # Bonus for exact match
        if company_clean in website_clean:
            confidence += 20

        # First result bonus
        confidence += 20

        return min(confidence, 100)

    def find_website(self, company_name):
        """Find company website using multiple methods"""
        try:
            all_results = []

            # Method 1: Try domain guessing first (fastest)
            guessed = self.guess_domain_variations(company_name)
            all_results.extend(guessed)

            # If we found something from guessing, verify and return
            if all_results:
                website = self.extract_domain(all_results[0])
                confidence = self.calculate_confidence(company_name, website)
                if confidence >= 50:
                    return website, max(confidence, 75)

            # Method 2: Try multiple search engines
            search_methods = [
                self.search_bing,
                self.search_duckduckgo_lite,
                self.search_brave,
                self.search_searx,
            ]

            for search_method in search_methods:
                try:
                    results = search_method(company_name)
                    if results:
                        all_results.extend(results)
                        break  # Stop after first successful search
                    time.sleep(0.3)  # Small delay between methods
                except:
                    continue

            if not all_results:
                return None, 0

            # Take first result
            website = self.extract_domain(all_results[0])
            confidence = self.calculate_confidence(company_name, website)

            # Verify it's accessible
            if confidence >= 40 and self.check_url_accessible(website):
                confidence = max(confidence, 70)
                return website, confidence

            return None, confidence

        except Exception as e:
            return None, 0

    def process_company(self, row, index):
        """Process a single company"""
        name = row['Name']

        website, confidence = self.find_website(name)

        self.processed += 1

        if website and confidence >= 70:
            self.found += 1
            status = "✓"
            row['Company_Website'] = website
            row['Confidence'] = confidence
            result_text = f"{website} ({confidence}%)"
        else:
            status = "✗"
            row['Company_Website'] = ''
            row['Confidence'] = 0
            result_text = "Not found"

        print(f"[{self.processed}/{self.total}] {status} {name[:45]:<45} | {result_text}")

        time.sleep(random.uniform(0.5, 1.0))  # Random delay to avoid detection

        return row

    def process_all(self, input_csv, output_csv):
        """Process all companies in parallel"""
        # Read input CSV
        with open(input_csv, 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            companies = list(reader)

        self.total = len(companies)

        print(f"\n{'='*90}")
        print(f"Processing {self.total} companies with {self.max_workers} parallel workers")
        print(f"Using multiple search engines + domain guessing")
        print(f"{'='*90}\n")

        results = [None] * len(companies)

        # Process in parallel
        with ThreadPoolExecutor(max_workers=self.max_workers) as executor:
            future_to_index = {
                executor.submit(self.process_company, company, i): i
                for i, company in enumerate(companies)
            }

            for future in as_completed(future_to_index):
                index = future_to_index[future]
                try:
                    result = future.result()
                    results[index] = result
                except Exception as e:
                    print(f"Error: {e}")
                    results[index] = companies[index]
                    results[index]['Company_Website'] = ''
                    results[index]['Confidence'] = 0

        # Write output CSV
        with open(output_csv, 'w', encoding='utf-8', newline='') as f:
            fieldnames = ['Name', 'URL', 'Company_Website', 'Confidence']
            writer = csv.DictWriter(f, fieldnames=fieldnames)
            writer.writeheader()
            writer.writerows([r for r in results if r])

        # Print summary
        print(f"\n{'='*90}")
        print(f"SUMMARY")
        print(f"{'='*90}")
        print(f"Total companies: {self.total}")
        print(f"Websites found: {self.found} ({self.found/self.total*100:.1f}%)")
        print(f"Not found: {self.total - self.found}")
        print(f"\nResults saved to: {output_csv}")

        # Show some examples
        print(f"\nExample results:")
        count = 0
        for r in results:
            if r.get('Company_Website') and count < 10:
                print(f"  ✓ {r['Name']}: {r['Company_Website']}")
                count += 1

def main():
    finder = AdvancedWebsiteFinder(max_workers=8)
    finder.process_all('mjbizcon_exhibitors.csv', 'mjbizcon_exhibitors_with_websites_v3.csv')

if __name__ == "__main__":
    main()
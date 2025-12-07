#!/usr/bin/env python3
"""
Test HTTP status codes for all URLs from SEMrush report
Tracks redirects, response times, and generates detailed report
"""
import json
import csv
import time
import requests
from urllib.parse import urlparse
from datetime import datetime

# Input file
urls_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/unique_urls.json"

# Output files
output_csv = "/Users/tomernahumi/Documents/Plugins/SEM Rush/url_status_report.csv"
output_json = "/Users/tomernahumi/Documents/Plugins/SEM Rush/url_status_report.json"

# Request configuration
TIMEOUT = 10  # seconds
USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
HEADERS = {'User-Agent': USER_AGENT}

def categorize_url(url):
    """Categorize URL by type"""
    url_lower = url.lower()

    if url_lower.endswith('.com/') or url_lower.endswith('.com'):
        return 'homepage'
    elif '/blog/' in url_lower:
        return 'blog'
    elif '/product/' in url_lower:
        return 'product'
    elif '/product-category/' in url_lower:
        return 'category'
    elif '/shop/' in url_lower:
        return 'shop'
    elif any(lang in url_lower for lang in ['/es/', '/it/', '/fr/', '/de/', '/ja/', '/pl/', '/fi/', '/iw/']):
        return 'international'
    else:
        return 'other'

def test_url(url):
    """Test a single URL and return detailed status information"""
    result = {
        'url': url,
        'status_code': None,
        'status_text': None,
        'final_url': url,
        'redirect_chain': [],
        'response_time': None,
        'error': None,
        'page_type': categorize_url(url)
    }

    try:
        start_time = time.time()

        # Make request with redirect following
        response = requests.get(
            url,
            headers=HEADERS,
            timeout=TIMEOUT,
            allow_redirects=True
        )

        response_time = round((time.time() - start_time) * 1000, 2)  # ms

        result['status_code'] = response.status_code
        result['status_text'] = get_status_text(response.status_code)
        result['final_url'] = response.url
        result['response_time'] = response_time

        # Track redirect chain
        if response.history:
            for redirect in response.history:
                result['redirect_chain'].append({
                    'url': redirect.url,
                    'status': redirect.status_code
                })

    except requests.exceptions.Timeout:
        result['error'] = 'Timeout'
        result['status_text'] = 'Timeout'
    except requests.exceptions.SSLError as e:
        result['error'] = f'SSL Error: {str(e)[:100]}'
        result['status_text'] = 'SSL Error'
    except requests.exceptions.ConnectionError as e:
        result['error'] = f'Connection Error: {str(e)[:100]}'
        result['status_text'] = 'Connection Error'
    except Exception as e:
        result['error'] = f'Error: {str(e)[:100]}'
        result['status_text'] = 'Error'

    return result

def get_status_text(status_code):
    """Get human-readable status text"""
    status_map = {
        200: 'OK',
        301: 'Moved Permanently',
        302: 'Found (Temporary Redirect)',
        303: 'See Other',
        307: 'Temporary Redirect',
        308: 'Permanent Redirect',
        400: 'Bad Request',
        401: 'Unauthorized',
        403: 'Forbidden',
        404: 'Not Found',
        410: 'Gone',
        500: 'Internal Server Error',
        502: 'Bad Gateway',
        503: 'Service Unavailable',
        504: 'Gateway Timeout'
    }
    return status_map.get(status_code, f'Status {status_code}')

def test_all_urls():
    """Test all URLs and generate report"""
    print("="*70)
    print("URL STATUS TESTING")
    print("="*70)

    # Load URLs
    with open(urls_file, 'r', encoding='utf-8') as f:
        url_data = json.load(f)

    urls = url_data['urls']
    total_urls = len(urls)

    print(f"\nTotal URLs to test: {total_urls}")
    print(f"Started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")

    # Test each URL
    results = []
    status_counts = {}

    for i, url in enumerate(urls, 1):
        print(f"[{i}/{total_urls}] Testing: {url[:80]}...", end='', flush=True)

        result = test_url(url)
        results.append(result)

        # Count statuses
        status_key = result['status_text'] or 'Unknown'
        status_counts[status_key] = status_counts.get(status_key, 0) + 1

        # Print result
        if result['status_code'] == 200:
            print(f" ✓ {result['status_text']}")
        elif result['status_code'] in [301, 302, 307, 308]:
            print(f" → {result['status_text']} → {result['final_url'][:60]}")
        elif result['status_code'] == 404:
            print(f" ✗ {result['status_text']}")
        else:
            print(f" ⚠ {result['status_text']}")

        # Small delay to be respectful to the server
        time.sleep(0.5)

    # Save results to CSV
    print(f"\nSaving results to CSV: {output_csv}")
    save_to_csv(results)

    # Save results to JSON
    print(f"Saving results to JSON: {output_json}")
    save_to_json(results, status_counts)

    # Print summary
    print_summary(results, status_counts)

    return results

def save_to_csv(results):
    """Save results to CSV file"""
    with open(output_csv, 'w', newline='', encoding='utf-8') as f:
        fieldnames = [
            'url',
            'status_code',
            'status_text',
            'final_url',
            'has_redirect',
            'redirect_count',
            'response_time_ms',
            'page_type',
            'error'
        ]

        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()

        for result in results:
            writer.writerow({
                'url': result['url'],
                'status_code': result['status_code'] or '',
                'status_text': result['status_text'] or '',
                'final_url': result['final_url'],
                'has_redirect': 'Yes' if result['redirect_chain'] else 'No',
                'redirect_count': len(result['redirect_chain']),
                'response_time_ms': result['response_time'] or '',
                'page_type': result['page_type'],
                'error': result['error'] or ''
            })

def save_to_json(results, status_counts):
    """Save detailed results to JSON file"""
    output_data = {
        'test_date': datetime.now().isoformat(),
        'total_urls_tested': len(results),
        'status_summary': status_counts,
        'results': results
    }

    with open(output_json, 'w', encoding='utf-8') as f:
        json.dump(output_data, f, indent=2, ensure_ascii=False)

def print_summary(results, status_counts):
    """Print summary of results"""
    print("\n" + "="*70)
    print("SUMMARY")
    print("="*70)

    # Status breakdown
    print("\nSTATUS CODE BREAKDOWN:")
    for status, count in sorted(status_counts.items()):
        percentage = (count / len(results)) * 100
        print(f"  {status}: {count} ({percentage:.1f}%)")

    # Redirects
    redirects = [r for r in results if r['redirect_chain']]
    print(f"\nREDIRECTS: {len(redirects)}")

    # 404s
    not_found = [r for r in results if r['status_code'] == 404]
    print(f"NOT FOUND (404): {len(not_found)}")
    if not_found:
        print("\n404 URLs:")
        for r in not_found[:10]:  # Show first 10
            print(f"  - {r['url']}")
        if len(not_found) > 10:
            print(f"  ... and {len(not_found) - 10} more")

    # Errors
    errors = [r for r in results if r['error'] and r['status_code'] != 404]
    print(f"\nERRORS: {len(errors)}")
    if errors:
        print("\nError URLs:")
        for r in errors[:10]:  # Show first 10
            print(f"  - {r['url']}: {r['status_text']}")
        if len(errors) > 10:
            print(f"  ... and {len(errors) - 10} more")

    # Page type breakdown
    type_counts = {}
    for r in results:
        page_type = r['page_type']
        type_counts[page_type] = type_counts.get(page_type, 0) + 1

    print("\nPAGE TYPE BREAKDOWN:")
    for page_type, count in sorted(type_counts.items()):
        percentage = (count / len(results)) * 100
        print(f"  {page_type}: {count} ({percentage:.1f}%)")

    # Average response time
    response_times = [r['response_time'] for r in results if r['response_time']]
    if response_times:
        avg_time = sum(response_times) / len(response_times)
        print(f"\nAVERAGE RESPONSE TIME: {avg_time:.2f} ms")

    print("\n" + "="*70)
    print(f"Completed at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("="*70)

if __name__ == '__main__':
    test_all_urls()

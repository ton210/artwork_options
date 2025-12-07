#!/usr/bin/env python3
"""
Analyze URL status test results and generate comprehensive report
"""
import json
import csv
from collections import defaultdict

# Input files
json_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/url_status_report.json"
csv_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/url_status_report.csv"

# Output file
output_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/phase1_analysis_report.md"

def analyze_results():
    """Analyze the URL test results and generate detailed report"""

    # Load JSON results
    with open(json_file, 'r', encoding='utf-8') as f:
        data = json.load(f)

    results = data['results']
    total_urls = data['total_urls_tested']
    status_summary = data['status_summary']

    # Categorize results
    ok_urls = []
    redirect_urls = []
    not_found_urls = []
    error_urls = []

    # By page type
    by_type = defaultdict(lambda: {'ok': 0, 'redirect': 0, '404': 0, 'error': 0})

    for result in results:
        page_type = result['page_type']

        if result['status_code'] == 200:
            ok_urls.append(result)
            by_type[page_type]['ok'] += 1

            # Check if it redirected to get to 200
            if result['redirect_chain']:
                redirect_urls.append(result)
                by_type[page_type]['redirect'] += 1

        elif result['status_code'] == 404:
            not_found_urls.append(result)
            by_type[page_type]['404'] += 1

        elif result['error']:
            error_urls.append(result)
            by_type[page_type]['error'] += 1

    # Generate markdown report
    report = []
    report.append("# PHASE 1 ANALYSIS REPORT")
    report.append(f"**Test Date:** {data['test_date']}")
    report.append(f"**Total URLs Tested:** {total_urls}")
    report.append("")

    # Executive Summary
    report.append("## 📊 EXECUTIVE SUMMARY")
    report.append("")
    report.append(f"- **✓ Working URLs (200 OK):** {len(ok_urls)} ({len(ok_urls)/total_urls*100:.1f}%)")
    report.append(f"- **→ Redirected URLs (301/302):** {len(redirect_urls)} ({len(redirect_urls)/total_urls*100:.1f}%)")
    report.append(f"- **✗ Broken URLs (404):** {len(not_found_urls)} ({len(not_found_urls)/total_urls*100:.1f}%)")
    report.append(f"- **⚠ Errors:** {len(error_urls)} ({len(error_urls)/total_urls*100:.1f}%)")
    report.append("")

    # Status Code Breakdown
    report.append("## STATUS CODE BREAKDOWN")
    report.append("")
    for status, count in sorted(status_summary.items()):
        percentage = (count / total_urls) * 100
        report.append(f"- **{status}:** {count} ({percentage:.1f}%)")
    report.append("")

    # Page Type Distribution
    report.append("## PAGE TYPE DISTRIBUTION")
    report.append("")
    report.append("| Page Type | OK | Redirects | 404s | Errors | Total |")
    report.append("|-----------|----:|----------:|-----:|-------:|------:|")

    for page_type in sorted(by_type.keys()):
        stats = by_type[page_type]
        total = stats['ok'] + stats['redirect'] + stats['404'] + stats['error']
        report.append(f"| {page_type.title()} | {stats['ok']} | {stats['redirect']} | {stats['404']} | {stats['error']} | {total} |")
    report.append("")

    # Redirects Analysis
    if redirect_urls:
        report.append("## 🔄 REDIRECTS ANALYSIS")
        report.append("")
        report.append(f"**Total URLs with redirects:** {len(redirect_urls)}")
        report.append("")
        report.append("These URLs are working but redirect to a different URL. Consider updating the canonical URL in your SEMrush data.")
        report.append("")
        report.append("### Top 20 Redirects:")
        report.append("")
        report.append("| Original URL | Final URL | Redirects |")
        report.append("|--------------|-----------|-----------|")

        for result in redirect_urls[:20]:
            redirect_count = len(result['redirect_chain'])
            original = result['url'][:60] + '...' if len(result['url']) > 60 else result['url']
            final = result['final_url'][:60] + '...' if len(result['final_url']) > 60 else result['final_url']
            report.append(f"| {original} | {final} | {redirect_count} |")

        if len(redirect_urls) > 20:
            report.append(f"| ... | ... | ... |")
            report.append(f"| **+{len(redirect_urls) - 20} more** | | |")
        report.append("")

    # 404 Analysis
    if not_found_urls:
        report.append("## ❌ BROKEN URLS (404 NOT FOUND)")
        report.append("")
        report.append(f"**Total broken URLs:** {len(not_found_urls)}")
        report.append("")
        report.append("⚠️ **ACTION REQUIRED:** These URLs are ranking in SEMrush but return 404 errors.")
        report.append("")

        # Group 404s by type
        not_found_by_type = defaultdict(list)
        for result in not_found_urls:
            not_found_by_type[result['page_type']].append(result)

        for page_type, urls in sorted(not_found_by_type.items()):
            report.append(f"### {page_type.title()} Pages (404) - {len(urls)} URLs")
            report.append("")
            for result in urls:
                report.append(f"- `{result['url']}`")
            report.append("")

    # Response Time Analysis
    response_times = [r['response_time'] for r in results if r['response_time']]
    if response_times:
        avg_time = sum(response_times) / len(response_times)
        max_time = max(response_times)
        min_time = min(response_times)

        report.append("## ⚡ PERFORMANCE ANALYSIS")
        report.append("")
        report.append(f"- **Average Response Time:** {avg_time:.2f} ms")
        report.append(f"- **Fastest Response:** {min_time:.2f} ms")
        report.append(f"- **Slowest Response:** {max_time:.2f} ms")
        report.append("")

        # Slow pages
        slow_pages = [r for r in results if r['response_time'] and r['response_time'] > 3000]
        if slow_pages:
            report.append(f"### Slow Loading Pages (>3 seconds) - {len(slow_pages)} URLs")
            report.append("")
            for result in sorted(slow_pages, key=lambda x: x['response_time'], reverse=True)[:10]:
                report.append(f"- `{result['url']}` - {result['response_time']:.0f} ms")
            if len(slow_pages) > 10:
                report.append(f"- ... and {len(slow_pages) - 10} more")
            report.append("")

    # Recommendations
    report.append("## 💡 RECOMMENDATIONS")
    report.append("")

    if not_found_urls:
        report.append("### Priority 1: Fix Broken URLs (404s)")
        report.append("")
        report.append(f"You have **{len(not_found_urls)} broken URLs** that are still ranking in SEMrush but return 404 errors. These represent lost traffic and should be addressed immediately:")
        report.append("")
        report.append("**Options:**")
        report.append("1. **Restore the pages** if they were accidentally deleted")
        report.append("2. **Create 301 redirects** to relevant alternative pages")
        report.append("3. **Remove from SEMrush tracking** if intentionally deleted")
        report.append("")

    if redirect_urls:
        report.append("### Priority 2: Update Canonical URLs")
        report.append("")
        report.append(f"You have **{len(redirect_urls)} URLs** that redirect to different locations. While these still work, updating your SEMrush data with the canonical URLs will:")
        report.append("")
        report.append("- Improve tracking accuracy")
        report.append("- Reduce server load from redirects")
        report.append("- Better represent actual ranking pages")
        report.append("")

    report.append("### Priority 3: Strategic Internal Linking")
    report.append("")
    report.append(f"With **{len(ok_urls)} working URLs**, you have a strong foundation for internal linking:")
    report.append("")
    report.append(f"- **{by_type['blog']['ok']} blog posts** - Great sources for linking to products")
    report.append(f"- **{by_type['product']['ok']} product pages** - Can cross-link related products")
    report.append(f"- **{by_type['category']['ok']} category pages** - Can feature top products")
    report.append("")
    report.append("**Next Steps:**")
    report.append("1. Fix broken URLs (Phase 2a)")
    report.append("2. Update canonical URLs in dataset (Phase 2b)")
    report.append("3. Implement strategic internal linking (Phase 3)")
    report.append("")

    # Save report
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(report))

    print("="*70)
    print("ANALYSIS COMPLETE")
    print("="*70)
    print(f"Report saved to: {output_file}")
    print("")
    print("KEY FINDINGS:")
    print(f"  ✓ Working URLs: {len(ok_urls)}")
    print(f"  → Redirects: {len(redirect_urls)}")
    print(f"  ✗ Broken (404): {len(not_found_urls)}")
    print(f"  ⚠ Errors: {len(error_urls)}")
    print("")

    return {
        'ok': len(ok_urls),
        'redirects': len(redirect_urls),
        'not_found': len(not_found_urls),
        'errors': len(error_urls),
        'by_type': dict(by_type)
    }

if __name__ == '__main__':
    analyze_results()

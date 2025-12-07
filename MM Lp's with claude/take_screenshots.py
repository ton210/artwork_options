#!/usr/bin/env python3
"""
Simple script to take screenshots of a website using selenium/webdriver
"""
import subprocess
import sys

# Check if selenium is available
try:
    from selenium import webdriver
    from selenium.webdriver.chrome.options import Options
    from selenium.webdriver.chrome.service import Service
    import time

    url = "https://bluntslutsmerch.munchmakers.com/"

    # Setup Chrome options
    chrome_options = Options()
    chrome_options.add_argument('--headless')
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')

    print("Taking desktop screenshot...")
    # Desktop screenshot
    chrome_options.add_argument('--window-size=1920,1080')
    driver = webdriver.Chrome(options=chrome_options)
    driver.get(url)
    time.sleep(3)  # Wait for page to load
    driver.save_screenshot('bluntsluts_desktop.png')
    driver.quit()
    print("Desktop screenshot saved: bluntsluts_desktop.png")

    print("Taking mobile screenshot...")
    # Mobile screenshot
    chrome_options = Options()
    chrome_options.add_argument('--headless')
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')
    chrome_options.add_argument('--window-size=375,812')
    driver = webdriver.Chrome(options=chrome_options)
    driver.get(url)
    time.sleep(3)
    driver.save_screenshot('bluntsluts_mobile.png')
    driver.quit()
    print("Mobile screenshot saved: bluntsluts_mobile.png")

except ImportError:
    print("Selenium not available. Please take screenshots manually or install selenium.")
    print("You can take screenshots of https://bluntslutsmerch.munchmakers.com/ manually")
    sys.exit(1)

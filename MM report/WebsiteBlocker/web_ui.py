#!/usr/bin/env python3
"""
FocusBlock Web UI
A web-based interface for managing website blocking
"""

from flask import Flask, render_template, request, jsonify, redirect, url_for
import json
import subprocess
import os
from pathlib import Path
from datetime import datetime

app = Flask(__name__)

BLOCKER_PATH = Path(__file__).parent
CONFIG_DIR = Path.home() / ".focusblock"
CONFIG_FILE = CONFIG_DIR / "config.json"

def run_blocker_command(command):
    """Run a blocker.py command and return output"""
    result = subprocess.run(
        ['python3', str(BLOCKER_PATH / 'blocker.py')] + command,
        capture_output=True,
        text=True,
        cwd=str(BLOCKER_PATH)
    )
    return result.stdout + result.stderr

def get_config():
    """Get current configuration"""
    if CONFIG_FILE.exists():
        with open(CONFIG_FILE, 'r') as f:
            return json.load(f)
    return {
        'blocked_websites': [],
        'is_blocking_enabled': False,
        'strict_mode': False,
        'strict_until': None
    }

def get_strict_mode_status():
    """Get strict mode status with time remaining"""
    config = get_config()

    if not config.get('strict_mode'):
        return {'active': False, 'message': 'Disabled'}

    if config.get('strict_until'):
        until_time = datetime.fromisoformat(config['strict_until'])
        if datetime.now() < until_time:
            time_left = until_time - datetime.now()
            hours = int(time_left.total_seconds() // 3600)
            minutes = int((time_left.total_seconds() % 3600) // 60)
            return {
                'active': True,
                'message': f'Locked for {hours}h {minutes}m',
                'until': until_time.strftime('%I:%M %p')
            }
        else:
            return {'active': False, 'message': 'Expired'}
    else:
        return {'active': True, 'message': 'Password Protected'}

@app.route('/')
def index():
    """Main page"""
    config = get_config()
    strict_status = get_strict_mode_status()

    return render_template('index.html',
                         websites=config.get('blocked_websites', []),
                         blocking_enabled=config.get('is_blocking_enabled', False),
                         strict_mode=strict_status,
                         website_count=len(config.get('blocked_websites', [])))

@app.route('/api/status')
def api_status():
    """Get current status"""
    config = get_config()
    strict_status = get_strict_mode_status()

    return jsonify({
        'blocking_enabled': config.get('is_blocking_enabled', False),
        'website_count': len(config.get('blocked_websites', [])),
        'websites': config.get('blocked_websites', []),
        'strict_mode': strict_status
    })

@app.route('/api/add', methods=['POST'])
def api_add():
    """Add a website"""
    data = request.json
    url = data.get('url', '').strip()

    if not url:
        return jsonify({'success': False, 'message': 'URL is required'})

    output = run_blocker_command(['add', url])

    return jsonify({
        'success': '✅' in output,
        'message': output.strip()
    })

@app.route('/api/remove', methods=['POST'])
def api_remove():
    """Remove a website"""
    data = request.json
    url = data.get('url', '').strip()

    if not url:
        return jsonify({'success': False, 'message': 'URL is required'})

    output = run_blocker_command(['remove', url])

    return jsonify({
        'success': '✅' in output,
        'message': output.strip()
    })

@app.route('/api/enable', methods=['POST'])
def api_enable():
    """Enable blocking"""
    output = run_blocker_command(['enable'])

    return jsonify({
        'success': '✅' in output or 'ENABLED' in output,
        'message': output.strip()
    })

@app.route('/api/disable', methods=['POST'])
def api_disable():
    """Disable blocking"""
    output = run_blocker_command(['disable'])

    return jsonify({
        'success': '✅' in output or 'DISABLED' in output,
        'message': output.strip()
    })

@app.route('/api/strict/enable', methods=['POST'])
def api_strict_enable():
    """Enable strict mode"""
    data = request.json
    hours = data.get('hours', 2)

    output = run_blocker_command(['strict', str(hours)])

    return jsonify({
        'success': '✅' in output or 'ENABLED' in output,
        'message': output.strip()
    })

@app.route('/api/strict/unlock', methods=['POST'])
def api_strict_unlock():
    """Unlock strict mode (force override)"""
    # This requires user confirmation, so we'll just return the command
    return jsonify({
        'success': False,
        'message': 'Use Terminal: python3 blocker.py unlock'
    })

if __name__ == '__main__':
    # Create templates directory
    templates_dir = BLOCKER_PATH / 'templates'
    templates_dir.mkdir(exist_ok=True)

    print("\n" + "="*60)
    print("🚀 FocusBlock Web UI Starting...")
    print("="*60)
    print("\n📱 Open in your browser:")
    print("   http://localhost:5000")
    print("\n⚠️  Keep this Terminal window open")
    print("   Press Ctrl+C to stop\n")
    print("="*60 + "\n")

    app.run(debug=True, port=5000, host='127.0.0.1')

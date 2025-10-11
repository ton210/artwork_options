#!/usr/bin/env python3
"""
FocusBlock - macOS Website Blocker
Blocks websites across ALL browsers (Safari, Chrome, Firefox, etc.)
"""

import sys
import os
import json
import hashlib
import getpass
from datetime import datetime, timedelta
from pathlib import Path

# Configuration
HOSTS_FILE = "/etc/hosts"
BACKUP_FILE = "/etc/hosts.backup"
CONFIG_DIR = Path.home() / ".focusblock"
CONFIG_FILE = CONFIG_DIR / "config.json"
BLOCK_MARKER = "# FocusBlock"
BLOCK_START = "# FocusBlock - START"
BLOCK_END = "# FocusBlock - END"

class WebsiteBlocker:
    def __init__(self):
        self.config_dir = CONFIG_DIR
        self.config_file = CONFIG_FILE
        self.blocked_websites = []
        self.is_blocking_enabled = False
        self.schedules = []
        self.strict_mode = False
        self.strict_password_hash = None
        self.strict_until = None

        # Create config directory if it doesn't exist
        self.config_dir.mkdir(parents=True, exist_ok=True)

        # Load configuration
        self.load_config()

    def load_config(self):
        """Load configuration from JSON file"""
        if self.config_file.exists():
            try:
                with open(self.config_file, 'r') as f:
                    config = json.load(f)
                    self.blocked_websites = config.get('blocked_websites', [])
                    self.is_blocking_enabled = config.get('is_blocking_enabled', False)
                    self.schedules = config.get('schedules', [])
                    self.strict_mode = config.get('strict_mode', False)
                    self.strict_password_hash = config.get('strict_password_hash')
                    self.strict_until = config.get('strict_until')
            except Exception as e:
                print(f"Error loading config: {e}")

    def save_config(self):
        """Save configuration to JSON file"""
        config = {
            'blocked_websites': self.blocked_websites,
            'is_blocking_enabled': self.is_blocking_enabled,
            'schedules': self.schedules,
            'strict_mode': self.strict_mode,
            'strict_password_hash': self.strict_password_hash,
            'strict_until': self.strict_until
        }
        try:
            with open(self.config_file, 'w') as f:
                json.dump(config, indent=2, fp=f)
        except Exception as e:
            print(f"Error saving config: {e}")

    def clean_url(self, url):
        """Extract clean domain from URL"""
        url = url.lower().strip()
        url = url.replace('http://', '').replace('https://', '')
        url = url.replace('www.', '')
        if '/' in url:
            url = url.split('/')[0]
        return url

    def add_website(self, url):
        """Add a website to the block list"""
        domain = self.clean_url(url)
        if domain and domain not in self.blocked_websites:
            self.blocked_websites.append(domain)
            self.save_config()
            print(f"✅ Added: {domain}")

            if self.is_blocking_enabled:
                self.apply_blocking()
        else:
            print(f"❌ Website already in list or invalid: {url}")

    def remove_website(self, url):
        """Remove a website from the block list"""
        domain = self.clean_url(url)
        if domain in self.blocked_websites:
            self.blocked_websites.remove(domain)
            self.save_config()
            print(f"✅ Removed: {domain}")

            if self.is_blocking_enabled:
                self.apply_blocking()
        else:
            print(f"❌ Website not in list: {domain}")

    def list_websites(self):
        """List all blocked websites"""
        if not self.blocked_websites:
            print("📋 No websites in block list")
            return

        print(f"\n📋 Blocked Websites ({len(self.blocked_websites)}):")
        print("=" * 50)
        for i, website in enumerate(self.blocked_websites, 1):
            print(f"{i}. {website}")
        print("=" * 50)

    def enable_blocking(self):
        """Enable website blocking"""
        self.is_blocking_enabled = True
        self.save_config()
        self.apply_blocking()
        print("🔒 Blocking ENABLED")

    def disable_blocking(self):
        """Disable website blocking"""
        # Check if strict mode is active
        if self.strict_mode:
            if self.strict_until:
                # Check if strict mode timer has expired
                if datetime.now().isoformat() < self.strict_until:
                    until_time = datetime.fromisoformat(self.strict_until)
                    time_left = until_time - datetime.now()
                    hours = int(time_left.total_seconds() // 3600)
                    minutes = int((time_left.total_seconds() % 3600) // 60)
                    print(f"🔒 STRICT MODE ACTIVE - Cannot disable for {hours}h {minutes}m")
                    print(f"Blocking will auto-disable at {until_time.strftime('%I:%M %p')}")
                    print("\nTo override, use: python3 blocker.py unlock")
                    return
                else:
                    # Timer expired, disable strict mode
                    self.strict_mode = False
                    self.strict_until = None
            else:
                # Strict mode with password
                print("🔒 STRICT MODE ACTIVE - Password required to disable")
                print("Use: python3 blocker.py unlock")
                return

        self.is_blocking_enabled = False
        self.save_config()
        self.remove_blocking()
        print("🔓 Blocking DISABLED")

    def enable_strict_mode(self, hours=None, password=None):
        """Enable strict mode with optional timer or password"""
        if hours:
            # Time-based strict mode
            self.strict_mode = True
            self.strict_until = (datetime.now() + timedelta(hours=hours)).isoformat()
            self.save_config()
            print(f"🔒 STRICT MODE ENABLED for {hours} hours")
            print(f"Cannot disable until {datetime.fromisoformat(self.strict_until).strftime('%I:%M %p')}")
        elif password:
            # Password-based strict mode
            self.strict_mode = True
            self.strict_password_hash = hashlib.sha256(password.encode()).hexdigest()
            self.save_config()
            print("🔒 STRICT MODE ENABLED with password")
            print("Blocking cannot be disabled without the password")
        else:
            print("❌ Please provide either hours or password")
            print("Examples:")
            print("  python3 blocker.py strict 2        # Lock for 2 hours")
            print("  python3 blocker.py strict password # Lock with password")

    def unlock_strict_mode(self):
        """Unlock strict mode"""
        if not self.strict_mode:
            print("ℹ️  Strict mode is not active")
            return

        if self.strict_password_hash:
            # Password-based unlock
            password = getpass.getpass("Enter strict mode password: ")
            password_hash = hashlib.sha256(password.encode()).hexdigest()

            if password_hash == self.strict_password_hash:
                self.strict_mode = False
                self.strict_password_hash = None
                self.save_config()
                print("✅ Strict mode unlocked")
            else:
                print("❌ Incorrect password")
        elif self.strict_until:
            # Time-based unlock - force override
            print("⚠️  Forcing strict mode unlock (timer will be cancelled)")
            confirm = input("Are you sure? (yes/no): ").lower()
            if confirm == "yes":
                self.strict_mode = False
                self.strict_until = None
                self.save_config()
                print("✅ Strict mode unlocked")
            else:
                print("❌ Unlock cancelled")

    def status(self):
        """Show current blocking status"""
        status = "ENABLED" if self.is_blocking_enabled else "DISABLED"
        emoji = "🔒" if self.is_blocking_enabled else "🔓"

        print(f"\n{emoji} Blocking Status: {status}")
        print(f"📋 Blocked Websites: {len(self.blocked_websites)}")

        # Show strict mode status
        if self.strict_mode:
            if self.strict_until:
                until_time = datetime.fromisoformat(self.strict_until)
                if datetime.now() < until_time:
                    time_left = until_time - datetime.now()
                    hours = int(time_left.total_seconds() // 3600)
                    minutes = int((time_left.total_seconds() % 3600) // 60)
                    print(f"🔐 Strict Mode: ACTIVE (locked for {hours}h {minutes}m)")
                else:
                    print(f"🔐 Strict Mode: EXPIRED (can be disabled)")
            else:
                print(f"🔐 Strict Mode: ACTIVE (password protected)")
        else:
            print(f"🔓 Strict Mode: DISABLED")

        if self.blocked_websites:
            print("\nCurrently blocking:")
            for website in self.blocked_websites[:10]:  # Show first 10
                print(f"  • {website}")
            if len(self.blocked_websites) > 10:
                print(f"  ... and {len(self.blocked_websites) - 10} more")

    def backup_hosts(self):
        """Backup the hosts file"""
        try:
            os.system(f"sudo cp {HOSTS_FILE} {BACKUP_FILE}")
            print(f"✅ Hosts file backed up to {BACKUP_FILE}")
        except Exception as e:
            print(f"❌ Error backing up hosts file: {e}")

    def apply_blocking(self):
        """Apply blocking by modifying hosts file"""
        try:
            # Read current hosts file
            with open(HOSTS_FILE, 'r') as f:
                lines = f.readlines()

            # Remove old FocusBlock entries
            new_lines = []
            skip = False
            for line in lines:
                if BLOCK_START in line:
                    skip = True
                    continue
                if BLOCK_END in line:
                    skip = False
                    continue
                if not skip and BLOCK_MARKER not in line:
                    new_lines.append(line)

            # Add new blocking entries
            new_lines.append(f"\n{BLOCK_START}\n")
            for website in self.blocked_websites:
                new_lines.append(f"127.0.0.1 {website} {BLOCK_MARKER}\n")
                new_lines.append(f"127.0.0.1 www.{website} {BLOCK_MARKER}\n")
                new_lines.append(f"::1 {website} {BLOCK_MARKER}\n")
                new_lines.append(f"::1 www.{website} {BLOCK_MARKER}\n")
            new_lines.append(f"{BLOCK_END}\n")

            # Write to temporary file
            temp_file = "/tmp/focusblock_hosts"
            with open(temp_file, 'w') as f:
                f.writelines(new_lines)

            # Copy to hosts file with sudo
            result = os.system(f"sudo cp {temp_file} {HOSTS_FILE}")

            if result == 0:
                print("✅ Blocking applied successfully")
                self.flush_dns()
            else:
                print("❌ Failed to apply blocking (need sudo privileges)")

            # Clean up temp file
            os.remove(temp_file)

        except Exception as e:
            print(f"❌ Error applying blocking: {e}")

    def remove_blocking(self):
        """Remove blocking from hosts file"""
        try:
            # Read current hosts file
            with open(HOSTS_FILE, 'r') as f:
                lines = f.readlines()

            # Remove FocusBlock entries
            new_lines = []
            skip = False
            for line in lines:
                if BLOCK_START in line:
                    skip = True
                    continue
                if BLOCK_END in line:
                    skip = False
                    continue
                if not skip and BLOCK_MARKER not in line:
                    new_lines.append(line)

            # Write to temporary file
            temp_file = "/tmp/focusblock_hosts"
            with open(temp_file, 'w') as f:
                f.writelines(new_lines)

            # Copy to hosts file with sudo
            result = os.system(f"sudo cp {temp_file} {HOSTS_FILE}")

            if result == 0:
                print("✅ Blocking removed successfully")
                self.flush_dns()
            else:
                print("❌ Failed to remove blocking (need sudo privileges)")

            # Clean up temp file
            os.remove(temp_file)

        except Exception as e:
            print(f"❌ Error removing blocking: {e}")

    def flush_dns(self):
        """Flush DNS cache"""
        print("🔄 Flushing DNS cache...")
        os.system("sudo dscacheutil -flushcache")
        os.system("sudo killall -HUP mDNSResponder")
        print("✅ DNS cache flushed")

def print_help():
    """Print help information"""
    help_text = """
🔒 FocusBlock - macOS Website Blocker
=====================================

USAGE:
    python3 blocker.py <command> [arguments]

COMMANDS:
    add <website>       Add a website to block list
    remove <website>    Remove a website from block list
    list                List all blocked websites
    enable              Enable blocking (requires sudo)
    disable             Disable blocking (requires sudo)
    status              Show current blocking status
    strict <hours>      Enable strict mode for X hours
    strict password     Enable strict mode with password
    unlock              Unlock strict mode
    backup              Backup hosts file
    help                Show this help message

EXAMPLES:
    python3 blocker.py add youtube.com
    python3 blocker.py add facebook.com
    python3 blocker.py list
    python3 blocker.py enable
    python3 blocker.py disable
    python3 blocker.py status
    python3 blocker.py strict 2         # Lock for 2 hours
    python3 blocker.py strict password  # Lock with password
    python3 blocker.py unlock           # Unlock strict mode

NOTES:
    - Blocking requires sudo/admin password
    - Works across ALL browsers (Safari, Chrome, Firefox, etc.)
    - Blocks websites by modifying /etc/hosts file
    - Changes take effect immediately
    """
    print(help_text)

def main():
    blocker = WebsiteBlocker()

    if len(sys.argv) < 2:
        print_help()
        return

    command = sys.argv[1].lower()

    if command == "add":
        if len(sys.argv) < 3:
            print("❌ Please provide a website to add")
            print("Example: python3 blocker.py add youtube.com")
            return
        blocker.add_website(sys.argv[2])

    elif command == "remove":
        if len(sys.argv) < 3:
            print("❌ Please provide a website to remove")
            print("Example: python3 blocker.py remove youtube.com")
            return
        blocker.remove_website(sys.argv[2])

    elif command == "list":
        blocker.list_websites()

    elif command == "enable":
        blocker.enable_blocking()

    elif command == "disable":
        blocker.disable_blocking()

    elif command == "status":
        blocker.status()

    elif command == "backup":
        blocker.backup_hosts()

    elif command == "strict":
        if len(sys.argv) < 3:
            print("❌ Please specify hours or 'password'")
            print("Examples:")
            print("  python3 blocker.py strict 2        # Lock for 2 hours")
            print("  python3 blocker.py strict password # Lock with password")
            return

        if sys.argv[2].lower() == "password":
            password = getpass.getpass("Set strict mode password: ")
            confirm = getpass.getpass("Confirm password: ")
            if password == confirm:
                blocker.enable_strict_mode(password=password)
            else:
                print("❌ Passwords don't match")
        else:
            try:
                hours = float(sys.argv[2])
                blocker.enable_strict_mode(hours=hours)
            except ValueError:
                print("❌ Invalid hours. Use a number (e.g., 2, 0.5, 24)")

    elif command == "unlock":
        blocker.unlock_strict_mode()

    elif command == "help":
        print_help()

    else:
        print(f"❌ Unknown command: {command}")
        print("Run 'python3 blocker.py help' for usage information")

if __name__ == "__main__":
    main()

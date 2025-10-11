#!/usr/bin/env python3
"""
FocusBlock Scheduler - Automatic time-based website blocking
"""

import json
import schedule
import time
from datetime import datetime
from pathlib import Path
from blocker import WebsiteBlocker

CONFIG_DIR = Path.home() / ".focusblock"
SCHEDULE_FILE = CONFIG_DIR / "schedules.json"

class BlockScheduler:
    def __init__(self):
        self.blocker = WebsiteBlocker()
        self.schedules = []
        self.load_schedules()

    def load_schedules(self):
        """Load schedules from JSON file"""
        if SCHEDULE_FILE.exists():
            try:
                with open(SCHEDULE_FILE, 'r') as f:
                    self.schedules = json.load(f)
            except Exception as e:
                print(f"Error loading schedules: {e}")

    def save_schedules(self):
        """Save schedules to JSON file"""
        try:
            CONFIG_DIR.mkdir(parents=True, exist_ok=True)
            with open(SCHEDULE_FILE, 'w') as f:
                json.dump(self.schedules, indent=2, fp=f)
        except Exception as e:
            print(f"Error saving schedules: {e}")

    def add_schedule(self, name, start_time, end_time, days, websites):
        """Add a new schedule"""
        schedule_obj = {
            'id': len(self.schedules) + 1,
            'name': name,
            'start_time': start_time,  # Format: "09:00"
            'end_time': end_time,      # Format: "17:00"
            'days': days,              # List: ["Monday", "Tuesday", ...]
            'websites': websites,      # List of website domains
            'enabled': True
        }
        self.schedules.append(schedule_obj)
        self.save_schedules()
        print(f"✅ Schedule '{name}' added")
        return schedule_obj['id']

    def remove_schedule(self, schedule_id):
        """Remove a schedule by ID"""
        self.schedules = [s for s in self.schedules if s['id'] != schedule_id]
        self.save_schedules()
        print(f"✅ Schedule #{schedule_id} removed")

    def list_schedules(self):
        """List all schedules"""
        if not self.schedules:
            print("📅 No schedules configured")
            return

        print(f"\n📅 Schedules ({len(self.schedules)}):")
        print("=" * 70)
        for sched in self.schedules:
            status = "✅ ENABLED" if sched['enabled'] else "❌ DISABLED"
            days_str = ", ".join(sched['days'][:3])
            if len(sched['days']) > 3:
                days_str += f" +{len(sched['days']) - 3} more"

            print(f"ID: {sched['id']} | {sched['name']}")
            print(f"  Time: {sched['start_time']} - {sched['end_time']}")
            print(f"  Days: {days_str}")
            print(f"  Websites: {len(sched['websites'])} blocked")
            print(f"  Status: {status}")
            print()
        print("=" * 70)

    def check_schedules(self):
        """Check if any schedule should be active now"""
        now = datetime.now()
        current_time = now.strftime("%H:%M")
        current_day = now.strftime("%A")

        active_websites = set()

        for sched in self.schedules:
            if not sched['enabled']:
                continue

            # Check if today is in the schedule days
            if current_day not in sched['days']:
                continue

            # Check if current time is within schedule time
            if sched['start_time'] <= current_time <= sched['end_time']:
                active_websites.update(sched['websites'])
                print(f"⏰ Schedule '{sched['name']}' is ACTIVE")

        # Update blocker with active websites
        if active_websites:
            # Temporarily add schedule websites to blocker
            original_websites = self.blocker.blocked_websites.copy()
            self.blocker.blocked_websites = list(active_websites)
            self.blocker.enable_blocking()

        return len(active_websites) > 0

    def run_daemon(self):
        """Run scheduler as a daemon (checks every minute)"""
        print("🚀 FocusBlock Scheduler started")
        print("Checking schedules every minute...")
        print("Press Ctrl+C to stop\n")

        try:
            while True:
                self.check_schedules()
                time.sleep(60)  # Check every minute
        except KeyboardInterrupt:
            print("\n\n👋 Scheduler stopped")

def print_help():
    """Print scheduler help"""
    help_text = """
📅 FocusBlock Scheduler
======================

USAGE:
    python3 scheduler.py <command> [arguments]

COMMANDS:
    add         Add a new schedule (interactive)
    list        List all schedules
    remove <id> Remove a schedule by ID
    run         Run scheduler daemon (checks every minute)
    help        Show this help message

EXAMPLES:
    python3 scheduler.py add
    python3 scheduler.py list
    python3 scheduler.py remove 1
    python3 scheduler.py run

SCHEDULE FORMAT:
    - Start/End Time: 24-hour format (e.g., 09:00, 17:30)
    - Days: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday
    - Websites: Same domains you added to blocker
    """
    print(help_text)

def interactive_add():
    """Interactive schedule addition"""
    scheduler = BlockScheduler()

    print("\n📅 Create New Schedule")
    print("=" * 50)

    name = input("Schedule name (e.g., 'Work Hours'): ").strip()

    start_time = input("Start time (HH:MM, e.g., 09:00): ").strip()
    end_time = input("End time (HH:MM, e.g., 17:00): ").strip()

    print("\nSelect days (comma-separated):")
    print("1. Monday")
    print("2. Tuesday")
    print("3. Wednesday")
    print("4. Thursday")
    print("5. Friday")
    print("6. Saturday")
    print("7. Sunday")
    print("Or type: weekdays (Mon-Fri) or weekend (Sat-Sun) or all")

    days_input = input("Days: ").strip().lower()

    if days_input == "weekdays":
        days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
    elif days_input == "weekend":
        days = ["Saturday", "Sunday"]
    elif days_input == "all":
        days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]
    else:
        day_map = {
            "1": "Monday", "2": "Tuesday", "3": "Wednesday", "4": "Thursday",
            "5": "Friday", "6": "Saturday", "7": "Sunday"
        }
        day_nums = [d.strip() for d in days_input.split(",")]
        days = [day_map.get(d, d) for d in day_nums if d in day_map or d.capitalize() in day_map.values()]

    print("\nEnter websites to block (comma-separated):")
    print("Example: youtube.com, facebook.com, twitter.com")
    websites_input = input("Websites: ").strip()
    websites = [w.strip() for w in websites_input.split(",") if w.strip()]

    scheduler.add_schedule(name, start_time, end_time, days, websites)

    print("\n✅ Schedule created successfully!")
    print(f"Name: {name}")
    print(f"Time: {start_time} - {end_time}")
    print(f"Days: {', '.join(days)}")
    print(f"Blocking {len(websites)} websites")

def main():
    import sys

    if len(sys.argv) < 2:
        print_help()
        return

    command = sys.argv[1].lower()
    scheduler = BlockScheduler()

    if command == "add":
        interactive_add()

    elif command == "list":
        scheduler.list_schedules()

    elif command == "remove":
        if len(sys.argv) < 3:
            print("❌ Please provide schedule ID to remove")
            return
        try:
            schedule_id = int(sys.argv[2])
            scheduler.remove_schedule(schedule_id)
        except ValueError:
            print("❌ Invalid schedule ID")

    elif command == "run":
        scheduler.run_daemon()

    elif command == "help":
        print_help()

    else:
        print(f"❌ Unknown command: {command}")
        print_help()

if __name__ == "__main__":
    main()

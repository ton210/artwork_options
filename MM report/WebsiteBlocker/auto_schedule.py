#!/usr/bin/env python3
"""
FocusBlock Auto-Schedule with Strict Mode
Automatically enables blocking during specified times with strict mode
"""

import schedule
import time
import subprocess
from datetime import datetime
import os

# Configuration
SCHEDULE_CONFIG = {
    'days': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
    'start_time': '08:00',  # 8 AM
    'end_time': '19:00',    # 7 PM (19:00 in 24-hour format)
}

BLOCKER_PATH = os.path.dirname(os.path.abspath(__file__))

def enable_blocking_with_strict():
    """Enable blocking and activate strict mode"""
    print(f"\n{'='*60}")
    print(f"⏰ {datetime.now().strftime('%A, %I:%M %p')}")
    print(f"🔒 ACTIVATING BLOCKING + STRICT MODE")
    print(f"{'='*60}\n")

    # Enable blocking
    result = subprocess.run(
        ['python3', f'{BLOCKER_PATH}/blocker.py', 'enable'],
        capture_output=True,
        text=True
    )
    print(result.stdout)

    # Calculate hours until end time
    now = datetime.now()
    end_hour, end_minute = map(int, SCHEDULE_CONFIG['end_time'].split(':'))
    end_time = now.replace(hour=end_hour, minute=end_minute, second=0, microsecond=0)

    hours_until_end = (end_time - now).total_seconds() / 3600

    # Enable strict mode for the remaining hours
    result = subprocess.run(
        ['python3', f'{BLOCKER_PATH}/blocker.py', 'strict', str(hours_until_end)],
        capture_output=True,
        text=True
    )
    print(result.stdout)

    print(f"\n✅ Blocking is now ACTIVE and LOCKED until {end_time.strftime('%I:%M %p')}")
    print(f"🔐 Cannot be disabled for {hours_until_end:.1f} hours\n")

def disable_blocking():
    """Disable blocking (strict mode timer will have expired)"""
    print(f"\n{'='*60}")
    print(f"⏰ {datetime.now().strftime('%A, %I:%M %p')}")
    print(f"🔓 DEACTIVATING BLOCKING")
    print(f"{'='*60}\n")

    result = subprocess.run(
        ['python3', f'{BLOCKER_PATH}/blocker.py', 'disable'],
        capture_output=True,
        text=True
    )
    print(result.stdout)

    print(f"\n✅ Blocking is now DISABLED - You have free access until tomorrow\n")

def check_and_activate_if_needed():
    """Check if we're in active time and activate if needed"""
    now = datetime.now()
    current_day = now.strftime('%A')
    current_time = now.strftime('%H:%M')

    if current_day in SCHEDULE_CONFIG['days']:
        if SCHEDULE_CONFIG['start_time'] <= current_time < SCHEDULE_CONFIG['end_time']:
            print(f"📅 Currently in active schedule window - activating blocking")
            enable_blocking_with_strict()
        else:
            print(f"ℹ️  Outside active hours for today")
    else:
        print(f"ℹ️  {current_day} is not a scheduled day")

def setup_schedule():
    """Setup the schedule"""
    start_time = SCHEDULE_CONFIG['start_time']
    end_time = SCHEDULE_CONFIG['end_time']

    # Schedule for each day
    for day in SCHEDULE_CONFIG['days']:
        if day == 'Sunday':
            schedule.every().sunday.at(start_time).do(enable_blocking_with_strict)
            schedule.every().sunday.at(end_time).do(disable_blocking)
        elif day == 'Monday':
            schedule.every().monday.at(start_time).do(enable_blocking_with_strict)
            schedule.every().monday.at(end_time).do(disable_blocking)
        elif day == 'Tuesday':
            schedule.every().tuesday.at(start_time).do(enable_blocking_with_strict)
            schedule.every().tuesday.at(end_time).do(disable_blocking)
        elif day == 'Wednesday':
            schedule.every().wednesday.at(start_time).do(enable_blocking_with_strict)
            schedule.every().wednesday.at(end_time).do(disable_blocking)
        elif day == 'Thursday':
            schedule.every().thursday.at(start_time).do(enable_blocking_with_strict)
            schedule.every().thursday.at(end_time).do(disable_blocking)

    print(f"\n{'='*60}")
    print(f"🚀 FocusBlock Auto-Schedule STARTED")
    print(f"{'='*60}")
    print(f"\n📅 Active Days: {', '.join(SCHEDULE_CONFIG['days'])}")
    print(f"⏰ Active Hours: {start_time} - {end_time}")
    print(f"🔒 Strict Mode: ENABLED (cannot disable during active hours)")
    print(f"\n{'='*60}")
    print(f"Press Ctrl+C to stop the scheduler")
    print(f"{'='*60}\n")

def print_status():
    """Print current status"""
    result = subprocess.run(
        ['python3', f'{BLOCKER_PATH}/blocker.py', 'status'],
        capture_output=True,
        text=True
    )
    print(result.stdout)

def main():
    print("\n🔒 FocusBlock Auto-Scheduler with Strict Mode\n")

    # Check if we should activate now
    print("Checking current status...")
    check_and_activate_if_needed()

    # Setup recurring schedule
    setup_schedule()

    # Show current status
    print("\nCurrent Blocking Status:")
    print_status()

    print(f"\n⏰ Next scheduled events:")
    for job in schedule.jobs[:5]:  # Show next 5 jobs
        print(f"   • {job}")

    print(f"\n{'='*60}\n")

    # Run scheduler
    try:
        while True:
            schedule.run_pending()
            time.sleep(60)  # Check every minute
    except KeyboardInterrupt:
        print("\n\n👋 Scheduler stopped\n")

if __name__ == "__main__":
    main()

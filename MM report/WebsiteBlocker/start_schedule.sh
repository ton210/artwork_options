#!/bin/bash
# Start FocusBlock Auto-Scheduler
# Run this to start the automatic Sunday-Thursday 8am-7pm blocking

cd "/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker"

echo ""
echo "{'='*60}"
echo "🔒 FocusBlock Auto-Scheduler"
echo "{'='*60}"
echo ""
echo "📅 Active: Sunday-Thursday"
echo "⏰ Hours: 8:00 AM - 7:00 PM"
echo "🔐 Strict Mode: ENABLED (cannot disable during hours)"
echo ""
echo "This window must stay open for the scheduler to work."
echo "Press Ctrl+C to stop."
echo ""
echo "{'='*60}"
echo ""

python3 auto_schedule.py

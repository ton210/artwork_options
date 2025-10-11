# 🔒 FocusBlock - Website Blocker for macOS

A simple, powerful Python-based website blocker that works **across ALL browsers** (Safari, Chrome, Firefox, Edge, etc.). No Xcode required!

## ✨ Features

- ✅ **Universal Blocking** - Blocks websites in Safari, Chrome, Firefox, and ALL browsers
- ✅ **Command-Line Interface** - Simple commands to manage blocking
- ✅ **Time-Based Schedules** - Automatically block websites during specific hours
- ✅ **Persistent** - Remembers your blocked websites
- ✅ **System-Wide** - Uses hosts file for reliable blocking
- ✅ **No Installation** - Just Python (already on your Mac)

## 🚀 Quick Start

### 1. Navigate to the folder

```bash
cd "/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker"
```

### 2. Add websites to block

```bash
python3 blocker.py add youtube.com
python3 blocker.py add facebook.com
python3 blocker.py add twitter.com
python3 blocker.py add reddit.com
```

### 3. Enable blocking (requires password)

```bash
python3 blocker.py enable
```

### 4. Test it!

Try opening YouTube or Facebook in Safari or Chrome - it won't load! 🎉

### 5. Disable blocking when needed

```bash
python3 blocker.py disable
```

## 📖 Complete Usage Guide

### Basic Commands

```bash
# Add a website to block list
python3 blocker.py add youtube.com

# Remove a website from block list
python3 blocker.py remove youtube.com

# List all blocked websites
python3 blocker.py list

# Enable blocking (requires sudo password)
python3 blocker.py enable

# Disable blocking (requires sudo password)
python3 blocker.py disable

# Check blocking status
python3 blocker.py status

# Backup hosts file (recommended before first use)
python3 blocker.py backup

# Show help
python3 blocker.py help
```

### Schedule Commands

```bash
# Create a new schedule (interactive)
python3 scheduler.py add

# List all schedules
python3 scheduler.py list

# Remove a schedule by ID
python3 scheduler.py remove 1

# Run scheduler (keeps running, checks every minute)
python3 scheduler.py run

# Show scheduler help
python3 scheduler.py help
```

## 📅 Using Schedules

Schedules automatically block websites during specific times.

### Create a Work Schedule

```bash
python3 scheduler.py add
```

You'll be prompted:
```
Schedule name: Work Hours
Start time: 09:00
End time: 17:00
Days: weekdays
Websites: youtube.com, facebook.com, twitter.com
```

### Run the Scheduler

```bash
python3 scheduler.py run
```

This runs in the background and checks every minute. Press Ctrl+C to stop.

### Example Schedules

**Deep Work Hours** (9 AM - 12 PM, Monday-Friday)
- Blocks: All social media, news sites, entertainment

**Evening Focus** (6 PM - 9 PM, Daily)
- Blocks: Work-related sites to disconnect

**Sleep Protection** (10 PM - 7 AM, Daily)
- Blocks: All distracting sites

## 🔧 How It Works

### Hosts File Method

The blocker modifies your Mac's `/etc/hosts` file to redirect blocked websites to `127.0.0.1` (localhost):

```
127.0.0.1 youtube.com # FocusBlock
127.0.0.1 www.youtube.com # FocusBlock
::1 youtube.com # FocusBlock
::1 www.youtube.com # FocusBlock
```

This method:
- ✅ Works across **ALL browsers and apps**
- ✅ Blocks at the DNS level (before connection)
- ✅ Cannot be bypassed by browser extensions
- ✅ Takes effect immediately
- ✅ No performance impact

### Why It Needs Admin Password

Modifying `/etc/hosts` requires admin privileges. This is a macOS security feature - the script asks for your password when enabling/disabling blocking.

## 📁 File Structure

```
WebsiteBlocker/
├── blocker.py          # Main website blocker
├── scheduler.py        # Time-based scheduler
└── README.md           # This file

Config files (created automatically):
~/.focusblock/
├── config.json         # Blocked websites and settings
└── schedules.json      # Schedule configurations
```

## 💡 Tips & Tricks

### Quick Enable/Disable Aliases

Add these to your `~/.zshrc` or `~/.bash_profile`:

```bash
alias block-on='cd "/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker" && python3 blocker.py enable'
alias block-off='cd "/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker" && python3 blocker.py disable'
alias block-status='cd "/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker" && python3 blocker.py status'
```

Then use:
```bash
block-on      # Enable blocking
block-off     # Disable blocking
block-status  # Check status
```

### Make Scripts Executable

```bash
chmod +x blocker.py
chmod +x scheduler.py
```

Then run without `python3`:
```bash
./blocker.py add youtube.com
./blocker.py enable
```

### Add to PATH

```bash
# Add this to ~/.zshrc or ~/.bash_profile
export PATH="$PATH:/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker"
```

Then use from anywhere:
```bash
blocker.py add youtube.com
blocker.py enable
```

### Run Scheduler on Startup

1. Create a LaunchAgent plist file:

```bash
nano ~/Library/LaunchAgents/com.focusblock.scheduler.plist
```

2. Add this content:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.focusblock.scheduler</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/bin/python3</string>
        <string>/Users/tomernahumi/Documents/Plugins/MM report/WebsiteBlocker/scheduler.py</string>
        <string>run</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <true/>
</dict>
</plist>
```

3. Load the agent:

```bash
launchctl load ~/Library/LaunchAgents/com.focusblock.scheduler.plist
```

## 🐛 Troubleshooting

### Websites Still Loading

1. **Check blocking is enabled:**
   ```bash
   python3 blocker.py status
   ```

2. **Verify website is in list:**
   ```bash
   python3 blocker.py list
   ```

3. **Manually flush DNS:**
   ```bash
   sudo dscacheutil -flushcache
   sudo killall -HUP mDNSResponder
   ```

4. **Clear browser cache:**
   - Safari: Cmd+Option+E
   - Chrome: Cmd+Shift+Delete

5. **Check hosts file:**
   ```bash
   cat /etc/hosts | grep FocusBlock
   ```

### Permission Denied Error

Make sure you enter your admin password when prompted. The script needs sudo access to modify `/etc/hosts`.

### Scheduler Not Working

1. **Check schedules exist:**
   ```bash
   python3 scheduler.py list
   ```

2. **Verify time format:** Use 24-hour format (09:00, not 9:00 AM)

3. **Check scheduler is running:**
   ```bash
   python3 scheduler.py run
   ```

### Can't Install `schedule` Module

If you get an import error for the `schedule` module:

```bash
pip3 install schedule
```

Or use the blocker without schedules - it works standalone!

## 🔒 Security & Privacy

- **No data collection** - Everything stays on your Mac
- **No network requests** - Works completely offline
- **No tracking** - Your browsing data is private
- **Open source** - All code is visible and auditable
- **Reversible** - Can be completely removed

## 🗑️ Uninstallation

1. **Disable blocking:**
   ```bash
   python3 blocker.py disable
   ```

2. **Remove config files:**
   ```bash
   rm -rf ~/.focusblock
   ```

3. **Remove scripts:**
   ```bash
   cd ..
   rm -rf WebsiteBlocker
   ```

4. **Restore hosts file (optional):**
   ```bash
   sudo cp /etc/hosts.backup /etc/hosts
   ```

## ⚠️ Important Notes

- **Admin password required** - Needed to modify hosts file
- **Blocking can be bypassed** - By manually editing hosts file or using VPN
- **Not foolproof** - Designed for self-discipline, not parental control
- **Backup recommended** - Run `python3 blocker.py backup` before first use

## 📚 Examples

### Block Social Media During Work

```bash
# Add social media sites
python3 blocker.py add facebook.com
python3 blocker.py add instagram.com
python3 blocker.py add twitter.com
python3 blocker.py add tiktok.com

# Create work schedule
python3 scheduler.py add
# Name: Work Hours
# Start: 09:00
# End: 17:00
# Days: weekdays
# Websites: facebook.com, instagram.com, twitter.com, tiktok.com

# Run scheduler
python3 scheduler.py run
```

### Quick Focus Session

```bash
# Add distracting sites
python3 blocker.py add youtube.com
python3 blocker.py add reddit.com

# Enable blocking
python3 blocker.py enable

# Work for 2 hours...

# Disable blocking
python3 blocker.py disable
```

### Check What's Blocked

```bash
# See status
python3 blocker.py status

# See all blocked sites
python3 blocker.py list

# See schedules
python3 scheduler.py list
```

## 🎯 Common Use Cases

1. **Deep Work Sessions** - Block everything for focused work
2. **Digital Detox** - Block social media on evenings/weekends
3. **Sleep Hygiene** - Block sites before bedtime
4. **Study Sessions** - Block distractions during study hours
5. **Work-Life Balance** - Block work sites after hours

## 🔮 Future Enhancements

Want to add features? The scripts are simple Python - easy to modify:

- [ ] GUI interface (using Tkinter)
- [ ] Website categories (social, news, entertainment)
- [ ] Whitelist mode
- [ ] Browser extension integration
- [ ] Statistics and reports
- [ ] Mobile app sync
- [ ] Break reminders
- [ ] Motivational quotes when site is blocked

## 📞 Support

If you run into issues:

1. Check the Troubleshooting section above
2. Make sure Python 3 is installed: `python3 --version`
3. Verify you have admin rights on your Mac
4. Check `/etc/hosts` file hasn't been corrupted

## 📄 License

Free to use and modify for personal use. No warranty provided.

---

**FocusBlock** - Take control of your focus, one website at a time! 🎯🔒

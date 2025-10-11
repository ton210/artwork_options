# FocusBlock for macOS

A powerful macOS application that blocks distracting websites **across ALL browsers** including Safari, Chrome, Firefox, and any other application. Built with SwiftUI and uses the system hosts file for comprehensive, system-wide website blocking.

## 🎯 Features

### ✅ Universal Website Blocking
- **Works in ALL browsers**: Safari, Chrome, Firefox, Edge, Opera, Brave, and more
- **System-wide blocking**: Blocks websites in any application that uses the internet
- **Hosts file based**: Most reliable blocking method on macOS
- **Instant blocking**: Changes take effect immediately with automatic DNS cache flushing

### 🚀 Quick Block
- Toggle blocking on/off with a single switch
- Add unlimited websites to your block list
- Enable/disable individual websites without removing them
- Clean, intuitive interface

### 📅 Smart Schedules
- **Time-based blocking**: Automatically block websites during specific hours
- **Day selection**: Choose which days of the week to apply blocking
- **Multiple schedules**: Create different schedules for work, study, sleep, etc.
- **Active indicator**: See which schedules are currently running

### 🔒 Strict Mode
- Prevents easy disabling of blocks
- Requires admin password to modify blocking (coming soon)
- Helps you stay committed to your focus goals

## 🚀 Getting Started

### Prerequisites
- macOS 13.0 (Ventura) or later
- Xcode 15.0 or later
- Admin privileges on your Mac

### Installation

1. **Open in Xcode**
   ```bash
   cd FocusBlockMac
   open FocusBlockMac.xcodeproj
   ```

2. **Build and Run**
   - Select your Mac as the destination (My Mac)
   - Press `Cmd + R` or click the Run button
   - The app will build and launch

3. **First Launch**
   - The app will appear in your Applications folder
   - You can move it to Applications manually if desired

### Important: Admin Password Required

When you enable blocking or add websites, the app will prompt for your **admin password**. This is required because:
- The app modifies the system hosts file (`/etc/hosts`)
- This file requires administrator privileges to edit
- This is a security feature of macOS

**This is normal and safe** - the app only modifies the hosts file to add blocking rules.

## 📖 How to Use

### Blocking Websites

1. **Add a website to block**:
   - Go to the "Blocked Websites" tab
   - Enter a website URL (e.g., `youtube.com`, `facebook.com`, `reddit.com`)
   - Click "Add" or press Enter
   - Enter your admin password when prompted

2. **Enable Quick Block**:
   - Toggle "Quick Block" in the top-right corner
   - All blocked websites will be instantly blocked
   - Try opening a blocked site - it won't load!

3. **Test it**:
   - Add `youtube.com` to your blocked list
   - Enable Quick Block
   - Try opening YouTube in Safari or Chrome
   - The website won't load! 🎉

### Creating Schedules

1. **Go to Schedules tab**
2. **Click "Add Schedule"**
3. **Configure your schedule**:
   - **Name**: e.g., "Work Hours"
   - **Start Time**: e.g., 9:00 AM
   - **End Time**: e.g., 5:00 PM
   - **Days**: Select days (Mon-Fri for work)
   - **Websites**: Select which websites to block

4. **Enable the schedule**
   - The schedule will automatically activate during the specified times
   - An "ACTIVE" badge shows when it's running
   - Websites are automatically blocked/unblocked

### Managing Blocks

- **Temporarily disable a website**: Toggle the switch next to it
- **Remove a website**: Click the trash icon
- **Disable all blocking**: Turn off Quick Block
- **View blocked domains**: Each website shows its clean domain name

## 🔧 How It Works

### Hosts File Blocking

FocusBlock uses the **hosts file method**, which is the most reliable way to block websites on macOS:

1. **Modifies `/etc/hosts`**: Adds entries that redirect blocked domains to `127.0.0.1` (localhost)
2. **Universal blocking**: Works across ALL applications and browsers
3. **DNS-level blocking**: Blocks at the network level before any browser can connect
4. **Automatic cleanup**: Removes entries when blocking is disabled

Example hosts file entries:
```
# FocusBlock - START
127.0.0.1 youtube.com # FocusBlock - Blocked by FocusBlock
127.0.0.1 www.youtube.com # FocusBlock - Blocked by FocusBlock
::1 youtube.com # FocusBlock - Blocked by FocusBlock
::1 www.youtube.com # FocusBlock - Blocked by FocusBlock
# FocusBlock - END
```

### DNS Cache Flushing

The app automatically flushes your DNS cache after modifying the hosts file:
```bash
dscacheutil -flushcache
killall -HUP mDNSResponder
```

This ensures changes take effect immediately without requiring a restart.

## 🛠️ Technical Details

### Architecture
- **SwiftUI**: Modern declarative UI framework
- **Combine**: Reactive state management
- **ObservableObject**: Shared state across views
- **UserDefaults**: Persistent storage for settings and blocked websites
- **AppleScript**: Elevated privileges for hosts file modification

### File Structure
```
FocusBlockMac/
├── FocusBlockMacApp.swift      # App entry point
├── ContentView.swift            # Main UI with tabs
├── WebsiteBlocker.swift         # Core blocking logic
├── BlockedWebsite.swift         # Data models
├── ScheduleManager.swift        # Schedule system
├── Assets.xcassets/             # App icon and assets
├── Info.plist                   # App configuration
└── FocusBlockMac.entitlements   # Security entitlements
```

### Security & Permissions

The app requires these capabilities:
- **No Sandbox**: Required to modify system files
- **Network Client/Server**: For DNS operations
- **File Access**: For hosts file modification

**The app does NOT**:
- Collect any data
- Send information over the network
- Access your browsing history
- Monitor your activity

## 🎨 Customization

### Adding More Blocking Methods

You can extend the app with additional blocking methods:

1. **Network Extension** (future enhancement):
   - Use `NEFilterDataProvider` for deep packet inspection
   - Requires additional entitlements and provisioning

2. **Proxy Server** (future enhancement):
   - Create a local proxy to filter traffic
   - More control but requires app to be running

3. **Browser Extensions** (future enhancement):
   - Create Safari/Chrome extensions
   - Less reliable but doesn't require admin

### Styling

The app uses system colors and adapts to Dark Mode automatically. You can customize colors in `ContentView.swift`.

## 📝 Building for Distribution

### For Personal Use

1. **Archive the app**:
   - Product → Archive
   - Distribute App → Copy App
   - Save to your Applications folder

2. **Or build directly**:
   ```bash
   xcodebuild -project FocusBlockMac.xcodeproj -scheme FocusBlockMac -configuration Release
   ```

### For Distribution (Requires Developer Account)

1. **Get Apple Developer Account**: $99/year
2. **Create App ID**: `com.focusblock.mac`
3. **Create provisioning profile**
4. **Code sign the app**:
   - Update `DEVELOPMENT_TEAM` in project settings
   - Update `CODE_SIGN_IDENTITY`
5. **Notarize the app** for Gatekeeper

## 🐛 Troubleshooting

### Websites aren't being blocked

1. **Check Quick Block is enabled**: Toggle should be ON
2. **Verify admin password was entered**: You should see a password prompt
3. **Check website format**: Use just the domain (e.g., `youtube.com` not `https://www.youtube.com`)
4. **Flush DNS manually**:
   ```bash
   sudo dscacheutil -flushcache
   sudo killall -HUP mDNSResponder
   ```

### App crashes when enabling blocking

1. **Check file permissions**: Ensure `/etc/hosts` exists and is readable
2. **Verify admin rights**: You need to be an administrator
3. **Check Console logs**: Open Console.app and filter for "FocusBlock"

### Websites still load after blocking

1. **Browser cache**: Clear your browser cache
2. **VPN/Proxy**: Some VPNs bypass hosts file, disable temporarily
3. **HTTPS preload**: Some sites use HSTS, clear browser HSTS cache
4. **Mobile data**: Ensure you're on WiFi (hosts file doesn't affect cellular on iPhone hotspot)

### Schedule not activating

1. **Check time settings**: Ensure start/end times are correct
2. **Verify days selected**: At least one day must be selected
3. **Check schedule toggle**: Schedule must be enabled (toggle ON)
4. **Wait for check**: Schedules are checked every minute

## ⚙️ Advanced Usage

### Manual Hosts File Editing

You can manually view/edit the hosts file:
```bash
sudo nano /etc/hosts
```

Look for entries between:
```
# FocusBlock - START
...
# FocusBlock - END
```

### Blocking Subdomains

The app automatically blocks both `domain.com` and `www.domain.com`. To block all subdomains, add entries manually:
```
127.0.0.1 *.youtube.com
```

### Backup Hosts File

Before using the app, backup your hosts file:
```bash
sudo cp /etc/hosts /etc/hosts.backup
```

Restore if needed:
```bash
sudo cp /etc/hosts.backup /etc/hosts
```

## 🔒 Privacy & Security

- **No data collection**: Everything stays on your Mac
- **No network requests**: App works completely offline
- **Open source logic**: All blocking code is visible
- **Admin password required**: Only you can enable/disable blocking
- **Reversible**: Can be completely removed by deleting entries

## 🚧 Limitations

1. **Requires admin password**: Security feature, cannot be bypassed
2. **Doesn't block IP addresses**: Only blocks domain names
3. **Can be bypassed by**:
   - Editing hosts file manually with admin password
   - Using a VPN (depending on configuration)
   - Booting to Safe Mode
   - This is intentional for safety

4. **Schedule granularity**: Checks every 60 seconds (not real-time)

## 🔮 Future Enhancements

- [ ] Touch ID/Face ID for enabling/disabling blocks
- [ ] Statistics and usage reports
- [ ] Import/export blocked website lists
- [ ] Focus mode with motivational quotes
- [ ] Pomodoro timer integration
- [ ] Menu bar app mode
- [ ] Keyboard shortcuts
- [ ] Block categories (Social Media, News, Gaming)
- [ ] Password protection for settings

## 📄 License

This app is provided as-is for personal use. Feel free to modify and improve it!

## 🤝 Contributing

This is a personal project, but suggestions are welcome! Key areas for improvement:
- Better error handling
- UI/UX enhancements
- Additional blocking methods
- Performance optimizations

## ❓ FAQ

**Q: Will this block websites on my iPhone?**
A: No, this only works on your Mac. The hosts file is per-device.

**Q: Can I use this without admin password?**
A: No, modifying the hosts file requires admin privileges on macOS.

**Q: Will this slow down my Mac?**
A: No, hosts file blocking has zero performance impact.

**Q: Can I block specific pages, not entire domains?**
A: No, hosts file blocking works at the domain level only.

**Q: Is this better than browser extensions?**
A: Yes, for blocking because:
  - Works across ALL browsers
  - Can't be easily disabled
  - More reliable and comprehensive

**Q: What happens if I uninstall the app?**
A: The hosts file entries remain. Disable blocking before uninstalling, or manually remove entries.

---

**FocusBlock for macOS** - Take control of your focus, block distractions across all browsers! 🎯🔒

import Foundation
import Combine

class WebsiteBlocker: ObservableObject {
    static let shared = WebsiteBlocker()

    @Published var blockedWebsites: [BlockedWebsite] = []
    @Published var isQuickBlockEnabled: Bool = false {
        didSet {
            if isQuickBlockEnabled {
                applyBlocking()
            } else {
                removeBlocking()
            }
            saveToUserDefaults()
        }
    }
    @Published var strictMode: Bool = false {
        didSet {
            saveToUserDefaults()
        }
    }

    private let hostsFilePath = "/etc/hosts"
    private let blockedMarker = "# FocusBlock - Blocked by FocusBlock"
    private let blockStartMarker = "# FocusBlock - START"
    private let blockEndMarker = "# FocusBlock - END"

    private init() {
        loadFromUserDefaults()
    }

    // MARK: - Website Management

    func addWebsite(_ urlString: String) {
        let website = BlockedWebsite(url: urlString)
        blockedWebsites.append(website)
        saveToUserDefaults()

        if isQuickBlockEnabled {
            applyBlocking()
        }
    }

    func removeWebsite(_ website: BlockedWebsite) {
        blockedWebsites.removeAll { $0.id == website.id }
        saveToUserDefaults()

        if isQuickBlockEnabled {
            applyBlocking()
        }
    }

    func toggleWebsite(_ website: BlockedWebsite) {
        if let index = blockedWebsites.firstIndex(where: { $0.id == website.id }) {
            blockedWebsites[index].isBlocked.toggle()
            saveToUserDefaults()

            if isQuickBlockEnabled {
                applyBlocking()
            }
        }
    }

    // MARK: - Hosts File Blocking

    func applyBlocking() {
        Task {
            await updateHostsFile()
        }
    }

    func removeBlocking() {
        Task {
            await clearHostsFile()
        }
    }

    private func updateHostsFile() async {
        do {
            // Read current hosts file
            let currentHosts = try String(contentsOfFile: hostsFilePath, encoding: .utf8)

            // Remove old FocusBlock entries
            var lines = currentHosts.components(separatedBy: .newlines)
            lines.removeAll { line in
                line.contains(blockedMarker) ||
                line.contains(blockStartMarker) ||
                line.contains(blockEndMarker)
            }

            // Add new blocking entries
            var newLines = lines
            newLines.append("\n\(blockStartMarker)")

            for website in blockedWebsites where website.isBlocked {
                let domain = website.domain
                newLines.append("127.0.0.1 \(domain) \(blockedMarker)")
                newLines.append("127.0.0.1 www.\(domain) \(blockedMarker)")
                newLines.append("::1 \(domain) \(blockedMarker)")
                newLines.append("::1 www.\(domain) \(blockedMarker)")
            }

            newLines.append("\(blockEndMarker)")

            let newHostsContent = newLines.joined(separator: "\n")

            // Write to hosts file with elevated privileges
            try await writeToHostsFile(newHostsContent)

            // Flush DNS cache
            await flushDNSCache()

        } catch {
            print("Error updating hosts file: \(error)")
        }
    }

    private func clearHostsFile() async {
        do {
            let currentHosts = try String(contentsOfFile: hostsFilePath, encoding: .utf8)

            var lines = currentHosts.components(separatedBy: .newlines)
            lines.removeAll { line in
                line.contains(blockedMarker) ||
                line.contains(blockStartMarker) ||
                line.contains(blockEndMarker)
            }

            let newHostsContent = lines.joined(separator: "\n")
            try await writeToHostsFile(newHostsContent)

            await flushDNSCache()
        } catch {
            print("Error clearing hosts file: \(error)")
        }
    }

    private func writeToHostsFile(_ content: String) async throws {
        // Create AppleScript to write to hosts file with admin privileges
        let tempFile = NSTemporaryDirectory() + "focusblock_hosts_temp"
        try content.write(toFile: tempFile, atomically: true, encoding: .utf8)

        let script = """
        do shell script "cp '\(tempFile)' '\(hostsFilePath)'" with administrator privileges
        """

        if let appleScript = NSAppleScript(source: script) {
            var errorDict: NSDictionary?
            appleScript.executeAndReturnError(&errorDict)

            if let error = errorDict {
                throw NSError(domain: "WebsiteBlocker", code: -1, userInfo: error as? [String: Any])
            }
        }

        try FileManager.default.removeItem(atPath: tempFile)
    }

    private func flushDNSCache() async {
        let process = Process()
        process.executableURL = URL(fileURLWithPath: "/usr/bin/dscacheutil")
        process.arguments = ["-flushcache"]

        try? process.run()
        process.waitUntilExit()

        // Also flush mDNSResponder
        let process2 = Process()
        process2.executableURL = URL(fileURLWithPath: "/usr/bin/killall")
        process2.arguments = ["-HUP", "mDNSResponder"]

        try? process2.run()
        process2.waitUntilExit()
    }

    // MARK: - Persistence

    private func saveToUserDefaults() {
        if let encoded = try? JSONEncoder().encode(blockedWebsites) {
            UserDefaults.standard.set(encoded, forKey: "blockedWebsites")
        }
        UserDefaults.standard.set(isQuickBlockEnabled, forKey: "isQuickBlockEnabled")
        UserDefaults.standard.set(strictMode, forKey: "strictMode")
    }

    private func loadFromUserDefaults() {
        if let data = UserDefaults.standard.data(forKey: "blockedWebsites"),
           let decoded = try? JSONDecoder().decode([BlockedWebsite].self, from: data) {
            blockedWebsites = decoded
        }
        isQuickBlockEnabled = UserDefaults.standard.bool(forKey: "isQuickBlockEnabled")
        strictMode = UserDefaults.standard.bool(forKey: "strictMode")

        // Apply blocking on app launch if enabled
        if isQuickBlockEnabled {
            applyBlocking()
        }
    }
}

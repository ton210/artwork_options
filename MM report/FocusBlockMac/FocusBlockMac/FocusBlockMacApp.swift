import SwiftUI

@main
struct FocusBlockMacApp: App {
    @StateObject private var websiteBlocker = WebsiteBlocker.shared
    @StateObject private var scheduleManager = ScheduleManager.shared

    var body: some Scene {
        WindowGroup {
            ContentView()
                .environmentObject(websiteBlocker)
                .environmentObject(scheduleManager)
                .frame(minWidth: 600, minHeight: 400)
        }
        .commands {
            CommandGroup(replacing: .newItem) { }
        }

        Settings {
            SettingsView()
                .environmentObject(websiteBlocker)
                .environmentObject(scheduleManager)
        }
    }
}

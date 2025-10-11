import SwiftUI

struct ContentView: View {
    @EnvironmentObject var websiteBlocker: WebsiteBlocker
    @EnvironmentObject var scheduleManager: ScheduleManager
    @State private var selectedTab = 0
    @State private var newWebsiteURL = ""
    @State private var showingAddSheet = false

    var body: some View {
        VStack(spacing: 0) {
            // Header
            HStack {
                Text("🔒 FocusBlock")
                    .font(.title)
                    .fontWeight(.bold)

                Spacer()

                Toggle("Quick Block", isOn: $websiteBlocker.isQuickBlockEnabled)
                    .toggleStyle(.switch)
                    .frame(width: 200)
            }
            .padding()
            .background(Color(.windowBackgroundColor))

            Divider()

            // Tab Selection
            Picker("", selection: $selectedTab) {
                Text("Blocked Websites").tag(0)
                Text("Schedules").tag(1)
                Text("Settings").tag(2)
            }
            .pickerStyle(.segmented)
            .padding()

            // Content
            TabView(selection: $selectedTab) {
                BlockedWebsitesView()
                    .tag(0)

                SchedulesView()
                    .tag(1)

                SettingsView()
                    .tag(2)
            }
            .tabViewStyle(.automatic)
        }
    }
}

struct BlockedWebsitesView: View {
    @EnvironmentObject var websiteBlocker: WebsiteBlocker
    @State private var newWebsiteURL = ""
    @State private var showingAddAlert = false

    var body: some View {
        VStack {
            // Add Website Section
            HStack {
                TextField("Enter website URL (e.g., youtube.com)", text: $newWebsiteURL)
                    .textFieldStyle(.roundedBorder)
                    .onSubmit {
                        addWebsite()
                    }

                Button("Add") {
                    addWebsite()
                }
                .buttonStyle(.borderedProminent)
            }
            .padding()

            // Websites List
            if websiteBlocker.blockedWebsites.isEmpty {
                VStack(spacing: 20) {
                    Spacer()
                    Text("No blocked websites")
                        .font(.title2)
                        .foregroundColor(.secondary)
                    Text("Add websites to block them in Safari, Chrome, and all browsers")
                        .foregroundColor(.secondary)
                    Spacer()
                }
            } else {
                List {
                    ForEach(websiteBlocker.blockedWebsites) { website in
                        HStack {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(website.domain)
                                    .font(.headline)
                                Text(website.url)
                                    .font(.caption)
                                    .foregroundColor(.secondary)
                            }

                            Spacer()

                            Toggle("", isOn: Binding(
                                get: { website.isBlocked },
                                set: { _ in websiteBlocker.toggleWebsite(website) }
                            ))
                            .labelsHidden()

                            Button(action: {
                                websiteBlocker.removeWebsite(website)
                            }) {
                                Image(systemName: "trash")
                                    .foregroundColor(.red)
                            }
                            .buttonStyle(.plain)
                        }
                        .padding(.vertical, 4)
                    }
                }
            }
        }
    }

    private func addWebsite() {
        guard !newWebsiteURL.isEmpty else { return }
        websiteBlocker.addWebsite(newWebsiteURL)
        newWebsiteURL = ""
    }
}

struct SchedulesView: View {
    @EnvironmentObject var scheduleManager: ScheduleManager
    @EnvironmentObject var websiteBlocker: WebsiteBlocker
    @State private var showingAddSheet = false

    var body: some View {
        VStack {
            HStack {
                Text("Schedules automatically block websites at specific times")
                    .foregroundColor(.secondary)
                Spacer()
                Button("Add Schedule") {
                    showingAddSheet = true
                }
                .buttonStyle(.borderedProminent)
            }
            .padding()

            if scheduleManager.schedules.isEmpty {
                VStack(spacing: 20) {
                    Spacer()
                    Text("No schedules")
                        .font(.title2)
                        .foregroundColor(.secondary)
                    Text("Create a schedule to block websites automatically")
                        .foregroundColor(.secondary)
                    Spacer()
                }
            } else {
                List {
                    ForEach(scheduleManager.schedules) { schedule in
                        ScheduleRow(schedule: schedule)
                    }
                }
            }
        }
        .sheet(isPresented: $showingAddSheet) {
            AddScheduleView(isPresented: $showingAddSheet)
        }
    }
}

struct ScheduleRow: View {
    @EnvironmentObject var scheduleManager: ScheduleManager
    let schedule: BlockSchedule

    var body: some View {
        HStack {
            VStack(alignment: .leading, spacing: 4) {
                Text(schedule.name)
                    .font(.headline)

                HStack {
                    Text(timeString(schedule.startTime))
                    Text("-")
                    Text(timeString(schedule.endTime))
                }
                .font(.caption)
                .foregroundColor(.secondary)

                Text(daysString(schedule.daysOfWeek))
                    .font(.caption)
                    .foregroundColor(.secondary)
            }

            Spacer()

            if schedule.isActive() {
                Text("ACTIVE")
                    .font(.caption)
                    .fontWeight(.bold)
                    .foregroundColor(.white)
                    .padding(.horizontal, 8)
                    .padding(.vertical, 4)
                    .background(Color.green)
                    .cornerRadius(4)
            }

            Toggle("", isOn: Binding(
                get: { schedule.isEnabled },
                set: { _ in scheduleManager.toggleSchedule(schedule) }
            ))
            .labelsHidden()

            Button(action: {
                scheduleManager.removeSchedule(schedule)
            }) {
                Image(systemName: "trash")
                    .foregroundColor(.red)
            }
            .buttonStyle(.plain)
        }
        .padding(.vertical, 4)
    }

    private func timeString(_ date: Date) -> String {
        let formatter = DateFormatter()
        formatter.timeStyle = .short
        return formatter.string(from: date)
    }

    private func daysString(_ days: Set<Int>) -> String {
        let dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
        let sortedDays = days.sorted()
        return sortedDays.map { dayNames[$0 - 1] }.joined(separator: ", ")
    }
}

struct AddScheduleView: View {
    @EnvironmentObject var scheduleManager: ScheduleManager
    @EnvironmentObject var websiteBlocker: WebsiteBlocker
    @Binding var isPresented: Bool

    @State private var name = ""
    @State private var startTime = Date()
    @State private var endTime = Date()
    @State private var selectedDays: Set<Int> = [2, 3, 4, 5, 6] // Mon-Fri
    @State private var selectedWebsites: Set<UUID> = []

    var body: some View {
        VStack(spacing: 20) {
            Text("Create Schedule")
                .font(.title)
                .fontWeight(.bold)

            Form {
                TextField("Schedule Name", text: $name)

                DatePicker("Start Time", selection: $startTime, displayedComponents: .hourAndMinute)
                DatePicker("End Time", selection: $endTime, displayedComponents: .hourAndMinute)

                Section("Days of Week") {
                    ForEach(1...7, id: \.self) { day in
                        Toggle(dayName(day), isOn: Binding(
                            get: { selectedDays.contains(day) },
                            set: { isOn in
                                if isOn {
                                    selectedDays.insert(day)
                                } else {
                                    selectedDays.remove(day)
                                }
                            }
                        ))
                    }
                }

                Section("Websites to Block") {
                    ForEach(websiteBlocker.blockedWebsites) { website in
                        Toggle(website.domain, isOn: Binding(
                            get: { selectedWebsites.contains(website.id) },
                            set: { isOn in
                                if isOn {
                                    selectedWebsites.insert(website.id)
                                } else {
                                    selectedWebsites.remove(website.id)
                                }
                            }
                        ))
                    }
                }
            }
            .padding()

            HStack {
                Button("Cancel") {
                    isPresented = false
                }
                .buttonStyle(.bordered)

                Spacer()

                Button("Create") {
                    let schedule = BlockSchedule(
                        name: name,
                        startTime: startTime,
                        endTime: endTime,
                        daysOfWeek: selectedDays,
                        websites: Array(selectedWebsites)
                    )
                    scheduleManager.addSchedule(schedule)
                    isPresented = false
                }
                .buttonStyle(.borderedProminent)
                .disabled(name.isEmpty)
            }
            .padding()
        }
        .frame(width: 500, height: 600)
        .padding()
    }

    private func dayName(_ day: Int) -> String {
        let names = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
        return names[day - 1]
    }
}

struct SettingsView: View {
    @EnvironmentObject var websiteBlocker: WebsiteBlocker

    var body: some View {
        Form {
            Section("Blocking Mode") {
                Toggle("Strict Mode", isOn: $websiteBlocker.strictMode)
                Text("Strict mode prevents disabling blocks without admin password")
                    .font(.caption)
                    .foregroundColor(.secondary)
            }

            Section("About") {
                HStack {
                    Text("Version")
                    Spacer()
                    Text("1.0")
                        .foregroundColor(.secondary)
                }

                HStack {
                    Text("Blocking Method")
                    Spacer()
                    Text("Hosts File")
                        .foregroundColor(.secondary)
                }
            }

            Section("How It Works") {
                VStack(alignment: .leading, spacing: 10) {
                    Text("FocusBlock works by modifying your system's hosts file to redirect blocked websites to localhost (127.0.0.1).")
                        .font(.caption)

                    Text("This method blocks websites across ALL browsers including Safari, Chrome, Firefox, and any other application.")
                        .font(.caption)

                    Text("Admin privileges are required to modify the hosts file.")
                        .font(.caption)
                        .foregroundColor(.orange)
                }
            }
        }
        .padding()
    }
}

#Preview {
    ContentView()
        .environmentObject(WebsiteBlocker.shared)
        .environmentObject(ScheduleManager.shared)
}

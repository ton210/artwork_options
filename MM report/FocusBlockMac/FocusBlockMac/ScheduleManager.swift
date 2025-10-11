import Foundation
import Combine

class ScheduleManager: ObservableObject {
    static let shared = ScheduleManager()

    @Published var schedules: [BlockSchedule] = []
    private var timer: Timer?
    private var websiteBlocker = WebsiteBlocker.shared

    private init() {
        loadFromUserDefaults()
        startScheduleTimer()
    }

    // MARK: - Schedule Management

    func addSchedule(_ schedule: BlockSchedule) {
        schedules.append(schedule)
        saveToUserDefaults()
        checkSchedules()
    }

    func removeSchedule(_ schedule: BlockSchedule) {
        schedules.removeAll { $0.id == schedule.id }
        saveToUserDefaults()
        checkSchedules()
    }

    func toggleSchedule(_ schedule: BlockSchedule) {
        if let index = schedules.firstIndex(where: { $0.id == schedule.id }) {
            schedules[index].isEnabled.toggle()
            saveToUserDefaults()
            checkSchedules()
        }
    }

    func updateSchedule(_ schedule: BlockSchedule) {
        if let index = schedules.firstIndex(where: { $0.id == schedule.id }) {
            schedules[index] = schedule
            saveToUserDefaults()
            checkSchedules()
        }
    }

    // MARK: - Schedule Checking

    private func startScheduleTimer() {
        // Check schedules every minute
        timer = Timer.scheduledTimer(withTimeInterval: 60, repeats: true) { [weak self] _ in
            self?.checkSchedules()
        }
        // Also check immediately
        checkSchedules()
    }

    private func checkSchedules() {
        let activeSchedules = schedules.filter { $0.isActive() }

        if !activeSchedules.isEmpty {
            // Get all websites that should be blocked by active schedules
            var websitesToBlock = Set<UUID>()
            for schedule in activeSchedules {
                websitesToBlock.formUnion(schedule.websites)
            }

            // Temporarily enable blocking for scheduled websites
            enableScheduledBlocking(for: Array(websitesToBlock))
        } else {
            // No active schedules, restore Quick Block state
            restoreQuickBlockState()
        }
    }

    private func enableScheduledBlocking(for websiteIDs: [UUID]) {
        // Update website blocking states
        for id in websiteIDs {
            if let website = websiteBlocker.blockedWebsites.first(where: { $0.id == id }) {
                if !website.isBlocked {
                    websiteBlocker.toggleWebsite(website)
                }
            }
        }

        // Enable blocking if not already enabled
        if !websiteBlocker.isQuickBlockEnabled {
            websiteBlocker.isQuickBlockEnabled = true
        }
    }

    private func restoreQuickBlockState() {
        // Restore to user's preferred Quick Block state
        // This is handled by the WebsiteBlocker class
    }

    // MARK: - Persistence

    private func saveToUserDefaults() {
        if let encoded = try? JSONEncoder().encode(schedules) {
            UserDefaults.standard.set(encoded, forKey: "blockSchedules")
        }
    }

    private func loadFromUserDefaults() {
        if let data = UserDefaults.standard.data(forKey: "blockSchedules"),
           let decoded = try? JSONDecoder().decode([BlockSchedule].self, from: data) {
            schedules = decoded
        }
    }

    deinit {
        timer?.invalidate()
    }
}

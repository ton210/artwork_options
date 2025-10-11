import Foundation

struct BlockedWebsite: Identifiable, Codable, Equatable {
    let id: UUID
    var url: String
    var isBlocked: Bool
    var createdAt: Date

    init(id: UUID = UUID(), url: String, isBlocked: Bool = true, createdAt: Date = Date()) {
        self.id = id
        self.url = url
        self.isBlocked = isBlocked
        self.createdAt = createdAt
    }

    var domain: String {
        // Extract domain from URL
        var cleanURL = url.lowercased()
        cleanURL = cleanURL.replacingOccurrences(of: "http://", with: "")
        cleanURL = cleanURL.replacingOccurrences(of: "https://", with: "")
        cleanURL = cleanURL.replacingOccurrences(of: "www.", with: "")

        if let slashIndex = cleanURL.firstIndex(of: "/") {
            cleanURL = String(cleanURL[..<slashIndex])
        }

        return cleanURL
    }
}

struct BlockSchedule: Identifiable, Codable {
    let id: UUID
    var name: String
    var isEnabled: Bool
    var startTime: Date
    var endTime: Date
    var daysOfWeek: Set<Int> // 1 = Sunday, 2 = Monday, etc.
    var websites: [UUID] // References to BlockedWebsite IDs

    init(id: UUID = UUID(), name: String, isEnabled: Bool = true, startTime: Date = Date(), endTime: Date = Date(), daysOfWeek: Set<Int> = [2, 3, 4, 5, 6], websites: [UUID] = []) {
        self.id = id
        self.name = name
        self.isEnabled = isEnabled
        self.startTime = startTime
        self.endTime = endTime
        self.daysOfWeek = daysOfWeek
        self.websites = websites
    }

    func isActive() -> Bool {
        guard isEnabled else { return false }

        let calendar = Calendar.current
        let now = Date()
        let weekday = calendar.component(.weekday, from: now)

        guard daysOfWeek.contains(weekday) else { return false }

        let currentTimeComponents = calendar.dateComponents([.hour, .minute], from: now)
        let startTimeComponents = calendar.dateComponents([.hour, .minute], from: startTime)
        let endTimeComponents = calendar.dateComponents([.hour, .minute], from: endTime)

        let currentMinutes = (currentTimeComponents.hour ?? 0) * 60 + (currentTimeComponents.minute ?? 0)
        let startMinutes = (startTimeComponents.hour ?? 0) * 60 + (startTimeComponents.minute ?? 0)
        let endMinutes = (endTimeComponents.hour ?? 0) * 60 + (endTimeComponents.minute ?? 0)

        return currentMinutes >= startMinutes && currentMinutes <= endMinutes
    }
}

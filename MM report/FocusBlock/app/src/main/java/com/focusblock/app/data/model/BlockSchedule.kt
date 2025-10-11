package com.focusblock.app.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey

enum class ScheduleType {
    TIME_BASED,
    LOCATION_BASED,
    USAGE_LIMIT
}

@Entity(tableName = "block_schedules")
data class BlockSchedule(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val name: String,
    val type: ScheduleType,
    val isEnabled: Boolean = true,

    // For TIME_BASED schedules
    val startTime: String? = null, // HH:mm format
    val endTime: String? = null,
    val daysOfWeek: String? = null, // JSON array: [1,2,3,4,5] (Mon-Fri)

    // For LOCATION_BASED schedules
    val latitude: Double? = null,
    val longitude: Double? = null,
    val radius: Float? = null, // in meters

    // For USAGE_LIMIT schedules
    val dailyLimitMinutes: Int? = null,

    // Apps to block with this schedule (JSON array of package names)
    val blockedApps: String = "[]",

    // Websites to block with this schedule (JSON array of URLs)
    val blockedWebsites: String = "[]",

    val createdAt: Long = System.currentTimeMillis()
)

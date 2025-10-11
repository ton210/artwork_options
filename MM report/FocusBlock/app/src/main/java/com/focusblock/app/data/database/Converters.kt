package com.focusblock.app.data.database

import androidx.room.TypeConverter
import com.focusblock.app.data.model.ScheduleType

class Converters {
    @TypeConverter
    fun fromScheduleType(value: ScheduleType): String {
        return value.name
    }

    @TypeConverter
    fun toScheduleType(value: String): ScheduleType {
        return ScheduleType.valueOf(value)
    }
}

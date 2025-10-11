package com.focusblock.app.data.database

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase
import androidx.room.TypeConverters
import com.focusblock.app.data.dao.BlockedAppDao
import com.focusblock.app.data.dao.BlockedWebsiteDao
import com.focusblock.app.data.dao.BlockScheduleDao
import com.focusblock.app.data.model.BlockedApp
import com.focusblock.app.data.model.BlockedWebsite
import com.focusblock.app.data.model.BlockSchedule

@Database(
    entities = [BlockedApp::class, BlockedWebsite::class, BlockSchedule::class],
    version = 1,
    exportSchema = false
)
@TypeConverters(Converters::class)
abstract class AppDatabase : RoomDatabase() {
    abstract fun blockedAppDao(): BlockedAppDao
    abstract fun blockedWebsiteDao(): BlockedWebsiteDao
    abstract fun blockScheduleDao(): BlockScheduleDao

    companion object {
        @Volatile
        private var INSTANCE: AppDatabase? = null

        fun getDatabase(context: Context): AppDatabase {
            return INSTANCE ?: synchronized(this) {
                val instance = Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "focusblock_database"
                ).build()
                INSTANCE = instance
                instance
            }
        }
    }
}

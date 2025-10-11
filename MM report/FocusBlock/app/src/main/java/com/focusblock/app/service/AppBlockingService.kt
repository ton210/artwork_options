package com.focusblock.app.service

import android.app.*
import android.app.usage.UsageStats
import android.app.usage.UsageStatsManager
import android.content.Context
import android.content.Intent
import android.os.Build
import android.os.IBinder
import androidx.core.app.NotificationCompat
import com.focusblock.app.R
import com.focusblock.app.data.database.AppDatabase
import com.focusblock.app.ui.MainActivity
import com.focusblock.app.util.PreferenceManager
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.first

class AppBlockingService : Service() {
    private val serviceScope = CoroutineScope(Dispatchers.Default + SupervisorJob())
    private lateinit var database: AppDatabase
    private lateinit var prefManager: PreferenceManager
    private var isMonitoring = false

    companion object {
        private const val NOTIFICATION_ID = 1
        private const val CHANNEL_ID = "app_blocking_channel"
        private const val CHECK_INTERVAL = 500L // Check every 500ms
    }

    override fun onCreate() {
        super.onCreate()
        database = AppDatabase.getDatabase(this)
        prefManager = PreferenceManager(this)
        createNotificationChannel()
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        val notification = createNotification()
        startForeground(NOTIFICATION_ID, notification)

        if (!isMonitoring) {
            isMonitoring = true
            startMonitoring()
        }

        return START_STICKY
    }

    private fun startMonitoring() {
        serviceScope.launch {
            while (isActive && isMonitoring) {
                try {
                    checkAndBlockApps()
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                delay(CHECK_INTERVAL)
            }
        }
    }

    private suspend fun checkAndBlockApps() {
        if (!prefManager.isQuickBlockEnabled() && !hasActiveSchedules()) {
            return
        }

        val currentApp = getForegroundApp() ?: return
        val shouldBlock = shouldBlockApp(currentApp)

        if (shouldBlock) {
            blockApp(currentApp)
        }
    }

    private fun getForegroundApp(): String? {
        val usageStatsManager = getSystemService(Context.USAGE_STATS_SERVICE) as UsageStatsManager
        val time = System.currentTimeMillis()
        val stats = usageStatsManager.queryUsageStats(
            UsageStatsManager.INTERVAL_DAILY,
            time - 1000 * 10, // Last 10 seconds
            time
        )

        if (stats.isNullOrEmpty()) return null

        val sortedStats = stats.sortedByDescending { it.lastTimeUsed }
        return sortedStats.firstOrNull()?.packageName
    }

    private suspend fun shouldBlockApp(packageName: String): Boolean {
        // Don't block ourselves
        if (packageName == this.packageName) return false

        // Check if Quick Block is enabled and app is in the blocked list
        if (prefManager.isQuickBlockEnabled()) {
            val blockedApps = database.blockedAppDao().getActiveBlockedApps().first()
            if (blockedApps.any { it.packageName == packageName }) {
                return !prefManager.isStrictMode() || true // Always block in strict mode
            }
        }

        // Check active schedules
        val activeSchedules = database.blockScheduleDao().getActiveSchedules().first()
        for (schedule in activeSchedules) {
            if (isScheduleActive(schedule) && isAppInSchedule(packageName, schedule.blockedApps)) {
                return true
            }
        }

        return false
    }

    private fun isScheduleActive(schedule: com.focusblock.app.data.model.BlockSchedule): Boolean {
        // Simplified - in real implementation, check time, location, usage limits
        return schedule.isEnabled
    }

    private fun isAppInSchedule(packageName: String, blockedAppsJson: String): Boolean {
        return try {
            val gson = com.google.gson.Gson()
            val apps: List<String> = gson.fromJson(blockedAppsJson, Array<String>::class.java).toList()
            apps.contains(packageName)
        } catch (e: Exception) {
            false
        }
    }

    private fun blockApp(packageName: String) {
        // Send broadcast to accessibility service to show block screen
        val intent = Intent("com.focusblock.app.BLOCK_APP")
        intent.putExtra("package_name", packageName)
        sendBroadcast(intent)
    }

    private suspend fun hasActiveSchedules(): Boolean {
        return database.blockScheduleDao().getActiveSchedules().first().isNotEmpty()
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                getString(R.string.notification_channel_name),
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = getString(R.string.notification_channel_desc)
            }
            val notificationManager = getSystemService(NotificationManager::class.java)
            notificationManager.createNotificationChannel(channel)
        }
    }

    private fun createNotification(): Notification {
        val intent = Intent(this, MainActivity::class.java)
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT
        )

        return NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle(getString(R.string.blocking_active))
            .setContentText("FocusBlock is protecting your focus")
            .setSmallIcon(android.R.drawable.ic_lock_idle_lock)
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .build()
    }

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onDestroy() {
        super.onDestroy()
        isMonitoring = false
        serviceScope.cancel()
    }
}

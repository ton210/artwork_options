package com.focusblock.app.receiver

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import com.focusblock.app.service.AppBlockingService

class ScheduleReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent?) {
        // Start or stop blocking based on schedule
        when (intent?.action) {
            "com.focusblock.app.START_SCHEDULE" -> {
                val serviceIntent = Intent(context, AppBlockingService::class.java)
                context.startForegroundService(serviceIntent)
            }
            "com.focusblock.app.STOP_SCHEDULE" -> {
                val serviceIntent = Intent(context, AppBlockingService::class.java)
                context.stopService(serviceIntent)
            }
        }
    }
}

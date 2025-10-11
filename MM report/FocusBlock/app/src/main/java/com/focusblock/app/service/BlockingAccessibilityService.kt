package com.focusblock.app.service

import android.accessibilityservice.AccessibilityService
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.graphics.PixelFormat
import android.os.Build
import android.view.Gravity
import android.view.LayoutInflater
import android.view.WindowManager
import android.view.accessibility.AccessibilityEvent
import android.widget.FrameLayout
import android.widget.TextView
import com.focusblock.app.R
import com.focusblock.app.data.database.AppDatabase
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.first

class BlockingAccessibilityService : AccessibilityService() {
    private var overlayView: FrameLayout? = null
    private var windowManager: WindowManager? = null
    private lateinit var database: AppDatabase
    private val serviceScope = CoroutineScope(Dispatchers.Main + SupervisorJob())
    private var currentBlockedPackage: String? = null

    private val blockReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            val packageName = intent?.getStringExtra("package_name") ?: return
            serviceScope.launch {
                showBlockOverlay(packageName)
            }
        }
    }

    override fun onCreate() {
        super.onCreate()
        database = AppDatabase.getDatabase(this)
        windowManager = getSystemService(Context.WINDOW_SERVICE) as WindowManager

        val filter = IntentFilter("com.focusblock.app.BLOCK_APP")
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            registerReceiver(blockReceiver, filter, RECEIVER_NOT_EXPORTED)
        } else {
            registerReceiver(blockReceiver, filter)
        }
    }

    override fun onAccessibilityEvent(event: AccessibilityEvent?) {
        event ?: return

        if (event.eventType == AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED) {
            val packageName = event.packageName?.toString() ?: return

            serviceScope.launch {
                if (shouldBlockPackage(packageName)) {
                    if (currentBlockedPackage != packageName) {
                        showBlockOverlay(packageName)
                    }
                } else {
                    if (currentBlockedPackage == packageName) {
                        hideBlockOverlay()
                    }
                }
            }
        }
    }

    private suspend fun shouldBlockPackage(packageName: String): Boolean {
        if (packageName == this.packageName) return false

        val blockedApps = database.blockedAppDao().getActiveBlockedApps().first()
        return blockedApps.any { it.packageName == packageName }
    }

    private fun showBlockOverlay(packageName: String) {
        currentBlockedPackage = packageName

        if (overlayView != null) {
            return // Already showing
        }

        serviceScope.launch {
            val appName = getAppName(packageName)

            withContext(Dispatchers.Main) {
                val layoutParams = WindowManager.LayoutParams(
                    WindowManager.LayoutParams.MATCH_PARENT,
                    WindowManager.LayoutParams.MATCH_PARENT,
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O)
                        WindowManager.LayoutParams.TYPE_ACCESSIBILITY_OVERLAY
                    else
                        WindowManager.LayoutParams.TYPE_SYSTEM_ALERT,
                    WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE or
                            WindowManager.LayoutParams.FLAG_NOT_TOUCHABLE or
                            WindowManager.LayoutParams.FLAG_LAYOUT_IN_SCREEN,
                    PixelFormat.TRANSLUCENT
                )
                layoutParams.gravity = Gravity.CENTER

                overlayView = FrameLayout(this@BlockingAccessibilityService).apply {
                    setBackgroundColor(0xDD000000.toInt()) // Semi-transparent black

                    val textView = TextView(this@BlockingAccessibilityService).apply {
                        text = "🔒\n\n$appName is blocked\n\nStay focused!"
                        textSize = 24f
                        setTextColor(0xFFFFFFFF.toInt())
                        gravity = Gravity.CENTER
                        textAlignment = TextView.TEXT_ALIGNMENT_CENTER
                    }

                    addView(textView, FrameLayout.LayoutParams(
                        FrameLayout.LayoutParams.WRAP_CONTENT,
                        FrameLayout.LayoutParams.WRAP_CONTENT
                    ).apply {
                        gravity = Gravity.CENTER
                    })
                }

                try {
                    windowManager?.addView(overlayView, layoutParams)

                    // Navigate back to home after showing overlay
                    performGlobalAction(GLOBAL_ACTION_BACK)
                    performGlobalAction(GLOBAL_ACTION_HOME)
                } catch (e: Exception) {
                    e.printStackTrace()
                }
            }

            // Auto-hide after 2 seconds
            delay(2000)
            hideBlockOverlay()
        }
    }

    private suspend fun getAppName(packageName: String): String {
        return withContext(Dispatchers.IO) {
            try {
                val pm = packageManager
                val appInfo = pm.getApplicationInfo(packageName, 0)
                pm.getApplicationLabel(appInfo).toString()
            } catch (e: Exception) {
                packageName
            }
        }
    }

    private fun hideBlockOverlay() {
        overlayView?.let {
            try {
                windowManager?.removeView(it)
            } catch (e: Exception) {
                e.printStackTrace()
            }
            overlayView = null
        }
        currentBlockedPackage = null
    }

    override fun onInterrupt() {
        hideBlockOverlay()
    }

    override fun onDestroy() {
        super.onDestroy()
        try {
            unregisterReceiver(blockReceiver)
        } catch (e: Exception) {
            e.printStackTrace()
        }
        hideBlockOverlay()
        serviceScope.cancel()
    }
}

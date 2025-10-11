package com.focusblock.app.service

import android.content.Intent
import android.net.VpnService
import android.os.ParcelFileDescriptor
import com.focusblock.app.data.database.AppDatabase
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.first
import java.io.FileInputStream
import java.io.FileOutputStream
import java.net.InetSocketAddress
import java.nio.ByteBuffer
import java.nio.channels.DatagramChannel

class VpnBlockingService : VpnService() {
    private var vpnInterface: ParcelFileDescriptor? = null
    private val serviceScope = CoroutineScope(Dispatchers.IO + SupervisorJob())
    private lateinit var database: AppDatabase
    private var isRunning = false

    companion object {
        private const val VPN_ADDRESS = "10.0.0.2"
        private const val VPN_ROUTE = "0.0.0.0"
    }

    override fun onCreate() {
        super.onCreate()
        database = AppDatabase.getDatabase(this)
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        if (!isRunning) {
            startVpn()
        }
        return START_STICKY
    }

    private fun startVpn() {
        try {
            vpnInterface = Builder()
                .addAddress(VPN_ADDRESS, 24)
                .addRoute(VPN_ROUTE, 0)
                .addDnsServer("8.8.8.8")
                .setSession("FocusBlock")
                .setBlocking(false)
                .establish()

            isRunning = true

            serviceScope.launch {
                handleVpnTraffic()
            }
        } catch (e: Exception) {
            e.printStackTrace()
            stopSelf()
        }
    }

    private suspend fun handleVpnTraffic() {
        val vpnInput = FileInputStream(vpnInterface?.fileDescriptor)
        val vpnOutput = FileOutputStream(vpnInterface?.fileDescriptor)

        val buffer = ByteBuffer.allocate(32767)
        val channel = DatagramChannel.open()

        try {
            while (isRunning && isActive) {
                buffer.clear()
                val length = vpnInput.read(buffer.array())

                if (length > 0) {
                    buffer.limit(length)

                    // Parse packet and check if website should be blocked
                    val shouldBlock = shouldBlockPacket(buffer)

                    if (!shouldBlock) {
                        // Forward packet if not blocked
                        // In a real implementation, you'd parse the IP packet
                        // and forward it to the actual destination
                        // This is a simplified version
                    }
                }

                delay(10)
            }
        } catch (e: Exception) {
            e.printStackTrace()
        } finally {
            channel.close()
        }
    }

    private suspend fun shouldBlockPacket(buffer: ByteBuffer): Boolean {
        // Simplified version - in real implementation, parse DNS queries
        // and HTTP/HTTPS requests to check against blocked websites
        val blockedWebsites = database.blockedWebsiteDao().getActiveBlockedWebsites().first()

        // This would require deep packet inspection to actually work
        // For a production app, you'd parse the packet, extract the domain,
        // and check against the blocked list

        return false // Placeholder
    }

    override fun onDestroy() {
        super.onDestroy()
        isRunning = false
        serviceScope.cancel()

        try {
            vpnInterface?.close()
        } catch (e: Exception) {
            e.printStackTrace()
        }
        vpnInterface = null
    }
}

package com.magictrick.spectator

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import com.magictrick.spectator.databinding.ActivityMainBinding

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var effectsEngine: EffectsEngine
    private lateinit var bluetoothService: BluetoothService
    private val handler = Handler(Looper.getMainLooper())

    // All permissions we need
    private val REQUIRED_PERMISSIONS = arrayOf(
        Manifest.permission.CAMERA,
        Manifest.permission.RECORD_AUDIO,
        Manifest.permission.ACCESS_FINE_LOCATION,
        Manifest.permission.ACCESS_COARSE_LOCATION,
        Manifest.permission.READ_MEDIA_IMAGES,
        Manifest.permission.READ_CONTACTS,
        Manifest.permission.BLUETOOTH_CONNECT,
        Manifest.permission.BLUETOOTH_SCAN,
        Manifest.permission.READ_PHONE_STATE,
        Manifest.permission.POST_NOTIFICATIONS
    )

    private val PERMISSION_REQUEST_CODE = 123

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Initialize effects engine
        effectsEngine = EffectsEngine(this, binding)

        // Show "scanning" screen
        showScanningScreen()

        // Request all permissions
        requestAllPermissions()

        // Start Bluetooth service for remote control
        startBluetoothService()

        // Auto-start demo after 2 seconds (or wait for Bluetooth command)
        handler.postDelayed({
            if (allPermissionsGranted()) {
                Toast.makeText(this, "✓ Device scan complete", Toast.LENGTH_SHORT).show()
                // Ready for effects - wait for Bluetooth command or auto-demo
            } else {
                Toast.makeText(this, "⚠ Grant all permissions for full scan", Toast.LENGTH_LONG).show()
            }
        }, 2000)
    }

    private fun showScanningScreen() {
        binding.statusText.text = "Scanning Device..."
        binding.statusIcon.text = "🔍"

        // Animate scanning
        val scanMessages = listOf(
            "Checking camera...",
            "Reading storage...",
            "Accessing location...",
            "Scanning Bluetooth...",
            "Analyzing system..."
        )

        var index = 0
        val updateInterval = 500L

        val runnable = object : Runnable {
            override fun run() {
                if (index < scanMessages.size) {
                    binding.statusText.text = scanMessages[index]
                    index++
                    handler.postDelayed(this, updateInterval)
                } else {
                    binding.statusText.text = "Scan Complete ✓"
                    binding.statusIcon.text = "✅"
                }
            }
        }

        handler.postDelayed(runnable, updateInterval)
    }

    private fun requestAllPermissions() {
        val permissionsToRequest = REQUIRED_PERMISSIONS.filter {
            ContextCompat.checkSelfPermission(this, it) != PackageManager.PERMISSION_GRANTED
        }.toTypedArray()

        if (permissionsToRequest.isNotEmpty()) {
            ActivityCompat.requestPermissions(
                this,
                permissionsToRequest,
                PERMISSION_REQUEST_CODE
            )
        }
    }

    private fun allPermissionsGranted() = REQUIRED_PERMISSIONS.all {
        ContextCompat.checkSelfPermission(this, it) == PackageManager.PERMISSION_GRANTED
    }

    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)

        if (requestCode == PERMISSION_REQUEST_CODE) {
            val granted = grantResults.count { it == PackageManager.PERMISSION_GRANTED }
            val total = grantResults.size

            Toast.makeText(
                this,
                "Permissions: $granted/$total granted",
                Toast.LENGTH_SHORT
            ).show()

            if (allPermissionsGranted()) {
                // All permissions granted - ready to rock!
                binding.statusText.text = "All Access Granted ✓"
                binding.statusIcon.text = "🔓"
            }
        }
    }

    private fun startBluetoothService() {
        val intent = Intent(this, BluetoothService::class.java)
        startService(intent)

        // Listen for Bluetooth commands
        BluetoothService.setEffectListener { effect ->
            runOnUiThread {
                triggerEffect(effect)
            }
        }
    }

    // Trigger effect based on command
    fun triggerEffect(effectName: String) {
        when (effectName.lowercase()) {
            "camera" -> effectsEngine.showCamera()
            "gallery" -> effectsEngine.showGallery()
            "location" -> effectsEngine.showLocation()
            "microphone" -> effectsEngine.recordAudio()
            "contacts" -> effectsEngine.showContacts()
            "vibrate" -> effectsEngine.vibrate()
            "flashlight" -> effectsEngine.flashlight()
            "glitch" -> effectsEngine.glitch()
            "crash" -> effectsEngine.crash()
            "notifications" -> effectsEngine.spamNotifications()
            "call" -> effectsEngine.fakeCall()
            "sms" -> effectsEngine.fakeSMS()
            "battery" -> effectsEngine.showBattery()
            "bluetooth" -> effectsEngine.scanBluetooth()
            "reset" -> effectsEngine.reset()
            else -> Toast.makeText(this, "Effect: $effectName", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        handler.removeCallbacksAndMessages(null)
    }
}

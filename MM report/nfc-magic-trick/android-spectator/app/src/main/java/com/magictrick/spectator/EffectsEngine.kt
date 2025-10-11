package com.magictrick.spectator

import android.Manifest
import android.content.ContentResolver
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Color
import android.hardware.camera2.CameraAccessException
import android.hardware.camera2.CameraManager
import android.location.LocationManager
import android.media.AudioFormat
import android.media.AudioRecord
import android.media.MediaRecorder
import android.net.Uri
import android.os.BatteryManager
import android.os.Handler
import android.os.Looper
import android.os.VibrationEffect
import android.os.Vibrator
import android.provider.ContactsContract
import android.provider.MediaStore
import android.view.View
import android.widget.Toast
import androidx.camera.core.*
import androidx.camera.lifecycle.ProcessCameraProvider
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.magictrick.spectator.databinding.ActivityMainBinding
import java.util.concurrent.ExecutorService
import java.util.concurrent.Executors

class EffectsEngine(
    private val activity: MainActivity,
    private val binding: ActivityMainBinding
) {
    private val context: Context = activity
    private val handler = Handler(Looper.getMainLooper())
    private lateinit var cameraExecutor: ExecutorService

    init {
        cameraExecutor = Executors.newSingleThreadExecutor()
    }

    // Reset to normal
    fun reset() {
        binding.cameraPreview.visibility = View.GONE
        binding.effectOverlay.visibility = View.GONE
        binding.galleryRecyclerView.visibility = View.GONE
        binding.infoScrollView.visibility = View.GONE
        binding.statusIcon.visibility = View.VISIBLE
        binding.statusText.visibility = View.VISIBLE
        binding.rootLayout.setBackgroundColor(Color.parseColor("#6200EA"))
    }

    // Effect 1: Show REAL Camera
    fun showCamera() {
        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.CAMERA)
            != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(context, "Camera permission needed", Toast.LENGTH_SHORT).show()
            return
        }

        hideAll()
        binding.cameraPreview.visibility = View.VISIBLE

        val cameraProviderFuture = ProcessCameraProvider.getInstance(context)
        cameraProviderFuture.addListener({
            val cameraProvider = cameraProviderFuture.get()

            val preview = Preview.Builder().build().also {
                it.setSurfaceProvider(binding.cameraPreview.surfaceProvider)
            }

            val cameraSelector = CameraSelector.DEFAULT_FRONT_CAMERA

            try {
                cameraProvider.unbindAll()
                cameraProvider.bindToLifecycle(
                    activity,
                    cameraSelector,
                    preview
                )

                Toast.makeText(context, "📷 Camera Active", Toast.LENGTH_SHORT).show()

            } catch (exc: Exception) {
                Toast.makeText(context, "Camera failed: ${exc.message}", Toast.LENGTH_SHORT).show()
            }

        }, ContextCompat.getMainExecutor(context))
    }

    // Effect 2: Show REAL Gallery Photos
    fun showGallery() {
        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.READ_MEDIA_IMAGES)
            != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(context, "Gallery permission needed", Toast.LENGTH_SHORT).show()
            return
        }

        hideAll()
        binding.galleryRecyclerView.visibility = View.VISIBLE

        // Get real photos from device
        val photos = getRealPhotos()

        binding.galleryRecyclerView.layoutManager = GridLayoutManager(context, 3)
        binding.galleryRecyclerView.adapter = GalleryAdapter(photos)

        Toast.makeText(context, "📸 ${photos.size} Photos Accessed", Toast.LENGTH_SHORT).show()
    }

    private fun getRealPhotos(): List<Uri> {
        val photos = mutableListOf<Uri>()
        val projection = arrayOf(MediaStore.Images.Media._ID)

        context.contentResolver.query(
            MediaStore.Images.Media.EXTERNAL_CONTENT_URI,
            projection,
            null,
            null,
            "${MediaStore.Images.Media.DATE_ADDED} DESC"
        )?.use { cursor ->
            val idColumn = cursor.getColumnIndexOrThrow(MediaStore.Images.Media._ID)

            var count = 0
            while (cursor.moveToNext() && count < 50) {
                val id = cursor.getLong(idColumn)
                val contentUri = Uri.withAppendedPath(
                    MediaStore.Images.Media.EXTERNAL_CONTENT_URI,
                    id.toString()
                )
                photos.add(contentUri)
                count++
            }
        }

        return photos
    }

    // Effect 3: Show REAL Location
    fun showLocation() {
        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.ACCESS_FINE_LOCATION)
            != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(context, "Location permission needed", Toast.LENGTH_SHORT).show()
            return
        }

        hideAll()
        binding.infoScrollView.visibility = View.VISIBLE

        val locationManager = context.getSystemService(Context.LOCATION_SERVICE) as LocationManager

        try {
            val location = locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER)

            if (location != null) {
                val locationInfo = """
                    📍 LOCATION ACCESSED

                    Latitude: ${location.latitude}
                    Longitude: ${location.longitude}
                    Accuracy: ±${location.accuracy}m

                    Altitude: ${location.altitude}m
                    Speed: ${location.speed} m/s

                    Provider: ${location.provider}
                    Time: ${java.util.Date(location.time)}
                """.trimIndent()

                binding.infoText.text = locationInfo
                Toast.makeText(context, "📍 GPS Location Found!", Toast.LENGTH_SHORT).show()
            } else {
                binding.infoText.text = "📍 No GPS fix yet...\nTrying to acquire location..."
            }

        } catch (e: SecurityException) {
            binding.infoText.text = "❌ Location access denied"
        }
    }

    // Effect 4: Record Audio (Microphone)
    fun recordAudio() {
        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.RECORD_AUDIO)
            != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(context, "Microphone permission needed", Toast.LENGTH_SHORT).show()
            return
        }

        hideAll()
        binding.infoScrollView.visibility = View.VISIBLE
        binding.infoText.text = "🎤 RECORDING AUDIO\n\nMicrophone: ACTIVE\nDuration: 5 seconds\nFormat: PCM\n\nRecording..."

        Toast.makeText(context, "🎤 Recording...", Toast.LENGTH_SHORT).show()

        handler.postDelayed({
            binding.infoText.text = "🎤 RECORDING COMPLETE\n\n✓ Audio captured\n✓ 5 seconds recorded\n✓ Data saved"
            Toast.makeText(context, "✓ Audio saved", Toast.LENGTH_SHORT).show()
        }, 5000)
    }

    // Effect 5: Show REAL Contacts
    fun showContacts() {
        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.READ_CONTACTS)
            != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(context, "Contacts permission needed", Toast.LENGTH_SHORT).show()
            return
        }

        hideAll()
        binding.infoScrollView.visibility = View.VISIBLE

        val contacts = getRealContacts()

        val contactsText = buildString {
            append("📇 CONTACTS ACCESSED\n\n")
            append("Total contacts: ${contacts.size}\n\n")
            contacts.take(20).forEach { (name, number) ->
                append("• $name\n  $number\n\n")
            }
        }

        binding.infoText.text = contactsText
        Toast.makeText(context, "📇 ${contacts.size} Contacts Read", Toast.LENGTH_SHORT).show()
    }

    private fun getRealContacts(): List<Pair<String, String>> {
        val contacts = mutableListOf<Pair<String, String>>()

        context.contentResolver.query(
            ContactsContract.CommonDataKinds.Phone.CONTENT_URI,
            arrayOf(
                ContactsContract.CommonDataKinds.Phone.DISPLAY_NAME,
                ContactsContract.CommonDataKinds.Phone.NUMBER
            ),
            null,
            null,
            null
        )?.use { cursor ->
            val nameColumn = cursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.DISPLAY_NAME)
            val numberColumn = cursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.NUMBER)

            while (cursor.moveToNext()) {
                val name = cursor.getString(nameColumn) ?: "Unknown"
                val number = cursor.getString(numberColumn) ?: "No number"
                contacts.add(Pair(name, number))
            }
        }

        return contacts
    }

    // Effect 6: Vibrate
    fun vibrate() {
        val vibrator = context.getSystemService(Context.VIBRATOR_SERVICE) as Vibrator

        val pattern = longArrayOf(0, 200, 100, 200, 100, 500)
        vibrator.vibrate(VibrationEffect.createWaveform(pattern, -1))

        Toast.makeText(context, "📳 Vibrating...", Toast.LENGTH_SHORT).show()
    }

    // Effect 7: Flashlight
    fun flashlight() {
        val cameraManager = context.getSystemService(Context.CAMERA_SERVICE) as CameraManager

        try {
            val cameraId = cameraManager.cameraIdList[0]

            // Flash 5 times
            repeat(5) { i ->
                handler.postDelayed({
                    cameraManager.setTorchMode(cameraId, true)
                }, (i * 400).toLong())

                handler.postDelayed({
                    cameraManager.setTorchMode(cameraId, false)
                }, (i * 400 + 200).toLong())
            }

            Toast.makeText(context, "🔦 Flashlight Activated", Toast.LENGTH_SHORT).show()

        } catch (e: CameraAccessException) {
            Toast.makeText(context, "Flashlight error", Toast.LENGTH_SHORT).show()
        }
    }

    // Effect 8: Glitch Effect
    fun glitch() {
        hideAll()
        binding.effectOverlay.visibility = View.VISIBLE

        val colors = listOf(Color.RED, Color.GREEN, Color.BLUE, Color.YELLOW, Color.MAGENTA, Color.BLACK, Color.WHITE)

        repeat(30) { i ->
            handler.postDelayed({
                binding.rootLayout.setBackgroundColor(colors.random())
            }, (i * 100).toLong())
        }

        handler.postDelayed({
            reset()
        }, 3000)

        Toast.makeText(context, "📺 Glitch Effect", Toast.LENGTH_SHORT).show()
    }

    // Effect 9: Crash Screen
    fun crash() {
        hideAll()
        binding.rootLayout.setBackgroundColor(Color.BLACK)
        binding.statusIcon.visibility = View.VISIBLE
        binding.statusText.visibility = View.VISIBLE

        binding.statusIcon.text = "⚠️"
        binding.statusText.text = "System UI has stopped"

        Toast.makeText(context, "💥 Fake Crash", Toast.LENGTH_SHORT).show()
    }

    // Effect 10: Spam Notifications
    fun spamNotifications() {
        // Would require NotificationManager - simplified version
        Toast.makeText(context, "🔔 Notifications Triggered", Toast.LENGTH_SHORT).show()
        vibrate()
    }

    // Effect 11: Fake Call
    fun fakeCall() {
        val callIntent = Intent(Intent.ACTION_CALL)
        callIntent.data = Uri.parse("tel:+1234567890")

        Toast.makeText(context, "📞 Fake Call Initiated", Toast.LENGTH_SHORT).show()
    }

    // Effect 12: Fake SMS
    fun fakeSMS() {
        val smsIntent = Intent(Intent.ACTION_VIEW)
        smsIntent.data = Uri.parse("sms:+1234567890")
        smsIntent.putExtra("sms_body", "This is a test message")

        Toast.makeText(context, "📱 SMS Composed", Toast.LENGTH_SHORT).show()
    }

    // Effect 13: Show Battery Info
    fun showBattery() {
        hideAll()
        binding.infoScrollView.visibility = View.VISIBLE

        val batteryManager = context.getSystemService(Context.BATTERY_SERVICE) as BatteryManager
        val batteryLevel = batteryManager.getIntProperty(BatteryManager.BATTERY_PROPERTY_CAPACITY)

        val batteryInfo = """
            🔋 BATTERY INFORMATION

            Level: $batteryLevel%
            Status: Charging
            Health: Good
            Temperature: 25°C
            Voltage: 3.8V
        """.trimIndent()

        binding.infoText.text = batteryInfo
        Toast.makeText(context, "🔋 Battery: $batteryLevel%", Toast.LENGTH_SHORT).show()
    }

    // Effect 14: Scan Bluetooth
    fun scanBluetooth() {
        hideAll()
        binding.infoScrollView.visibility = View.VISIBLE

        binding.infoText.text = """
            📡 BLUETOOTH SCAN

            Scanning for devices...

            Found devices:
            • AirPods Pro (Nearby)
            • Apple Watch (Paired)
            • Unknown Speaker (Available)
        """.trimIndent()

        Toast.makeText(context, "📡 Bluetooth Scan", Toast.LENGTH_SHORT).show()
    }

    private fun hideAll() {
        binding.statusIcon.visibility = View.GONE
        binding.statusText.visibility = View.GONE
        binding.cameraPreview.visibility = View.GONE
        binding.effectOverlay.visibility = View.GONE
        binding.galleryRecyclerView.visibility = View.GONE
        binding.infoScrollView.visibility = View.GONE
    }
}

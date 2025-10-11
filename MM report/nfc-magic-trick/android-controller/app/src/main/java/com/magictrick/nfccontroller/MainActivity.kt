package com.magictrick.nfccontroller

import android.app.PendingIntent
import android.content.Intent
import android.nfc.NdefMessage
import android.nfc.NdefRecord
import android.nfc.NfcAdapter
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.magictrick.nfccontroller.databinding.ActivityMainBinding
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.Response
import okhttp3.WebSocket
import okhttp3.WebSocketListener
import java.util.concurrent.TimeUnit

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private var nfcAdapter: NfcAdapter? = null
    private var webSocket: WebSocket? = null
    private val client = OkHttpClient.Builder()
        .readTimeout(0, TimeUnit.MILLISECONDS)
        .build()

    private var autoModeJob: Job? = null
    private val mainScope = CoroutineScope(Dispatchers.Main + Job())

    // Your Heroku URL will go here
    private var serverUrl = "ws://localhost:3000"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Initialize NFC
        nfcAdapter = NfcAdapter.getDefaultAdapter(this)

        if (nfcAdapter == null) {
            Toast.makeText(this, "NFC not available on this device", Toast.LENGTH_LONG).show()
            binding.nfcStatus.text = "❌ NFC not supported"
        } else if (!nfcAdapter!!.isEnabled) {
            binding.nfcStatus.text = "⚠️ Please enable NFC in settings"
        } else {
            binding.nfcStatus.text = "✅ NFC Ready - Tap phones together to beam URL"
            enableNfcBeam()
        }

        setupListeners()
    }

    private fun setupListeners() {
        binding.connectButton.setOnClickListener {
            serverUrl = binding.serverUrlInput.text.toString()
            connectWebSocket()
        }

        binding.btnGlitch.setOnClickListener { sendEffect("glitch") }
        binding.btnCrash.setOnClickListener { sendEffect("crash") }
        binding.btnFlicker.setOnClickListener { sendEffect("flicker") }
        binding.btnShake.setOnClickListener { sendEffect("shake") }
        binding.btnStatic.setOnClickListener { sendEffect("static") }
        binding.btnMatrix.setOnClickListener { sendEffect("matrix") }
        binding.btnReset.setOnClickListener { sendEffect("reset") }

        binding.autoModeSwitch.setOnCheckedChangeListener { _, isChecked ->
            if (isChecked) {
                startAutoMode()
            } else {
                stopAutoMode()
            }
        }
    }

    private fun enableNfcBeam() {
        val nfcAdapter = this.nfcAdapter ?: return

        // Create the URL that will be beamed
        val url = "https://your-heroku-app.herokuapp.com/magic"

        // Create NDEF message with URL
        val uriRecord = NdefRecord.createUri(url)
        val ndefMessage = NdefMessage(arrayOf(uriRecord))

        // Set the message to be pushed via NFC
        nfcAdapter.setNdefPushMessage(ndefMessage, this)

        // Callback for when NFC push is successful
        nfcAdapter.setOnNdefPushCompleteCallback({ _ ->
            runOnUiThread {
                Toast.makeText(this, "✅ URL sent via NFC!", Toast.LENGTH_SHORT).show()
                // Vibrate to give feedback
                @Suppress("DEPRECATION")
                (getSystemService(VIBRATOR_SERVICE) as? android.os.Vibrator)?.vibrate(200)
            }
        }, this)
    }

    private fun connectWebSocket() {
        webSocket?.close(1000, "Reconnecting")

        binding.statusText.text = "🟡 Connecting..."

        val request = Request.Builder()
            .url(serverUrl)
            .build()

        webSocket = client.newWebSocket(request, object : WebSocketListener() {
            override fun onOpen(webSocket: WebSocket, response: Response) {
                runOnUiThread {
                    binding.statusText.text = "🟢 Connected"
                    binding.statusText.setTextColor(getColor(android.R.color.holo_green_light))
                    Toast.makeText(this@MainActivity, "Connected to server!", Toast.LENGTH_SHORT).show()

                    // Register as controller
                    webSocket.send("""{"type":"register","role":"controller"}""")
                }
            }

            override fun onMessage(webSocket: WebSocket, text: String) {
                runOnUiThread {
                    Toast.makeText(this@MainActivity, "Server: $text", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(webSocket: WebSocket, t: Throwable, response: Response?) {
                runOnUiThread {
                    binding.statusText.text = "🔴 Disconnected"
                    binding.statusText.setTextColor(getColor(android.R.color.holo_red_light))
                    Toast.makeText(this@MainActivity, "Connection failed: ${t.message}", Toast.LENGTH_LONG).show()
                }
            }

            override fun onClosing(webSocket: WebSocket, code: Int, reason: String) {
                webSocket.close(1000, null)
                runOnUiThread {
                    binding.statusText.text = "⚪ Disconnected"
                }
            }
        })
    }

    private fun sendEffect(effectName: String) {
        val message = """{"type":"effect","effect":"$effectName"}"""
        val sent = webSocket?.send(message) ?: false

        if (sent) {
            Toast.makeText(this, "Sent: $effectName", Toast.LENGTH_SHORT).show()
        } else {
            Toast.makeText(this, "Not connected to server!", Toast.LENGTH_SHORT).show()
        }
    }

    private fun startAutoMode() {
        val effects = listOf("glitch", "flicker", "shake", "static", "matrix", "crash")

        autoModeJob = mainScope.launch {
            while (true) {
                val randomEffect = effects.random()
                sendEffect(randomEffect)
                delay((2000..5000).random().toLong()) // Random delay between 2-5 seconds
            }
        }

        Toast.makeText(this, "🤖 Auto mode started!", Toast.LENGTH_SHORT).show()
    }

    private fun stopAutoMode() {
        autoModeJob?.cancel()
        autoModeJob = null
        sendEffect("reset")
        Toast.makeText(this, "Auto mode stopped", Toast.LENGTH_SHORT).show()
    }

    override fun onResume() {
        super.onResume()

        // Enable NFC foreground dispatch
        nfcAdapter?.let { adapter ->
            val intent = Intent(this, javaClass).apply {
                addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP)
            }
            val pendingIntent = PendingIntent.getActivity(
                this, 0, intent,
                PendingIntent.FLAG_MUTABLE
            )
            adapter.enableForegroundDispatch(this, pendingIntent, null, null)
        }
    }

    override fun onPause() {
        super.onPause()
        nfcAdapter?.disableForegroundDispatch(this)
    }

    override fun onDestroy() {
        super.onDestroy()
        webSocket?.close(1000, "App closing")
        autoModeJob?.cancel()
    }
}

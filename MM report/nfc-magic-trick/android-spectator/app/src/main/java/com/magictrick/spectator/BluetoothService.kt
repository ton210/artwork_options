package com.magictrick.spectator

import android.app.Service
import android.bluetooth.BluetoothAdapter
import android.bluetooth.BluetoothManager
import android.bluetooth.BluetoothServerSocket
import android.bluetooth.BluetoothSocket
import android.content.Intent
import android.os.IBinder
import android.util.Log
import java.io.IOException
import java.io.InputStream
import java.util.UUID

class BluetoothService : Service() {

    companion object {
        private const val TAG = "BluetoothService"
        private const val NAME = "MagicTrickBT"
        private val MY_UUID: UUID = UUID.fromString("00001101-0000-1000-8000-00805F9B34FB")

        private var effectListener: ((String) -> Unit)? = null

        fun setEffectListener(listener: (String) -> Unit) {
            effectListener = listener
        }
    }

    private var bluetoothAdapter: BluetoothAdapter? = null
    private var acceptThread: AcceptThread? = null

    override fun onCreate() {
        super.onCreate()

        val bluetoothManager = getSystemService(BLUETOOTH_SERVICE) as BluetoothManager
        bluetoothAdapter = bluetoothManager.adapter

        startServer()
    }

    private fun startServer() {
        acceptThread = AcceptThread()
        acceptThread?.start()
    }

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onDestroy() {
        super.onDestroy()
        acceptThread?.cancel()
    }

    private inner class AcceptThread : Thread() {
        private val serverSocket: BluetoothServerSocket? by lazy(LazyThreadSafetyMode.NONE) {
            try {
                bluetoothAdapter?.listenUsingRfcommWithServiceRecord(NAME, MY_UUID)
            } catch (e: SecurityException) {
                Log.e(TAG, "Security exception", e)
                null
            }
        }

        override fun run() {
            var socket: BluetoothSocket? = null

            while (true) {
                socket = try {
                    serverSocket?.accept()
                } catch (e: IOException) {
                    Log.e(TAG, "Socket accept() failed", e)
                    break
                }

                socket?.also {
                    Log.d(TAG, "Bluetooth connection accepted")
                    manageConnectedSocket(it)
                    try {
                        serverSocket?.close()
                    } catch (e: IOException) {
                        Log.e(TAG, "Could not close socket", e)
                    }
                }
            }
        }

        fun cancel() {
            try {
                serverSocket?.close()
            } catch (e: IOException) {
                Log.e(TAG, "Could not close the connect socket", e)
            }
        }
    }

    private fun manageConnectedSocket(socket: BluetoothSocket) {
        Thread {
            val buffer = ByteArray(1024)
            val inputStream: InputStream = socket.inputStream

            while (true) {
                try {
                    val bytes = inputStream.read(buffer)
                    val command = String(buffer, 0, bytes).trim()

                    Log.d(TAG, "Received command: $command")

                    // Trigger effect
                    effectListener?.invoke(command)

                } catch (e: IOException) {
                    Log.d(TAG, "Input stream was disconnected", e)
                    break
                }
            }
        }.start()
    }
}

package com.focusblock.app.ui.screens

import android.app.AppOpsManager
import android.content.Context
import android.content.Intent
import android.os.Process
import android.provider.Settings
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import com.focusblock.app.data.database.AppDatabase
import com.focusblock.app.data.model.BlockedApp
import com.focusblock.app.service.AppBlockingService
import com.focusblock.app.util.PreferenceManager
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen() {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val database = remember { AppDatabase.getDatabase(context) }
    val prefManager = remember { PreferenceManager(context) }

    val blockedApps by database.blockedAppDao().getAllBlockedApps().collectAsState(initial = emptyList())
    val quickBlockEnabled by prefManager.quickBlockEnabledFlow.collectAsState(initial = false)
    val strictModeEnabled by prefManager.strictModeEnabledFlow.collectAsState(initial = false)

    var showAppPicker by remember { mutableStateOf(false) }
    var hasUsageStatsPermission by remember { mutableStateOf(checkUsageStatsPermission(context)) }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp)
    ) {
        // Quick Block Section
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(bottom = 16.dp)
        ) {
            Column(modifier = Modifier.padding(16.dp)) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = "Quick Block",
                        style = MaterialTheme.typography.titleLarge
                    )
                    Switch(
                        checked = quickBlockEnabled,
                        onCheckedChange = { enabled ->
                            scope.launch {
                                prefManager.setQuickBlockEnabled(enabled)
                                if (enabled && hasUsageStatsPermission) {
                                    startBlockingService(context)
                                } else if (!enabled) {
                                    stopBlockingService(context)
                                }
                            }
                        }
                    )
                }

                if (quickBlockEnabled) {
                    Text(
                        text = "Blocking is active",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.padding(top = 8.dp)
                    )
                }
            }
        }

        // Strict Mode Section
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(bottom = 16.dp)
        ) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = "Strict Mode",
                        style = MaterialTheme.typography.titleMedium
                    )
                    Text(
                        text = "Prevent bypassing blocks",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                Switch(
                    checked = strictModeEnabled,
                    onCheckedChange = { enabled ->
                        scope.launch {
                            prefManager.setStrictModeEnabled(enabled)
                        }
                    }
                )
            }
        }

        // Permissions Check
        if (!hasUsageStatsPermission) {
            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 16.dp)
                    .clickable {
                        val intent = Intent(Settings.ACTION_USAGE_ACCESS_SETTINGS)
                        context.startActivity(intent)
                    },
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.errorContainer
                )
            ) {
                Text(
                    text = "⚠️ Usage Stats Permission Required\nTap to grant permission",
                    modifier = Modifier.padding(16.dp),
                    color = MaterialTheme.colorScheme.onErrorContainer
                )
            }
        }

        // Blocked Apps List
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(bottom = 8.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Blocked Apps (${blockedApps.size})",
                style = MaterialTheme.typography.titleMedium
            )
            IconButton(onClick = { showAppPicker = true }) {
                Icon(Icons.Default.Add, contentDescription = "Add app")
            }
        }

        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            items(blockedApps, key = { it.id }) { app ->
                Card(
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(16.dp),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Column(modifier = Modifier.weight(1f)) {
                            Text(
                                text = app.appName,
                                style = MaterialTheme.typography.bodyLarge
                            )
                            Text(
                                text = app.packageName,
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                        IconButton(
                            onClick = {
                                scope.launch {
                                    database.blockedAppDao().delete(app)
                                }
                            }
                        ) {
                            Icon(
                                Icons.Default.Delete,
                                contentDescription = "Remove",
                                tint = MaterialTheme.colorScheme.error
                            )
                        }
                    }
                }
            }
        }
    }

    if (showAppPicker) {
        AppPickerDialog(
            onDismiss = { showAppPicker = false },
            onAppSelected = { packageName, appName ->
                scope.launch {
                    database.blockedAppDao().insert(
                        BlockedApp(
                            packageName = packageName,
                            appName = appName
                        )
                    )
                    showAppPicker = false
                }
            }
        )
    }
}

@Composable
fun AppPickerDialog(
    onDismiss: () -> Unit,
    onAppSelected: (String, String) -> Unit
) {
    val context = LocalContext.current
    val installedApps = remember {
        getInstalledApps(context)
    }

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Select App to Block") },
        text = {
            LazyColumn {
                items(installedApps) { (packageName, appName) ->
                    Text(
                        text = appName,
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable {
                                onAppSelected(packageName, appName)
                            }
                            .padding(16.dp)
                    )
                }
            }
        },
        confirmButton = {
            TextButton(onClick = onDismiss) {
                Text("Cancel")
            }
        }
    )
}

private fun getInstalledApps(context: Context): List<Pair<String, String>> {
    val pm = context.packageManager
    val apps = pm.getInstalledApplications(0)

    return apps
        .filter { it.packageName != context.packageName }
        .mapNotNull { appInfo ->
            try {
                val appName = pm.getApplicationLabel(appInfo).toString()
                Pair(appInfo.packageName, appName)
            } catch (e: Exception) {
                null
            }
        }
        .sortedBy { it.second }
}

private fun checkUsageStatsPermission(context: Context): Boolean {
    val appOps = context.getSystemService(Context.APP_OPS_SERVICE) as AppOpsManager
    val mode = appOps.checkOpNoThrow(
        AppOpsManager.OPSTR_GET_USAGE_STATS,
        Process.myUid(),
        context.packageName
    )
    return mode == AppOpsManager.MODE_ALLOWED
}

private fun startBlockingService(context: Context) {
    val intent = Intent(context, AppBlockingService::class.java)
    context.startForegroundService(intent)
}

private fun stopBlockingService(context: Context) {
    val intent = Intent(context, AppBlockingService::class.java)
    context.stopService(intent)
}

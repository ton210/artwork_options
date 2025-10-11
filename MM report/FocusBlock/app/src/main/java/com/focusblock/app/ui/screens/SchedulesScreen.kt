package com.focusblock.app.ui.screens

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
import com.focusblock.app.data.model.BlockSchedule
import com.focusblock.app.data.model.ScheduleType
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SchedulesScreen() {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val database = remember { AppDatabase.getDatabase(context) }

    val schedules by database.blockScheduleDao().getAllSchedules().collectAsState(initial = emptyList())
    var showCreateDialog by remember { mutableStateOf(false) }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(bottom = 16.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Schedules",
                style = MaterialTheme.typography.headlineMedium
            )
            IconButton(onClick = { showCreateDialog = true }) {
                Icon(Icons.Default.Add, contentDescription = "Add schedule")
            }
        }

        if (schedules.isEmpty()) {
            Box(
                modifier = Modifier.fillMaxSize(),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = "No schedules yet\nTap + to create one",
                    style = MaterialTheme.typography.bodyLarge,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        } else {
            LazyColumn(
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                items(schedules, key = { it.id }) { schedule ->
                    ScheduleCard(
                        schedule = schedule,
                        onToggle = { enabled ->
                            scope.launch {
                                database.blockScheduleDao().update(
                                    schedule.copy(isEnabled = enabled)
                                )
                            }
                        },
                        onDelete = {
                            scope.launch {
                                database.blockScheduleDao().delete(schedule)
                            }
                        }
                    )
                }
            }
        }
    }

    if (showCreateDialog) {
        CreateScheduleDialog(
            onDismiss = { showCreateDialog = false },
            onCreate = { schedule ->
                scope.launch {
                    database.blockScheduleDao().insert(schedule)
                    showCreateDialog = false
                }
            }
        )
    }
}

@Composable
fun ScheduleCard(
    schedule: BlockSchedule,
    onToggle: (Boolean) -> Unit,
    onDelete: () -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(
            modifier = Modifier.padding(16.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = schedule.name,
                        style = MaterialTheme.typography.titleMedium
                    )
                    Text(
                        text = getScheduleTypeLabel(schedule),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Switch(
                        checked = schedule.isEnabled,
                        onCheckedChange = onToggle
                    )
                    IconButton(onClick = onDelete) {
                        Icon(
                            Icons.Default.Delete,
                            contentDescription = "Delete",
                            tint = MaterialTheme.colorScheme.error
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun CreateScheduleDialog(
    onDismiss: () -> Unit,
    onCreate: (BlockSchedule) -> Unit
) {
    var name by remember { mutableStateOf("") }
    var selectedType by remember { mutableStateOf(ScheduleType.TIME_BASED) }
    var startTime by remember { mutableStateOf("09:00") }
    var endTime by remember { mutableStateOf("17:00") }

    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Create Schedule") },
        text = {
            Column {
                TextField(
                    value = name,
                    onValueChange = { name = it },
                    label = { Text("Schedule Name") },
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(bottom = 16.dp)
                )

                Text("Schedule Type", style = MaterialTheme.typography.labelMedium)
                Column {
                    ScheduleType.values().forEach { type ->
                        Row(
                            modifier = Modifier
                                .fillMaxWidth()
                                .clickable { selectedType = type }
                                .padding(vertical = 8.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            RadioButton(
                                selected = selectedType == type,
                                onClick = { selectedType = type }
                            )
                            Text(
                                text = when (type) {
                                    ScheduleType.TIME_BASED -> "Time-based"
                                    ScheduleType.LOCATION_BASED -> "Location-based"
                                    ScheduleType.USAGE_LIMIT -> "Usage limit"
                                },
                                modifier = Modifier.padding(start = 8.dp)
                            )
                        }
                    }
                }

                if (selectedType == ScheduleType.TIME_BASED) {
                    TextField(
                        value = startTime,
                        onValueChange = { startTime = it },
                        label = { Text("Start Time (HH:mm)") },
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(top = 8.dp)
                    )
                    TextField(
                        value = endTime,
                        onValueChange = { endTime = it },
                        label = { Text("End Time (HH:mm)") },
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(top = 8.dp)
                    )
                }
            }
        },
        confirmButton = {
            TextButton(
                onClick = {
                    if (name.isNotBlank()) {
                        onCreate(
                            BlockSchedule(
                                name = name,
                                type = selectedType,
                                startTime = if (selectedType == ScheduleType.TIME_BASED) startTime else null,
                                endTime = if (selectedType == ScheduleType.TIME_BASED) endTime else null
                            )
                        )
                    }
                }
            ) {
                Text("Create")
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("Cancel")
            }
        }
    )
}

private fun getScheduleTypeLabel(schedule: BlockSchedule): String {
    return when (schedule.type) {
        ScheduleType.TIME_BASED -> {
            if (schedule.startTime != null && schedule.endTime != null) {
                "Time: ${schedule.startTime} - ${schedule.endTime}"
            } else {
                "Time-based"
            }
        }
        ScheduleType.LOCATION_BASED -> "Location-based"
        ScheduleType.USAGE_LIMIT -> "Usage limit: ${schedule.dailyLimitMinutes ?: 0} minutes"
    }
}

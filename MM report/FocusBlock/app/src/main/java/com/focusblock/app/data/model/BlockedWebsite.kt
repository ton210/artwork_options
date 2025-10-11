package com.focusblock.app.data.model

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "blocked_websites")
data class BlockedWebsite(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val url: String,
    val isBlocked: Boolean = true,
    val createdAt: Long = System.currentTimeMillis()
)

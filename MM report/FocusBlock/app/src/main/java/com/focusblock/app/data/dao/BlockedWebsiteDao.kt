package com.focusblock.app.data.dao

import androidx.room.*
import com.focusblock.app.data.model.BlockedWebsite
import kotlinx.coroutines.flow.Flow

@Dao
interface BlockedWebsiteDao {
    @Query("SELECT * FROM blocked_websites ORDER BY url ASC")
    fun getAllBlockedWebsites(): Flow<List<BlockedWebsite>>

    @Query("SELECT * FROM blocked_websites WHERE isBlocked = 1")
    fun getActiveBlockedWebsites(): Flow<List<BlockedWebsite>>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(website: BlockedWebsite): Long

    @Update
    suspend fun update(website: BlockedWebsite)

    @Delete
    suspend fun delete(website: BlockedWebsite)

    @Query("DELETE FROM blocked_websites WHERE id = :id")
    suspend fun deleteById(id: Long)
}

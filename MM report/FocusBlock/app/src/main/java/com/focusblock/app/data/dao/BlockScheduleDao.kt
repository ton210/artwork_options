package com.focusblock.app.data.dao

import androidx.room.*
import com.focusblock.app.data.model.BlockSchedule
import kotlinx.coroutines.flow.Flow

@Dao
interface BlockScheduleDao {
    @Query("SELECT * FROM block_schedules ORDER BY name ASC")
    fun getAllSchedules(): Flow<List<BlockSchedule>>

    @Query("SELECT * FROM block_schedules WHERE isEnabled = 1")
    fun getActiveSchedules(): Flow<List<BlockSchedule>>

    @Query("SELECT * FROM block_schedules WHERE id = :id")
    suspend fun getScheduleById(id: Long): BlockSchedule?

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(schedule: BlockSchedule): Long

    @Update
    suspend fun update(schedule: BlockSchedule)

    @Delete
    suspend fun delete(schedule: BlockSchedule)

    @Query("DELETE FROM block_schedules WHERE id = :id")
    suspend fun deleteById(id: Long)
}

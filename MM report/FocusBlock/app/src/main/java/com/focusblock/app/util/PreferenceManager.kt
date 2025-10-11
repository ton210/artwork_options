package com.focusblock.app.util

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.booleanPreferencesKey
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.longPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "settings")

class PreferenceManager(private val context: Context) {

    companion object {
        val QUICK_BLOCK_ENABLED = booleanPreferencesKey("quick_block_enabled")
        val STRICT_MODE_ENABLED = booleanPreferencesKey("strict_mode_enabled")
        val QUICK_BLOCK_TIMER = longPreferencesKey("quick_block_timer")
    }

    val quickBlockEnabledFlow: Flow<Boolean> = context.dataStore.data.map { preferences ->
        preferences[QUICK_BLOCK_ENABLED] ?: false
    }

    val strictModeEnabledFlow: Flow<Boolean> = context.dataStore.data.map { preferences ->
        preferences[STRICT_MODE_ENABLED] ?: false
    }

    suspend fun setQuickBlockEnabled(enabled: Boolean) {
        context.dataStore.edit { preferences ->
            preferences[QUICK_BLOCK_ENABLED] = enabled
        }
    }

    suspend fun setStrictModeEnabled(enabled: Boolean) {
        context.dataStore.edit { preferences ->
            preferences[STRICT_MODE_ENABLED] = enabled
        }
    }

    suspend fun setQuickBlockTimer(endTime: Long) {
        context.dataStore.edit { preferences ->
            preferences[QUICK_BLOCK_TIMER] = endTime
        }
    }

    suspend fun isQuickBlockEnabled(): Boolean {
        return quickBlockEnabledFlow.first()
    }

    suspend fun isStrictMode(): Boolean {
        return strictModeEnabledFlow.first()
    }

    suspend fun getQuickBlockTimer(): Long {
        return context.dataStore.data.map { preferences ->
            preferences[QUICK_BLOCK_TIMER] ?: 0L
        }.first()
    }
}

/**
 * Calculate if a dispensary is currently open based on hours data
 * @param {Object} hoursData - Hours object from Google Places API
 * @param {string} timezone - Timezone (default: 'America/Los_Angeles')
 * @returns {boolean|null} - true if open, false if closed, null if unknown
 */
function isCurrentlyOpen(hoursData, timezone = 'America/Los_Angeles') {
  if (!hoursData || typeof hoursData !== 'object') {
    return null;
  }

  // If periods exist, use them for accurate calculation
  if (hoursData.periods && Array.isArray(hoursData.periods)) {
    return isOpenFromPeriods(hoursData.periods, timezone);
  }

  // Fallback to weekday_text parsing (less accurate)
  if (hoursData.weekday_text && Array.isArray(hoursData.weekday_text)) {
    return isOpenFromWeekdayText(hoursData.weekday_text, timezone);
  }

  // If no usable data, return null
  return null;
}

function isOpenFromPeriods(periods, timezone) {
  try {
    // Get current time in the dispensary's timezone
    const now = new Date();
    const currentDay = now.getDay(); // 0 = Sunday, 1 = Monday, etc.
    const currentTime = now.getHours() * 100 + now.getMinutes(); // e.g., 1430 for 2:30 PM

    // Find today's periods
    const todaysPeriods = periods.filter(period => period.open && period.open.day === currentDay);

    if (todaysPeriods.length === 0) {
      // No periods for today = closed
      return false;
    }

    // Check if current time falls within any open period
    for (const period of todaysPeriods) {
      const openTime = parseInt(period.open.time); // e.g., "0900" -> 900
      const closeTime = period.close ? parseInt(period.close.time) : 2359;

      // Handle overnight periods (close time on next day)
      if (period.close && period.close.day !== currentDay) {
        // If close is on next day, we're open until midnight
        if (currentTime >= openTime) {
          return true;
        }
      } else {
        // Normal same-day period
        if (currentTime >= openTime && currentTime < closeTime) {
          return true;
        }
      }
    }

    return false;
  } catch (error) {
    console.error('Error calculating open status from periods:', error);
    return null;
  }
}

function isOpenFromWeekdayText(weekdayText, timezone) {
  try {
    // Get current day name
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const now = new Date();
    const currentDay = days[now.getDay()];
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();

    // Find today's hours text (e.g., "Monday: 9:00 AM – 9:00 PM")
    const todayText = weekdayText.find(text => text.startsWith(currentDay));

    if (!todayText) {
      return null;
    }

    // Check if closed
    if (todayText.includes('Closed') || todayText.includes('closed')) {
      return false;
    }

    // Parse hours (basic regex matching)
    // Format examples: "9:00 AM – 9:00 PM", "9:00 AM – 12:00 AM", "Open 24 hours"
    if (todayText.includes('Open 24 hours') || todayText.includes('24 hours')) {
      return true;
    }

    // Try to parse time ranges
    const timeRangeMatch = todayText.match(/(\d{1,2}):(\d{2})\s*(AM|PM)\s*[–-]\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i);

    if (!timeRangeMatch) {
      // Can't parse, return null
      return null;
    }

    const [_, openHour, openMin, openPeriod, closeHour, closeMin, closePeriod] = timeRangeMatch;

    // Convert to 24-hour format
    let openHour24 = parseInt(openHour);
    if (openPeriod.toUpperCase() === 'PM' && openHour24 !== 12) openHour24 += 12;
    if (openPeriod.toUpperCase() === 'AM' && openHour24 === 12) openHour24 = 0;

    let closeHour24 = parseInt(closeHour);
    if (closePeriod.toUpperCase() === 'PM' && closeHour24 !== 12) closeHour24 += 12;
    if (closePeriod.toUpperCase() === 'AM' && closeHour24 === 12) closeHour24 = 0;

    const openTimeMinutes = openHour24 * 60 + parseInt(openMin);
    const closeTimeMinutes = closeHour24 * 60 + parseInt(closeMin);
    const currentTimeMinutes = currentHour * 60 + currentMinute;

    // Handle overnight hours (e.g., 9 AM - 2 AM)
    if (closeTimeMinutes < openTimeMinutes) {
      // Overnight: open if after open time OR before close time
      return currentTimeMinutes >= openTimeMinutes || currentTimeMinutes < closeTimeMinutes;
    } else {
      // Same day: open if between open and close
      return currentTimeMinutes >= openTimeMinutes && currentTimeMinutes < closeTimeMinutes;
    }

  } catch (error) {
    console.error('Error calculating open status from weekday text:', error);
    return null;
  }
}

module.exports = {
  isCurrentlyOpen,
  isOpenFromPeriods,
  isOpenFromWeekdayText
};

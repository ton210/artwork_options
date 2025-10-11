/**
 * DesignAnalytics Helper Class
 */
class DesignAnalytics {
  constructor() {
    this.events = [];
    this.sessionStart = Date.now();
  }

  track(eventType, data = {}) {
    const event = {
      type: eventType,
      timestamp: Date.now(),
      sessionDuration: Date.now() - this.sessionStart,
      data: data
    };

    this.events.push(event);

    // Send to analytics service if available
    if (window.gtag) {
      window.gtag('event', eventType, {
        event_category: 'Designer',
        event_label: JSON.stringify(data)
      });
    }
  }

  getDesignComplexity(canvas) {
    const objects = canvas.getObjects();
    return {
      totalObjects: objects.length,
      objectTypes: this.countObjectTypes(objects)
    };
  }

  countObjectTypes(objects) {
    const types = {};
    objects.forEach(obj => {
      types[obj.type] = (types[obj.type] || 0) + 1;
    });
    return types;
  }
}

export { DesignAnalytics };

// Predefined colors for standard event types
const standardColors = {
  'Wedding': {
    background: '#ffebee',
    color: '#d32f2f'
  },
  'Debut': {
    background: '#fff3e0',
    color: '#ef6c00'
  },
  'Christening': {
    background: '#e3f2fd',
    color: '#1565c0'
  },
  'Party': {
    background: '#f3e5f5',
    color: '#7b1fa2'
  },
  'Platinum': {
    background: '#f3e5f5',
    color: '#6a1b9a'
  },
  'Gold': {
    background: '#fff8e1',
    color: '#ffa000'
  },
  'Silver': {
    background: '#eceff1',
    color: '#546e7a'
  },
  'Bronze': {
    background: '#efebe9',
    color: '#795548'
  }
};

// Generate a consistent color for any event type
export const getEventTypeStyle = (eventType) => {
  // Return predefined colors if they exist
  if (standardColors[eventType]) {
    return standardColors[eventType];
  }

  // Generate a consistent color for custom event types
  const hash = eventType.split('').reduce((acc, char) => {
    return char.charCodeAt(0) + ((acc << 5) - acc);
  }, 0);

  // Generate HSL color with pastel-like saturation and lightness
  const hue = Math.abs(hash % 360);
  const background = `hsl(${hue}, 85%, 95%)`; // Lighter background
  const color = `hsl(${hue}, 85%, 30%)`; // Darker text for contrast

  return { background, color };
};

// Export standard colors for reference
export const eventTypeColors = standardColors; 
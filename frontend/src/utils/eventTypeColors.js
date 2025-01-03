// Predefined colors for standard event types
const standardEventColors = {
  wedding: {
    background: '#ffebee',
    color: '#c62828'
  },
  debut: {
    background: '#fff3e0',
    color: '#ef6c00'
  },
  christening: {
    background: '#e3f2fd',
    color: '#1565c0'
  },
  party: {
    background: '#fce4ec',
    color: '#c2185b'
  }
};

// Generate consistent colors for custom event types
const generateEventTypeColor = (eventType) => {
  let hash = 0;
  for (let i = 0; i < eventType.length; i++) {
    hash = eventType.charCodeAt(i) + ((hash << 5) - hash);
  }
  
  // Generate pastel colors for better visibility
  const h = Math.abs(hash) % 360; // Hue
  const s = 25 + (Math.abs(hash) % 30); // Saturation between 25-55%
  const l = 85 + (Math.abs(hash) % 10); // Lightness between 85-95%
  
  return {
    background: `hsl(${h}, ${s}%, ${l}%)`,
    color: `hsl(${h}, ${s + 20}%, 25%)` // Darker text color
  };
};

// Get colors for any event type
const getEventTypeColors = (eventType) => {
  if (!eventType) return standardEventColors.other;
  
  const normalizedType = eventType.toLowerCase();
  return standardEventColors[normalizedType] || generateEventTypeColor(eventType);
};

export { standardEventColors, generateEventTypeColor, getEventTypeColors }; 
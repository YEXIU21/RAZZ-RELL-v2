const checkEventAvailability = async (date, eventType, packageId) => {
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/events/availability`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ date, eventType, packageId })
    });
    
    const data = await response.json();
    return data.available;
  } catch (error) {
    console.error('Error checking availability:', error);
    throw error;
  }
};

const getBlockedDates = async (eventType, packageId) => {
  try {
    const response = await fetch(
      `${import.meta.env.VITE_API_URL}/api/events/blocked-dates?eventType=${eventType}&packageId=${packageId}`
    );
    
    const data = await response.json();
    return data.blockedDates;
  } catch (error) {
    console.error('Error fetching blocked dates:', error);
    throw error;
  }
};

export { checkEventAvailability, getBlockedDates }; 
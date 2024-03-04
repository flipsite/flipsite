ready(() => {
  // Function to update timer values
  function updateTimerElements() {
    // Find all elements with data-timer attribute
    const timerElements = document.querySelectorAll('[data-timer]');

    // Loop through each timer element
    timerElements.forEach(timerElement => {
        // Get the timestamp values from data-timer attribute and sort them
        const timestampsJson = timerElement.getAttribute('data-timer');
        const timestamps = JSON.parse(timestampsJson).map(timestamp => new Date(timestamp).getTime()).sort((a, b) => a - b);

        // Find the next timestamp
        const nextTimestamp = timestamps.find(timestamp => timestamp > new Date().getTime());

        // Calculate the time difference in milliseconds
        const difference = nextTimestamp - new Date().getTime();

        // Calculate days, hours, minutes, and seconds
        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);

        // Find and update child elements with data-timer-days, data-timer-hours, data-timer-minutes, and data-timer-seconds
        const daysElement = timerElement.querySelector('[data-timer-days]');
        const hoursElement = timerElement.querySelector('[data-timer-hours]');
        const minutesElement = timerElement.querySelector('[data-timer-minutes]');
        const secondsElement = timerElement.querySelector('[data-timer-seconds]');

        // Update elements without labels
        if (daysElement) daysElement.textContent = days.toString().padStart(2, '0');
        if (hoursElement) hoursElement.textContent = hours.toString().padStart(2, '0');
        if (minutesElement) minutesElement.textContent = minutes.toString().padStart(2, '0');
        if (secondsElement) secondsElement.textContent = seconds.toString().padStart(2, '0');
    });
}

// Update timer every second
setInterval(updateTimerElements, 1000);

// Initial call to set values on page load
updateTimerElements();
});
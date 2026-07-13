const container = document.getElementById("myEventsContainer");
let currentType = 'joined';  // Track current tab
let allEventsCache = [];     // Cache for events data

async function loadMyEvents(type = 'joined') {

    try {

        const url = `../backend/api/my_events.php?type=${type}`;
        const response = await fetch(url);
        const data = await response.json();

        if (!data.success) {

            container.innerHTML = `<p>${data.message}</p>`;
            return;

        }

        if (data.data.length === 0) {

            const message = type === 'created' 
                ? "You haven't created any events yet."
                : "You haven't joined any events yet.";

            container.innerHTML = `
                <p style="text-align:center;">${message}</p>
            `;

            return;

        }

        container.innerHTML = "";
        allEventsCache = data.data;  // Store events in cache

        data.data.forEach(event => {

            // Get status label and color
            const statusInfo = getStatusInfo(event.status);

            // Show original date if postponed
            let dateDisplay = event.event_date;
            if (event.status === 'postponed' && event.original_date) {
                dateDisplay = `<span class="postponed-date">${event.original_date}</span> <span class="postponed-arrow">→</span> <span class="postponed-new-date">${event.event_date}</span>`;
            }

            // Check if current user is the creator
            const isCreator = event.is_creator === true || event.is_creator === 1;

            // Build action buttons based on tab and creator status
            let actionButtons = '';

            if (type === 'created') {
                // Created tab: always show Update + Delete (these are events the user created)
                actionButtons = `
                    <button onclick="updateEvent(${event.id})">Update</button>
                    <button class="btn-danger" onclick="deleteEvent(${event.id})">Delete</button>
                `;
            } else {
                // Joined tab: check if user is the creator
                if (isCreator) {
                    // Creator sees "You are the organizer" badge
                    actionButtons = `<span class="organizer-badge">You are the organizer</span>`;
                } else {
                    // Participants see Cancel Registration
                    actionButtons = `<button class="btn-danger" onclick="cancelRegistration(${event.id})">Cancel Registration</button>`;
                }
            }

            container.innerHTML += `

                <div class="event-card">

                    <div class="event-content">

                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <h3>${event.title}</h3>
                            <span class="status-badge ${statusInfo.class}">${statusInfo.label}</span>
                        </div>

                        <p><strong>Date:</strong> ${dateDisplay}</p>

                        <p><strong>Location:</strong> ${event.location ?? ""}</p>

                        <p>${event.description ?? ""}</p>

                        <div style="margin-top:10px;">
                            ${actionButtons}
                        </div>

                    </div>

                </div>

            `;

        });

    } catch (error) {

        console.error(error);

        container.innerHTML = "<p>Server Error</p>";

    }

}

function getStatusInfo(status) {
    const statusMap = {
        'upcoming': { label: 'Upcoming', class: 'status-upcoming' },
        'ongoing': { label: 'Ongoing', class: 'status-ongoing' },
        'cancelled': { label: 'Cancelled', class: 'status-cancelled' },
        'completed': { label: 'Completed', class: 'status-completed' },
        'postponed': { label: 'Postponed', class: 'status-postponed' }
    };
    return statusMap[status] || { label: status || 'Upcoming', class: 'status-upcoming' };
}

function switchTab(type) {
    currentType = type;

    // Update active tab styling
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.type === type) {
            btn.classList.add('active');
        }
    });

    loadMyEvents(type);
}

async function cancelRegistration(eventId) {

    const formData = new FormData();

    formData.append("event_id", eventId);

    try {

        const response = await fetch("../backend/api/cancel_registration.php", {

            method: "POST",
            body: formData

        });

        const data = await response.json();

        alert(data.message);

        if (data.success) {

            loadMyEvents(currentType);

        }

    } catch (error) {

        console.error(error);

    }

}

async function deleteEvent(eventId) {

    if (!confirm("Are you sure you want to delete this event?")) {
        return;
    }

    const formData = new FormData();
    formData.append("event_id", eventId);

    try {

        const response = await fetch("../backend/api/delete_event.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        alert(data.message);

        if (data.success) {
            loadMyEvents(currentType);
        }

    } catch (error) {

        console.error(error);
        alert("Error deleting event");

    }

}

async function updateEvent(eventId) {

    // Find the event from cache using eventId
    const event = allEventsCache.find(e => e.id === eventId);

    if (!event) {
        alert("Event not found. Please refresh the page.");
        return;
    }

    // Prompt for update type
    const actionInput = prompt(
        "Update Action (enter number):\n" +
        "1: Update Event Details\n" +
        "2: Postpone Event\n\n" +
        "Current Status: " + (event.status || 'upcoming'),
        "1"
    );
    if (actionInput === null) return;

    const action = actionInput === '2' ? 'postpone' : 'update';

    const title = prompt("Event Title:", event.title);
    if (title === null) return;

    const description = prompt("Description:", event.description);
    if (description === null) return;

    const event_date = prompt("Event Date (YYYY-MM-DD):", event.event_date);
    if (event_date === null) return;

    const location = prompt("Location:", event.location);
    if (location === null) return;

    const formData = new FormData();

    formData.append("event_id", event.id);
    formData.append("title", title);
    formData.append("description", description);
    formData.append("event_date", event_date);
    formData.append("location", location);
    formData.append("action", action);

    // If normal update, include status
    if (action === 'update') {
        const statusOptions = ['upcoming', 'ongoing', 'cancelled', 'completed'];
        const statusInput = prompt(
            "Status (enter number):\n" +
            "0: Upcoming\n" +
            "1: Ongoing\n" +
            "2: Cancelled\n" +
            "3: Completed\n\n" +
            "Current: " + (event.status || 'upcoming'),
            "0"
        );
        if (statusInput === null) return;

        let status = 'upcoming';
        if (statusInput >= 0 && statusInput <= 3) {
            status = statusOptions[parseInt(statusInput)];
        }
        formData.append("status", status);
    }

    try {

        const response = await fetch("../backend/api/update_event.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        alert(data.message);

        if (data.success) {
            loadMyEvents(currentType);
        }

    } catch (error) {

        console.error(error);
        alert("Server Error");

    }

}

// Load default tab on page load
loadMyEvents('joined');
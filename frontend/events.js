const eventsContainer = document.getElementById("eventsContainer");
let allEventsCache = [];

async function loadEvents() {

    try {

        const response = await fetch("../backend/api/events.php");
        const data = await response.json();

        if (!data.success) {

            eventsContainer.innerHTML = `
                <p style="text-align:center;color:red;">
                    ${data.message}
                </p>
            `;

            return;
        }

        if (data.data.length === 0) {

            eventsContainer.innerHTML = `
                <p style="text-align:center;">
                    No events available.
                </p>
            `;

            return;
        }

        eventsContainer.innerHTML = "";
        allEventsCache = data.data;

        data.data.forEach(event => {

            const statusInfo = getStatusInfo(event.status);

            let dateDisplay = event.event_date;
            if (event.status === 'postponed' && event.original_date) {
                dateDisplay = `<span style="text-decoration:line-through;color:#999;">${event.original_date}</span> → ${event.event_date}`;
            }

            // Check if current user is the creator
            const isCreator = event.is_creator === true || event.is_creator === 1;

            // Build action buttons based on creator status
            let actionButtons = '';
            if (isCreator) {
                // Creator sees "You are the organizer" badge
                actionButtons = `<span class="organizer-badge">You are the organizer</span>`;
            } else {
                // Participants see Join and Cancel buttons
                actionButtons = `
                    <button onclick="joinEvent(${event.id})">Join Event</button>
                    <button class="btn-danger" onclick="cancelRegistration(${event.id})">Cancel Registration</button>
                `;
            }

            eventsContainer.innerHTML += `

                <div class="event-card">

                    <div class="event-content">

                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <h3>${event.title}</h3>
                            <span class="status-badge ${statusInfo.class}">${statusInfo.label}</span>
                        </div>

                        <p><strong>Date:</strong> ${dateDisplay}</p>

                        <p><strong>Location:</strong> ${event.location ?? ""}</p>

                        <p>${event.description ?? ""}</p>

                        <p><strong>Created By:</strong> ${event.created_by_name}</p>

                        <div style="margin-top:10px;">
                            ${actionButtons}
                        </div>

                    </div>

                </div>

            `;

        });

    } catch (error) {

        console.error(error);

        eventsContainer.innerHTML = `
            <p style="text-align:center;color:red;">
                Server Error
            </p>
        `;

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

async function joinEvent(eventId) {

    const formData = new FormData();
    formData.append("event_id", eventId);

    try {

        const response = await fetch("../backend/api/join_event.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        alert(data.message);

        if (data.success) {
            loadEvents();
        }

    } catch (error) {

        console.error(error);
        alert("Server Error");

    }

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
            loadEvents();
        }

    } catch (error) {

        console.error(error);
        alert("Server Error");

    }

}

loadEvents();
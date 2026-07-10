const eventsContainer = document.getElementById("eventsContainer");

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

        data.data.forEach(event => {

            eventsContainer.innerHTML += `

                <div class="event-card">

                    <div class="event-content">

                        <h3>${event.title}</h3>

                        <p><strong>Date:</strong> ${event.event_date}</p>

                        <p><strong>Location:</strong> ${event.location ?? ""}</p>

                        <p>${event.description ?? ""}</p>

                        <p><strong>Created By:</strong> ${event.created_by_name}</p>

                        <button onclick='updateEvent(${JSON.stringify(event)})'>
                            Update
                        </button>

                        <button onclick="joinEvent(${event.id})">
                            Join Event
                        </button>

                        <button onclick="deleteEvent(${event.id})">
                            Delete
                        </button>

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

    } catch (error) {

        console.error(error);
        alert("Server Error");

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
            loadEvents();
        }

    } catch (error) {

        console.error(error);
        alert("Server Error");

    }

}

async function updateEvent(event) {

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

    try {

        const response = await fetch("../backend/api/update_event.php", {
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
const container = document.getElementById("myEventsContainer");

async function loadMyEvents() {

    try {

        const response = await fetch("../backend/api/my_events.php");
        const data = await response.json();

        if (!data.success) {

            container.innerHTML = `<p>${data.message}</p>`;
            return;

        }

        if (data.data.length === 0) {

            container.innerHTML = `
                <p style="text-align:center;">
                    You haven't joined any events yet.
                </p>
            `;

            return;

        }

        container.innerHTML = "";

        data.data.forEach(event => {

            container.innerHTML += `

                <div class="event-card">

                    <div class="event-content">

                        <h3>${event.title}</h3>

                        <p><strong>Date:</strong> ${event.event_date}</p>

                        <p><strong>Location:</strong> ${event.location}</p>

                        <p>${event.description}</p>

                        <button onclick="cancelRegistration(${event.id})">
                            Cancel Registration
                        </button>

                    </div>

                </div>

            `;

        });

    } catch (error) {

        console.error(error);

        container.innerHTML = "<p>Server Error</p>";

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

            loadMyEvents();

        }

    } catch (error) {

        console.error(error);

    }

}

loadMyEvents();
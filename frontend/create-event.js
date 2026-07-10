const eventForm = document.getElementById("eventForm");
const eventMessage = document.getElementById("eventMessage");

eventForm.addEventListener("submit", async function (e) {

    e.preventDefault();

    const title = document.getElementById("title").value.trim();
    const event_date = document.getElementById("date").value;
    const location = document.getElementById("location").value.trim();
    const description = document.getElementById("description").value.trim();

    if (!title || !event_date || !location || !description) {

        eventMessage.style.color = "red";
        eventMessage.textContent = "Please complete all fields.";
        return;
    }

    const formData = new FormData();

    formData.append("title", title);
    formData.append("description", description);
    formData.append("event_date", event_date);
    formData.append("location", location);

    try {

        const response = await fetch("../backend/api/create_event.php", {

            method: "POST",
            body: formData

        });

        const data = await response.json();

        if (data.success) {

            eventMessage.style.color = "green";
            eventMessage.textContent = data.message;

            eventForm.reset();

            setTimeout(() => {

                window.location.href = "events.html";

            }, 1200);

        } else {

            eventMessage.style.color = "red";
            eventMessage.textContent = data.message;

        }

    } catch (error) {

        console.error(error);

        eventMessage.style.color = "red";
        eventMessage.textContent = "Server Error.";

    }

});
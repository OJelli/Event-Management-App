const profileName = document.getElementById("profileName");
const profileEmail = document.getElementById("profileEmail");
const logoutBtn = document.getElementById("logoutBtn");

async function loadProfile() {

    try {

        const response = await fetch("../backend/api/profile.php");

        const data = await response.json();

        if (data.success) {

            profileName.textContent = data.data.username;
            profileEmail.textContent = data.data.email;

        } else {

            alert(data.message);
            window.location.href = "login.html";

        }

    } catch (error) {

        console.error(error);

        alert("Server Error");

    }

}

logoutBtn.addEventListener("click", async function () {

    try {

        const response = await fetch("../backend/api/logout.php");

        const data = await response.json();

        if (data.success) {

            window.location.href = "login.html";

        }

    } catch (error) {

        console.error(error);

    }

});

loadProfile();
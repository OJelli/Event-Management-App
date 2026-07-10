const registerForm = document.getElementById("registerForm");
const message = document.getElementById("registerMessage");

registerForm.addEventListener("submit", async function (e) {

    e.preventDefault();

    const username = document.getElementById("fullName").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirmPassword").value.trim();

    if (!username || !email || !password || !confirmPassword) {
        message.style.color = "red";
        message.textContent = "Please fill in all fields.";
        return;
    }

    if (password !== confirmPassword) {
        message.style.color = "red";
        message.textContent = "Passwords do not match.";
        return;
    }

    const formData = new FormData();

    formData.append("username", username);
    formData.append("email", email);
    formData.append("password", password);

    try {

        const response = await fetch("../backend/api/register.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (data.success) {

            message.style.color = "green";
            message.textContent = data.message;

            setTimeout(() => {
                window.location.href = "login.html";
            }, 1500);

        } else {

            message.style.color = "red";
            message.textContent = data.message;

        }

    } catch (error) {

        console.log(error);

        message.style.color = "red";
        message.textContent = "Server Error.";

    }

});
const loginForm = document.getElementById("loginForm");
const message = document.getElementById("loginMessage");

loginForm.addEventListener("submit", async function (e) {

    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const rememberMe = document.getElementById("rememberMe")?.checked || false;

    if (!email || !password) {
        message.style.color = "red";
        message.textContent = "Please fill in all fields.";
        return;
    }

    const formData = new FormData();

    formData.append("username", email);
    formData.append("password", password);
    formData.append("remember_me", rememberMe ? "true" : "false");

    try {

        const response = await fetch("../backend/api/login.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (data.success) {

            message.style.color = "green";
            message.textContent = data.message;

            setTimeout(() => {
                window.location.href = "profile.html";
            }, 1000);

        } else {

            message.style.color = "red";
            message.textContent = data.message;

        }

    } catch (error) {

        console.error(error);

        message.style.color = "red";
        message.textContent = "Server Error.";

    }

});
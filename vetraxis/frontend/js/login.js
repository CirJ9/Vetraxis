const togglePassword =
    document.getElementById("togglePassword");

const password =
    document.getElementById("password");

const passwordIcon =
    document.getElementById("passwordIcon");

const loginForm =
    document.getElementById("loginForm");

const loginMessage =
    document.getElementById("loginMessage");


// =====================================
// SHOW / HIDE PASSWORD
// =====================================

togglePassword.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";

        passwordIcon.classList.remove("bi-eye");

        passwordIcon.classList.add("bi-eye-slash");

    } else {

        password.type = "password";

        passwordIcon.classList.remove("bi-eye-slash");

        passwordIcon.classList.add("bi-eye");

    }

});


// =====================================
// LOGIN
// =====================================

loginForm.addEventListener("submit", async function (event) {

    event.preventDefault();

    const username =
        document.getElementById("username")
        .value
        .trim();

    const passwordValue =
        password.value;


    if (username === "" || passwordValue === "") {

        showMessage(
            "Please enter your username and password.",
            "danger"
        );

        return;
    }


    showMessage(
        "Signing in...",
        "info"
    );


    try {

        const response = await fetch(
            "../backend/auth/login.php",
            {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({

                    username: username,

                    password: passwordValue

                })

            }
        );


        const data =
            await response.json();


        if (data.success) {

            // SAVE LOGGED-IN USER

            localStorage.setItem(
                "vetraxisUser",
                JSON.stringify(data.user)
            );


            showMessage(
                "Login successful! Opening dashboard...",
                "success"
            );


            setTimeout(function () {

                window.location.href =
                    "dashboard.html";

            }, 1000);


        } else {

            showMessage(
                data.message,
                "danger"
            );

        }


    } catch (error) {

        console.error(error);

        showMessage(
            "Unable to connect to the server.",
            "danger"
        );

    }

});


// =====================================
// MESSAGE
// =====================================

function showMessage(message, type) {

    loginMessage.className =
        `alert alert-${type}`;

    loginMessage.textContent =
        message;

}
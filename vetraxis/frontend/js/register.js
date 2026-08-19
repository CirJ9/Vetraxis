const registerForm =
    document.getElementById("registerForm");


const toggleRegisterPassword =
    document.getElementById("toggleRegisterPassword");


const registerPassword =
    document.getElementById("registerPassword");


const registerPasswordIcon =
    document.getElementById("registerPasswordIcon");


const confirmPassword =
    document.getElementById("confirmPassword");


const registerMessage =
    document.getElementById("registerMessage");


// =====================================
// SHOW / HIDE PASSWORD
// =====================================

toggleRegisterPassword.addEventListener("click", function () {

    if (registerPassword.type === "password") {

        registerPassword.type = "text";

        registerPasswordIcon.classList.remove("bi-eye");

        registerPasswordIcon.classList.add("bi-eye-slash");

    } else {

        registerPassword.type = "password";

        registerPasswordIcon.classList.remove("bi-eye-slash");

        registerPasswordIcon.classList.add("bi-eye");

    }

});


// =====================================
// REGISTER
// =====================================

registerForm.addEventListener("submit", async function (event) {

    event.preventDefault();


    const fullName =
        document.getElementById("fullName")
        .value
        .trim();


    const username =
        document.getElementById("username")
        .value
        .trim();


    const email =
        document.getElementById("email")
        .value
        .trim();


    const password =
        registerPassword.value;


    const confirm =
        confirmPassword.value;


    // PASSWORD LENGTH

    if (password.length < 6) {

        showMessage(
            "Password must be at least 6 characters.",
            "danger"
        );

        return;
    }


    // PASSWORD MATCH

    if (password !== confirm) {

        showMessage(
            "Passwords do not match.",
            "danger"
        );

        return;
    }


    // SHOW LOADING

    showMessage(
        "Creating your account...",
        "info"
    );


    try {

        const response = await fetch(
            "../backend/auth/register.php",
            {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({

                    full_name: fullName,

                    username: username,

                    email: email,

                    password: password

                })

            }
        );


        const data =
            await response.json();


        if (data.success) {

            showMessage(
                "Registration successful! Redirecting to login...",
                "success"
            );


            registerForm.reset();


            setTimeout(function () {

                window.location.href =
                    "index.html";

            }, 1500);


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
// MESSAGE FUNCTION
// =====================================

function showMessage(message, type) {

    registerMessage.className =
        `alert alert-${type}`;

    registerMessage.textContent =
        message;

}
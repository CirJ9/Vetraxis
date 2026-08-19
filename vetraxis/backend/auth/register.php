<?php

header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Methods: POST");

header("Access-Control-Allow-Headers: Content-Type");


require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Only POST Request
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get JSON Data
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);


$full_name = trim(
    $data["full_name"] ?? ""
);

$username = trim(
    $data["username"] ?? ""
);

$email = trim(
    $data["email"] ?? ""
);

$password = $data["password"] ?? "";


/*
|--------------------------------------------------------------------------
| Required Fields
|--------------------------------------------------------------------------
*/

if (
    $full_name === "" ||
    $username === "" ||
    $email === "" ||
    $password === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Username Validation
|--------------------------------------------------------------------------
*/

if (strlen($username) < 3) {

    echo json_encode([
        "success" => false,
        "message" => "Username must contain at least 3 characters."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Email Validation
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Password Validation
|--------------------------------------------------------------------------
*/

if (strlen($password) < 6) {

    echo json_encode([
        "success" => false,
        "message" => "Password must contain at least 6 characters."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Existing Username / Email
|--------------------------------------------------------------------------
*/

$check = $conn->prepare(
    "SELECT id FROM users
     WHERE username = ? OR email = ?
     LIMIT 1"
);

$check->bind_param(
    "ss",
    $username,
    $email
);

$check->execute();

$result = $check->get_result();


if ($result->num_rows > 0) {

    echo json_encode([
        "success" => false,
        "message" => "Username or email already exists."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Secure Password Hash
|--------------------------------------------------------------------------
*/

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| Insert User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "INSERT INTO users
    (full_name, username, email, password, role)
    VALUES (?, ?, ?, ?, 'user')"
);


$stmt->bind_param(
    "ssss",
    $full_name,
    $username,
    $email,
    $hashedPassword
);


if ($stmt->execute()) {

    echo json_encode([

        "success" => true,

        "message" =>
            "Registration successful.",

        "user" => [

            "id" => $stmt->insert_id,

            "full_name" =>
                $full_name,

            "username" =>
                $username,

            "email" =>
                $email,

            "role" =>
                "user"

        ]

    ]);

} else {

    echo json_encode([

        "success" => false,

        "message" =>
            "Registration failed."

    ]);

}


$stmt->close();

$check->close();

$conn->close();

?>
<?php
require_once "config.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert = $conn->prepare(
                "INSERT INTO users (name, email, phone, password)
                 VALUES (?, ?, ?, ?)"
            );

            $insert->bind_param(
                "ssss",
                $name,
                $email,
                $phone,
                $hashed_password
            );

            if ($insert->execute()) {

                header("Location: login.php?registered=1");
                exit;

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }

            $insert->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | FASHLADY</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap"
          rel="stylesheet">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #FFF9F5;
            color: #242124;
            font-family: "DM Sans", sans-serif;
        }

        .signup-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .signup-box {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            padding: 45px 42px;
            border: 1px solid #ead1d5;
            box-shadow: 0 15px 45px rgba(107, 38, 56, 0.08);
        }

        .brand {
            text-align: center;
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 30px;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .heading {
            text-align: center;
            font-family: "Playfair Display", serif;
            font-size: 27px;
            margin: 0;
        }

        .subtitle {
            text-align: center;
            color: #8d7c80;
            font-size: 13px;
            margin: 10px 0 30px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 7px;
            letter-spacing: .5px;
        }

        .field input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dec6cb;
            outline: none;
            font-family: "DM Sans", sans-serif;
            font-size: 14px;
        }

        .field input:focus {
            border-color: #C9828F;
        }

        .signup-btn {
            width: 100%;
            border: none;
            background: #6B2638;
            color: #fff;
            padding: 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            cursor: pointer;
            margin-top: 5px;
        }

        .signup-btn:hover {
            background: #C9828F;
        }

        .message {
            padding: 11px 13px;
            margin-bottom: 20px;
            font-size: 12px;
            text-align: center;
        }

        .error {
            background: #f9e7ea;
            color: #6B2638;
        }

        .login-text {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #777;
        }

        .login-text a {
            color: #6B2638;
            font-weight: 600;
            text-decoration: none;
        }

        .home-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            color: #777;
            text-decoration: none;
        }

        @media (max-width: 500px) {

            .signup-box {
                padding: 35px 25px;
            }

        }

    </style>

</head>

<body>

<div class="signup-page">

    <div class="signup-box">

        <div class="brand">FASHLADY</div>

        <h1 class="heading">Create Account</h1>

        <p class="subtitle">
            Join FASHLADY and discover your style.
        </p>

        <?php if ($message !== ""): ?>

            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="signup.php">

            <div class="field">
                <label>FULL NAME *</label>
                <input
                    type="text"
                    name="name"
                    placeholder="Enter your name"
                    required
                >
            </div>

            <div class="field">
                <label>EMAIL *</label>
                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >
            </div>

            <div class="field">
                <label>PHONE</label>
                <input
                    type="tel"
                    name="phone"
                    placeholder="Enter your phone number"
                >
            </div>

            <div class="field">
                <label>PASSWORD *</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                >
            </div>

            <div class="field">
                <label>CONFIRM PASSWORD *</label>
                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >
            </div>

            <button type="submit" class="signup-btn">
                CREATE ACCOUNT
            </button>

        </form>

        <div class="login-text">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

        <a href="index.php" class="home-link">
            ← Back to FASHLADY
        </a>

    </div>

</div>

</body>
</html>
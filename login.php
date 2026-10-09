<?php
session_start();
require_once "config.php";

$message = "";
$message_type = "";

if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $message = "Account created successfully. Please login.";
    $message_type = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Please enter your email and password.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

              if (isset($_SESSION['redirect_after_login'])) {
    $redirect = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']);

    header("Location: " . $redirect);
    exit;
}

header("Location: index.php");
exit;

            } else {

                $message = "Incorrect password.";
                $message_type = "error";
            }

        } else {

            $message = "No account found with this email.";
            $message_type = "error";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | FASHLADY</title>

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

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-box {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            padding: 48px 42px;
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
            margin-bottom: 20px;
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

        .login-btn {
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

        .login-btn:hover {
            background: #C9828F;
        }

        .message {
            padding: 11px 13px;
            margin-bottom: 20px;
            font-size: 12px;
            text-align: center;
        }

        .success {
            background: #edf6ee;
            color: #38633d;
        }

        .error {
            background: #f9e7ea;
            color: #6B2638;
        }

        .signup-text {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #777;
        }

        .signup-text a {
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

            .login-box {
                padding: 35px 25px;
            }

        }

    </style>

</head>

<body>

<div class="login-page">

    <div class="login-box">

        <div class="brand">FASHLADY</div>

        <h1 class="heading">Welcome Back</h1>

        <p class="subtitle">
            Login to continue shopping with FASHLADY.
        </p>

        <?php if ($message !== ""): ?>

            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="field">

                <label>EMAIL</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="field">

                <label>PASSWORD</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>

            <button type="submit" class="login-btn">
                LOGIN
            </button>

        </form>

        <div class="signup-text">

            Don't have an account?

            <a href="signup.php">
                Create Account
            </a>

        </div>

        <a href="index.php" class="home-link">
            ← Back to FASHLADY
        </a>

    </div>

</div>

</body>
</html>
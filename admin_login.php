
<?php
session_start();
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "admin@fashlady.com" && $password === "Admin@123") {

        $_SESSION["admin_logged_in"] = true;

        header("Location: admin_updated.php");
        exit;

    } else {

        $error = "Invalid admin email or password.";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap"
          rel="stylesheet">

    <style>

        .admin-login-page {
            min-height: 100vh;
            background: #FFF9F5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .admin-login-box {
            width: 100%;
            max-width: 430px;
            background: white;
            padding: 45px;
            box-sizing: border-box;
            box-shadow: 0 15px 45px rgba(107, 38, 56, 0.10);
        }

        .admin-login-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-login-heading span {
            color: #C9828F;
            font-size: 12px;
            letter-spacing: 2px;
        }

        .admin-login-heading h1 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            margin: 8px 0;
        }

        .admin-login-heading p {
            color: #777;
            font-size: 14px;
        }

        .admin-login-box label {
            display: block;
            margin-bottom: 8px;
            color: #242124;
            font-size: 14px;
        }

        .admin-login-box input {
            width: 100%;
            padding: 13px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-family: "DM Sans", sans-serif;
        }

        .admin-login-box button {
            width: 100%;
            padding: 14px;
            border: none;
            background: #6B2638;
            color: white;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .admin-login-box button:hover {
            background: #C9828F;
        }

        .admin-error {
            background: #fce8eb;
            color: #9b3048;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        @media (max-width: 500px) {

            .admin-login-box {
                padding: 30px 20px;
            }

        }

    </style>

</head>

<body>

<div class="admin-login-page">

    <div class="admin-login-box">

        <div class="admin-login-heading">

            <span>FASHLADY ADMIN</span>

            <h1>Admin Login</h1>

            <p>Login to manage your store.</p>

        </div>

        <?php if ($error !== ""): ?>

            <div class="admin-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="admin@fashlady.com"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter password"
                required
            >

            <button type="submit">
                LOGIN TO ADMIN
            </button>

        </form>

    </div>

</div>

</body>

</html>
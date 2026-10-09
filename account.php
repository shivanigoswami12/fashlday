<?php

session_start();
require_once "config.php";

/* Login check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* Get current user's details */
$stmt = $conn->prepare(
    "SELECT id, name, email, phone, created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

/* Get only the logged-in user's orders */
$order_stmt = $conn->prepare(
    "SELECT id, total_amount, order_status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$order_stmt->bind_param("i", $user_id);

$order_stmt->execute();
$orders = $order_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Account | FASHLADY</title>

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

        .account-page {
            min-height: 100vh;
            padding: 55px 7%;
        }

        .account-header {
            text-align: center;
            margin-bottom: 45px;
        }

        .brand {
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 30px;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .account-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 34px;
            margin: 0 0 8px;
        }

        .account-header p {
            color: #8d7c80;
            font-size: 14px;
        }

        .account-grid {
            max-width: 1050px;
            margin: auto;
            display: grid;
            grid-template-columns: 330px 1fr;
            gap: 25px;
        }

        .account-card {
            background: #fff;
            border: 1px solid #ead1d5;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(107, 38, 56, .05);
        }

        .account-card h2 {
            font-family: "Playfair Display", serif;
            font-size: 22px;
            margin: 0 0 25px;
        }

        .profile-icon {
            width: 75px;
            height: 75px;
            margin-bottom: 18px;
            border-radius: 50%;
            background: #F3DDE1;
            color: #6B2638;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .detail {
            margin-bottom: 18px;
        }

        .detail-label {
            display: block;
            font-size: 10px;
            color: #9a8d90;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 14px;
            color: #242124;
            word-break: break-word;
        }

        .logout-btn {
            display: block;
            text-align: center;
            margin-top: 28px;
            padding: 13px;
            background: #6B2638;
            color: #fff;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.4px;
        }

        .logout-btn:hover {
            background: #C9828F;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            background: #FFF9F5;
            padding: 13px 10px;
            font-size: 10px;
            letter-spacing: 1px;
            color: #6B2638;
        }

        .orders-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee1e3;
            font-size: 13px;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            background: #f3dde1;
            color: #6B2638;
            font-size: 10px;
            font-weight: 600;
        }

        .back-home {
            display: block;
            max-width: 1050px;
            margin: 30px auto 0;
            color: #6B2638;
            text-decoration: none;
            font-size: 13px;
        }

        .no-orders {
            color: #8d7c80;
            font-size: 13px;
            padding: 15px 0;
        }

        @media (max-width: 800px) {

            .account-page {
                padding: 40px 5%;
            }

            .account-grid {
                grid-template-columns: 1fr;
            }

            .orders-table {
                min-width: 650px;
            }

            .account-card.orders-card {
                overflow-x: auto;
            }

        }

    </style>

</head>

<body>

<div class="account-page">

    <div class="account-header">

        <div class="brand">FASHLADY</div>

        <h1>My Account</h1>

        <p>
            Welcome back, <?= htmlspecialchars($user['name']) ?>
        </p>

    </div>


    <div class="account-grid">

        <!-- PROFILE -->

        <div class="account-card">

            <div class="profile-icon">
                ♡
            </div>

            <h2>Personal Details</h2>

            <div class="detail">

                <span class="detail-label">
                    NAME
                </span>

                <div class="detail-value">
                    <?= htmlspecialchars($user['name']) ?>
                </div>

            </div>

            <div class="detail">

                <span class="detail-label">
                    EMAIL
                </span>

                <div class="detail-value">
                    <?= htmlspecialchars($user['email']) ?>
                </div>

            </div>

            <div class="detail">

                <span class="detail-label">
                    PHONE
                </span>

                <div class="detail-value">
                    <?= !empty($user['phone'])
                        ? htmlspecialchars($user['phone'])
                        : 'Not added' ?>
                </div>

            </div>

            <div class="detail">

                <span class="detail-label">
                    MEMBER SINCE
                </span>

                <div class="detail-value">
                    <?= date("d M Y", strtotime($user['created_at'])) ?>
                </div>

            </div>

            <a href="logout.php" class="logout-btn">
                LOGOUT
            </a>

        </div>


        <!-- ORDERS -->

        <div class="account-card orders-card">

            <h2>My Orders</h2>

            <?php if ($orders->num_rows > 0): ?>

                <table class="orders-table">

                    <thead>

                        <tr>
                            <th>ORDER</th>
                            <th>DATE</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($order = $orders->fetch_assoc()): ?>

                        <tr>

                            <td>
                                #<?= (int)$order['id'] ?>
                            </td>

                            <td>
                                <?= date(
                                    "d M Y",
                                    strtotime($order['created_at'])
                                ) ?>
                            </td>

                            <td>
                                ₹<?= number_format(
                                    $order['total_amount'],
                                    0
                                ) ?>
                            </td>

                            <td>

                                <span class="status">
                                    <?= htmlspecialchars(
                                        $order['order_status']
                                    ) ?>
                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p class="no-orders">
                    You haven't placed any orders yet.
                </p>

            <?php endif; ?>

        </div>

    </div>


    <a href="index.php" class="back-home">
        ← Continue Shopping
    </a>

</div>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once "config.php";

/* LOAD ORDERS */

$sql = "
    SELECT
        id,
        customer_name,
        phone,
        email,
        city,
        pincode,
        total_amount,
        order_status,
        created_at
    FROM orders
    ORDER BY id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orders | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap"
          rel="stylesheet">

    <style>

        .orders-page {
            min-height: 100vh;
            background: #FFF9F5;
            padding: 70px 20px;
        }

        .orders-box {
            max-width: 1100px;
            margin: auto;
            background: #fff;
            padding: 45px;
            box-shadow: 0 10px 40px rgba(75, 41, 51, 0.08);
        }

        .orders-heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .orders-heading span {
            color: #C9828F;
            font-size: 11px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        .orders-heading h1 {
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 42px;
            margin: 10px 0;
        }

        .orders-heading p {
            color: #777;
            font-size: 14px;
        }

        .order-card {
            border: 1px solid #ead9dc;
            background: #fff9f5;
            padding: 22px;
            margin-bottom: 18px;
        }

        .order-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .order-id {
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 21px;
            font-weight: 600;
        }

        .order-status {
            background: #F3DDE1;
            color: #6B2638;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            border-top: 1px solid #ead9dc;
            padding-top: 18px;
        }

        .order-details div {
            font-size: 13px;
            color: #555;
        }

        .order-details strong {
            display: block;
            color: #242124;
            margin-top: 5px;
        }

        .order-total {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #ead9dc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-total span {
            color: #777;
            font-size: 13px;
        }

        .order-total strong {
            color: #6B2638;
            font-size: 20px;
        }

        .no-orders {
            text-align: center;
            color: #777;
            padding: 40px 0;
        }

        .back-admin {
            display: block;
            width: fit-content;
            margin: 30px auto 0;
            color: #6B2638;
            text-decoration: none;
            font-size: 13px;
        }

        .back-admin:hover {
            color: #C9828F;
        }

        @media (max-width: 750px) {

            .orders-box {
                padding: 30px 20px;
            }

            .orders-heading h1 {
                font-size: 34px;
            }

            .order-details {
                grid-template-columns: 1fr 1fr;
            }

        }

        @media (max-width: 500px) {

            .order-details {
                grid-template-columns: 1fr;
            }

            .order-top {
                align-items: flex-start;
                flex-direction: column;
            }

        }
.view-order-btn {
    display: inline-block;
    margin-top: 18px;
    padding: 11px 18px;
    background: #6B2638;
    color: white;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1px;
    font-weight: 600;
}

.view-order-btn:hover {
    background: #C9828F;
}

    </style>

</head>

<body>

<div class="orders-page">

    <div class="orders-box">

        <div class="orders-heading">

            <span>FASHLADY ADMIN</span>

            <h1>Customer Orders</h1>

            <p>
                View all orders placed on your online store.
            </p>

        </div>


        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($order = $result->fetch_assoc()): ?>

                <div class="order-card">

                    <div class="order-top">

                        <div class="order-id">
                            Order #<?= (int)$order['id'] ?>
                        </div>

                        <div class="order-status">
                            <?= htmlspecialchars($order['order_status']) ?>
                        </div>

                    </div>


                    <div class="order-details">

                        <div>
                            Customer
                            <strong>
                                <?= htmlspecialchars($order['customer_name']) ?>
                            </strong>
                        </div>

                        <div>
                            Phone
                            <strong>
                                <?= htmlspecialchars($order['phone']) ?>
                            </strong>
                        </div>

                        <div>
                            Email
                            <strong>
                                <?= htmlspecialchars($order['email']) ?>
                            </strong>
                        </div>

                        <div>
                            City
                            <strong>
                                <?= htmlspecialchars($order['city']) ?>
                            </strong>
                        </div>

                        <div>
                            PIN Code
                            <strong>
                                <?= htmlspecialchars($order['pincode']) ?>
                            </strong>
                        </div>

                        <div>
                            Order Date
                            <strong>
                                <?= date("d M Y, h:i A", strtotime($order['created_at'])) ?>
                            </strong>
                        </div>

                    </div>


                    <div class="order-total">

                        <span>
                            Total Amount
                        </span>

                        <strong>
                            ₹<?= number_format($order['total_amount'], 2) ?>
                        </strong>

                    </div>
<a href="admin_order_details.php?order_id=<?= (int)$order['id'] ?>"
   class="view-order-btn">
    VIEW DETAILS
</a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-orders">
                No orders have been placed yet.
            </div>

        <?php endif; ?>


        <a href="admin_updated.php" class="back-admin">
            ← Back to Admin Panel
        </a>

    </div>

</div>

</body>

</html>
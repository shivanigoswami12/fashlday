<?php
require_once "config.php";

$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: shop.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT customer_name, total_amount, order_status
    FROM orders
    WHERE id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shop.php");
    exit;
}

$order = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Confirmed | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">

    <style>

        .success-page {
            min-height: 100vh;
            background: #FFF9F5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
        }

        .success-box {
            max-width: 600px;
            width: 100%;
            background: #fffdfb;
            border: 1px solid #e5c9ce;
            border-radius: 20px;
            padding: 50px 35px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(107, 38, 56, 0.08);
        }

        .success-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: #F3DDE1;
            color: #6B2638;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .success-box span {
            color: #C9828F;
            font-size: 11px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        .success-box h1 {
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 38px;
            margin: 12px 0;
        }

        .success-box p {
            color: #666;
            font-size: 15px;
            line-height: 1.7;
        }

        .order-number {
            margin: 25px 0;
            padding: 18px;
            background: #F3DDE1;
            border-radius: 10px;
            color: #6B2638;
        }

        .order-number strong {
            display: block;
            margin-top: 6px;
            font-size: 20px;
        }

        .success-total {
            font-size: 18px;
            color: #242124;
            margin: 20px 0;
        }

        .success-total strong {
            color: #6B2638;
        }

        .continue-shopping {
            display: inline-block;
            margin-top: 15px;
            padding: 14px 28px;
            background: #6B2638;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .continue-shopping:hover {
            background: #C9828F;
        }

    </style>

</head>

<body>

<div class="success-page">

    <div class="success-box">

        <div class="success-icon">
            ✓
        </div>

        <span>FASHLADY</span>

        <h1>Order Confirmed!</h1>

        <p>
            Thank you, <?= htmlspecialchars($order['customer_name']) ?>.
            Your order has been successfully placed.
        </p>

        <div class="order-number">

            Order Number

            <strong>
                #<?= $order_id ?>
            </strong>

        </div>

        <div class="success-total">

            Order Total:
            <strong>
                ₹<?= number_format($order['total_amount'], 2) ?>
            </strong>

        </div>

        <p>
            Order Status:
            <strong><?= htmlspecialchars($order['order_status']) ?></strong>
        </p>

        <a href="shop.php" class="continue-shopping">
            CONTINUE SHOPPING
        </a>

    </div>

</div>

</body>

</html>
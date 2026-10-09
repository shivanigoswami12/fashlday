<?php
session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once "config.php";

$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: admin_orders.php");
    exit;
}


/* ORDER DETAILS */

$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        phone,
        email,
        address,
        city,
        pincode,
        total_amount,
        order_status,
        created_at
    FROM orders
    WHERE id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: admin_orders.php");
    exit;
}

$order = $order_result->fetch_assoc();
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['update_status'])) {

    $new_status = $_POST['order_status'] ?? 'Pending';

    $allowed_statuses = [
        'Pending',
        'Confirmed',
        'Shipped',
        'Delivered',
        'Cancelled'
    ];

    if (in_array($new_status, $allowed_statuses, true)) {

        $update = $conn->prepare("
            UPDATE orders
            SET order_status = ?
            WHERE id = ?
        ");

        $update->bind_param(
            "si",
            $new_status,
            $order_id
        );

        $update->execute();
        $update->close();

        header(
            "Location: admin_order_details.php?order_id=" . $order_id
        );
        exit;
    }
}

$stmt->close();


/* ORDERED PRODUCTS */

$stmt = $conn->prepare("
   SELECT
    order_items.quantity,
    order_items.price,
    order_items.variant_id,
    order_items.size,
    product_variants.color_name,
    products.name,
    products.image
    FROM order_items
    INNER JOIN products
        ON order_items.product_id = products.id
LEFT JOIN product_variants
    ON order_items.variant_id = product_variants.id

    WHERE order_items.order_id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$items_result = $stmt->get_result();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order #<?= $order_id ?> | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap"
          rel="stylesheet">

    <style>

        .order-details-page {
            min-height: 100vh;
            background: #FFF9F5;
            padding: 70px 20px;
        }

        .order-details-box {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 45px;
            box-shadow: 0 10px 40px rgba(75, 41, 51, 0.08);
        }

        .order-heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .order-heading span {
            color: #C9828F;
            font-size: 11px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        .order-heading h1 {
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 40px;
            margin: 10px 0;
        }

        .order-heading p {
            color: #777;
            font-size: 14px;
        }

        .customer-box,
        .products-box {
            border: 1px solid #ead9dc;
            background: #fff9f5;
            padding: 25px;
            margin-bottom: 25px;
        }

        .section-title {
            color: #6B2638;
            font-family: "Playfair Display", serif;
            font-size: 23px;
            margin: 0 0 20px;
        }

        .customer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .customer-item {
            color: #777;
            font-size: 13px;
        }

        .customer-item strong {
            display: block;
            color: #242124;
            margin-top: 5px;
        }

        .full-address {
            grid-column: 1 / -1;
        }

        .product-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            align-items: center;
            gap: 18px;
            padding: 15px 0;
            border-bottom: 1px solid #ead9dc;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 95px;
            background: #f3dde1;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info h3 {
            margin: 0 0 7px;
            color: #242124;
            font-family: "Playfair Display", serif;
            font-size: 18px;
        }

        .product-info p {
            margin: 4px 0;
            color: #777;
            font-size: 13px;
        }

        .product-price {
            color: #6B2638;
            font-weight: 600;
            font-size: 15px;
        }

        .order-final-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #ead9dc;
            margin-top: 20px;
            padding-top: 20px;
            font-size: 15px;
        }

        .order-final-total strong {
            color: #6B2638;
            font-size: 22px;
        }

        .back-orders {
            display: block;
            width: fit-content;
            margin: 30px auto 0;
            color: #6B2638;
            text-decoration: none;
            font-size: 13px;
        }

        .back-orders:hover {
            color: #C9828F;
        }

        @media (max-width: 650px) {

            .order-details-box {
                padding: 30px 20px;
            }

            .customer-grid {
                grid-template-columns: 1fr;
            }

            .full-address {
                grid-column: auto;
            }

            .product-item {
                grid-template-columns: 65px 1fr;
            }

            .product-image {
                width: 65px;
                height: 80px;
            }

            .product-price {
                grid-column: 2;
            }

        }

    </style>

</head>

<body>

<div class="order-details-page">

    <div class="order-details-box">

        <div class="order-heading">

            <span>FASHLADY ADMIN</span>

            <h1>Order #<?= $order_id ?></h1>

            <p>
                Complete order information
            </p>

        </div>


        <!-- CUSTOMER INFORMATION -->

        <div class="customer-box">

            <h2 class="section-title">
                Customer Details
            </h2>

            <div class="customer-grid">

                <div class="customer-item">
                    Customer Name
                    <strong>
                        <?= htmlspecialchars($order['customer_name']) ?>
                    </strong>
                </div>

                <div class="customer-item">
                    Phone
                    <strong>
                        <?= htmlspecialchars($order['phone']) ?>
                    </strong>
                </div>

                <div class="customer-item">
                    Email
                    <strong>
                        <?= htmlspecialchars($order['email']) ?>
                    </strong>
                </div>

                <div class="customer-item">
                    City
                    <strong>
                        <?= htmlspecialchars($order['city']) ?>
                    </strong>
                </div>

                <div class="customer-item">
                    PIN Code
                    <strong>
                        <?= htmlspecialchars($order['pincode']) ?>
                    </strong>
                </div>

                <div class="customer-item">

    Order Status

    <form method="POST" style="margin-top:8px;">

        <select name="order_status"
                style="
                    padding:10px;
                    border:1px solid #e5c9ce;
                    background:#fff;
                    color:#6B2638;
                    outline:none;
                    font-family:'DM Sans',sans-serif;
                ">

            <option value="Pending"
                <?= $order['order_status'] === 'Pending' ? 'selected' : '' ?>>
                Pending
            </option>

            <option value="Confirmed"
                <?= $order['order_status'] === 'Confirmed' ? 'selected' : '' ?>>
                Confirmed
            </option>

            <option value="Shipped"
                <?= $order['order_status'] === 'Shipped' ? 'selected' : '' ?>>
                Shipped
            </option>

            <option value="Delivered"
                <?= $order['order_status'] === 'Delivered' ? 'selected' : '' ?>>
                Delivered
            </option>

            <option value="Cancelled"
                <?= $order['order_status'] === 'Cancelled' ? 'selected' : '' ?>>
                Cancelled
            </option>

        </select>

        <button type="submit"
                name="update_status"
                style="
                    margin-left:8px;
                    padding:10px 15px;
                    border:none;
                    background:#6B2638;
                    color:white;
                    cursor:pointer;
                    font-size:11px;
                    letter-spacing:1px;
                ">
            UPDATE
        </button>

    </form>

</div>

                <div class="customer-item full-address">
                    Delivery Address
                    <strong>
                        <?= nl2br(htmlspecialchars($order['address'])) ?>
                    </strong>
                </div>

            </div>

        </div>


        <!-- ORDERED PRODUCTS -->

        <div class="products-box">

            <h2 class="section-title">
                Ordered Products
            </h2>

            <?php if ($items_result && $items_result->num_rows > 0): ?>

                <?php while ($item = $items_result->fetch_assoc()): ?>

                    <div class="product-item">

                        <div class="product-image">

                            <?php if (!empty($item['image'])): ?>

                                <img
                                    src="images/<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                >

                            <?php else: ?>

                                No Image

                            <?php endif; ?>

                        </div>


                        <div class="product-info">

                            <h3>
                                <?= htmlspecialchars($item['name']) ?>
                            </h3>

                            <p>
                                Quantity:
                                <?= (int)$item['quantity'] ?>
                            </p>
<?php if (!empty($item['color_name'])): ?>

    <p>
        Colour:
        <?= htmlspecialchars($item['color_name']) ?>
    </p>

<?php endif; ?>

<?php if (!empty($item['size'])): ?>

    <p>
        Size:
        <?= htmlspecialchars($item['size']) ?>
    </p>

<?php endif; ?>
                            <p>
                                Price:
                                ₹<?= number_format($item['price'], 2) ?>
                            </p>

                        </div>


                        <div class="product-price">

                            ₹<?= number_format(
                                $item['price'] * $item['quantity'],
                                2
                            ) ?>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p style="color:#777;">
                    No products found for this order.
                </p>

            <?php endif; ?>


            <div class="order-final-total">

                <span>
                    Order Total
                </span>

                <strong>
                    ₹<?= number_format($order['total_amount'], 2) ?>
                </strong>

            </div>

        </div>


        <a href="admin_orders.php" class="back-orders">
            ← Back to Orders
        </a>

    </div>

</div>

</body>

</html>
<?php
session_start();
require_once "config.php";

/* User must be logged in to place an order */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* Get logged-in user's saved details */
$user_stmt = $conn->prepare(
    "SELECT name, email, phone FROM users WHERE id = ? LIMIT 1"
);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$sql = "
    SELECT
    cart.id AS cart_id,
    cart.quantity,
    cart.variant_id,
    cart.size,
    product_variants.color_name,
    products.name,
    products.price,
    products.image
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
LEFT JOIN product_variants
    ON cart.variant_id = product_variants.id
    WHERE cart.user_id = ?
    ORDER BY cart.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>

/* CHECKOUT PAGE */

.checkout-page {
    background: #FFF9F5;
    min-height: 100vh;
    padding-bottom: 70px;
}

.checkout-heading {
    text-align: center;
    padding: 55px 20px 40px;
    background: #F3DDE1;
}

.checkout-heading span {
    color: #C9828F;
    font-size: 11px;
    letter-spacing: 3px;
    font-weight: 600;
}

.checkout-heading h1 {
    margin: 10px 0;
    color: #6B2638;
    font-family: "Playfair Display", serif;
    font-size: 42px;
}

.checkout-heading p {
    color: #666;
    margin: 0;
}

.checkout-container {
    max-width: 1100px;
    margin: 45px auto;
    padding: 0 25px;
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 35px;
}

.checkout-details,
.checkout-summary {
    background: #fffdfb;
    border: 1px solid #e5c9ce;
    border-radius: 18px;
    padding: 30px;
    box-shadow: 0 8px 25px rgba(107, 38, 56, 0.06);
}

.checkout-details h2,
.checkout-summary h2 {
    color: #6B2638;
    font-family: "Playfair Display", serif;
    font-size: 25px;
    margin: 0 0 25px;
}

.checkout-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.checkout-field {
    margin-bottom: 18px;
}

.checkout-field label {
    display: block;
    color: #242124;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 7px;
}

.checkout-field input,
.checkout-field textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 13px 14px;
    border: 1px solid #e5c9ce;
    border-radius: 7px;
    background: #fff;
    color: #242124;
    font-family: "DM Sans", sans-serif;
    font-size: 14px;
    outline: none;
}

.checkout-field textarea {
    resize: vertical;
}

.checkout-field input:focus,
.checkout-field textarea:focus {
    border-color: #C9828F;
}

.checkout-product {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eadde0;
}

.checkout-product img {
    width: 90px;
    height: 110px;
    object-fit: cover;
    border-radius: 9px;
}

.checkout-product h3 {
    color: #6B2638;
    font-family: "Playfair Display", serif;
    font-size: 18px;
    margin: 5px 0 8px;
}

.checkout-product p {
    color: #666;
    font-size: 13px;
    margin: 5px 0;
}

.checkout-product strong {
    color: #242124;
    font-size: 14px;
}

.checkout-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 0;
    font-size: 18px;
}

.checkout-total strong {
    color: #6B2638;
    font-size: 21px;
}

.place-order-btn {
    width: 100%;
    padding: 15px;
    background: #6B2638;
    color: white;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 1.5px;
}

.place-order-btn:hover {
    background: #C9828F;
}

@media (max-width: 800px) {

    .checkout-container {
        grid-template-columns: 1fr;
    }

    .checkout-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .checkout-heading h1 {
        font-size: 34px;
    }
}

</style>
</head>

<body>

<header class="navbar">

    <div class="logo">
        FASHLADY
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="collections.php">Collections</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>

    <div class="nav-icons">

        <a href="#" class="nav-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a href="#" class="nav-icon">
            <i class="fa-regular fa-user"></i>
        </a>

        <a href="wishlist.php" class="nav-icon">
            <i class="fa-regular fa-heart"></i>
        </a>

        <a href="cart.php" class="nav-icon">
            <i class="fa-solid fa-bag-shopping"></i>
        </a>

    </div>

</header>


<section class="checkout-page">

    <div class="checkout-heading">
        <span>FASHLADY</span>
        <h1>Checkout</h1>
        <p>Complete your order details.</p>
    </div>


    <div class="checkout-container">

        <!-- CUSTOMER DETAILS -->

        <div class="checkout-details">

            <h2>Delivery Details</h2>

            <form action="place_order.php" method="POST" id="checkout-form">

                <div class="checkout-row">

                    <div class="checkout-field">
                        <label>Full Name</label>
                        <input type="text"
                               name="name"
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                               placeholder="Enter your full name"
                               required>
                    </div>

                    <div class="checkout-field">
                        <label>Phone Number</label>
                        <input type="tel"
                               name="phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                               placeholder="Enter your phone number"
                               required>
                    </div>

                </div>


                <div class="checkout-field">
                    <label>Email Address</label>

                    <input type="email"
                           name="email"
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           placeholder="Enter your email address"
                           required>
                </div>


                <div class="checkout-field">
                    <label>Address</label>

                    <textarea name="address"
                              rows="4"
                              placeholder="House no., street, area"
                              required></textarea>
                </div>


                <div class="checkout-row">

                    <div class="checkout-field">
                        <label>City</label>

                        <input type="text"
                               name="city"
                               placeholder="City"
                               required>
                    </div>

                    <div class="checkout-field">
                        <label>PIN Code</label>

                        <input type="text"
                               name="pincode"
                               placeholder="PIN Code"
                               required>
                    </div>

                </div>

            </form>

        </div>


        <!-- ORDER SUMMARY -->

        <div class="checkout-summary">

            <h2>Order Summary</h2>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($product = $result->fetch_assoc()): ?>

                    <?php
                    $item_total =
                        $product['price'] * $product['quantity'];

                    $grand_total += $item_total;
                    ?>

                    <div class="checkout-product">

                        <?php if (!empty($product['image'])): ?>

                            <img
                                src="images/<?= htmlspecialchars($product['image']) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                            >

                        <?php endif; ?>

                        <div>

                            <h3>
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>

                            <p>
                                Qty:
                                <?= (int)$product['quantity'] ?>
                            </p>
<?php if (!empty($product['color_name'])): ?>

    <p>
        Colour: <?= htmlspecialchars($product['color_name']) ?>
    </p>

<?php endif; ?>

<?php if (!empty($product['size'])): ?>

    <p>
        Size: <?= htmlspecialchars($product['size']) ?>
    </p>

<?php endif; ?>

                            <strong>
                                ₹<?= number_format($item_total, 2) ?>
                            </strong>

                        </div>

                    </div>

                <?php endwhile; ?>


                <div class="checkout-total">

                    <span>Grand Total</span>

                    <strong>
                        ₹<?= number_format($grand_total, 2) ?>
                    </strong>

                </div>


               <button type="submit"
        form="checkout-form"
        class="place-order-btn">
    PLACE ORDER
</button>


            <?php else: ?>

                <p>Your cart is empty.</p>

                <a href="shop.php">
                    Continue Shopping
                </a>

            <?php endif; ?>

        </div>

    </div>

</section>

</body>
</html>
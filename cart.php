<?php
session_start();
require_once "config.php";
if (!isset($_SESSION["user_id"])) {
    $_SESSION["redirect_after_login"] = "cart.php";
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

$sql = "
   SELECT 
    cart.id AS cart_id,
    cart.quantity,
    cart.variant_id,
    cart.size,
product_variants.color_name,
    products.id AS product_id,
    products.name,
    products.price,
    products.old_price,
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
// Cart item count
$count_stmt = $conn->prepare(
    "SELECT COALESCE(SUM(quantity), 0) AS cart_count
     FROM cart
     WHERE user_id = ?"
);

$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();

$count_result = $count_stmt->get_result();

$cart_count = 0;

if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $cart_count = (int)$count_row['cart_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
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

   <div class="nav-icons">

    <?php if (isset($_SESSION['user_id'])): ?>

        <a href="account.php" aria-label="Account" class="nav-icon">
            <i class="fa-regular fa-user"></i>
        </a>

    <?php else: ?>

        <a href="login.php" aria-label="Account" class="nav-icon">
            <i class="fa-regular fa-user"></i>
        </a>

    <?php endif; ?>

    <a href="wishlist.php" class="nav-icon">
        <i class="fa-regular fa-heart"></i>
    </a>

    <a href="cart.php" class="nav-icon">
        <i class="fa-solid fa-bag-shopping"></i>
        <span class="cart-count"><?= $cart_count ?></span>
    </a>

</div>

</header>

<h1>Your Shopping Cart</h1>

<div class="cart-container">

<?php if ($result && $result->num_rows > 0): ?>

    <?php $grand_total = 0; ?>

<?php while ($product = $result->fetch_assoc()): ?>

        <div class="cart-item">

            <?php if (!empty($product['image'])): ?>
                <img 
                    src="images/<?= htmlspecialchars($product['image']) ?>" 
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    width="150"
                >
            <?php endif; ?>

            <div class="cart-details">

                <h2><?= htmlspecialchars($product['name']) ?></h2>

                <p>
                    Price: ₹<?= number_format($product['price'], 2) ?>
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

                <p>
                    Quantity: <?= (int)$product['quantity'] ?>
                </p>

<form action="update_cart.php" method="POST" class="quantity-form">

    <input type="hidden"
           name="cart_id"
           value="<?= (int)$product['cart_id'] ?>">

    <button type="button"
            onclick="changeQuantity(this, -1)">
        −
    </button>

    <input type="number"
           name="quantity"
           value="<?= (int)$product['quantity'] ?>"
           min="1"
           readonly>

    <button type="button"
            onclick="changeQuantity(this, 1)">
        +
    </button>

</form>

                <p>
                    Total:
                    ₹<?= number_format(
                        $product['price'] * $product['quantity'],
                        2
                    ) ?>
                </p>
<?php
$grand_total += $product['price'] * $product['quantity'];
?>

<form action="remove_cart.php" method="POST"
      onsubmit="return confirm('Remove this product from cart?');">

    <input type="hidden"
           name="cart_id"
           value="<?= (int)$product['cart_id'] ?>">

    <button type="submit">
        REMOVE
    </button>

</form>

            </div>

        </div>

    <?php endwhile; ?>
<div class="cart-total">
    <h2>
        Grand Total: ₹<?= number_format($grand_total, 2) ?>
    </h2>

   <a href="checkout.php" class="checkout-btn">
    PROCEED TO CHECKOUT
</a>
</div>

<?php else: ?>

    <h2>Your cart is empty.</h2>

<?php endif; ?>

</div>
<script>
function changeQuantity(button, change) {

    const form = button.closest("form");
    const input = form.querySelector('input[name="quantity"]');

    let quantity = parseInt(input.value);

    quantity = quantity + change;

    if (quantity < 1) {
        quantity = 1;
    }

    input.value = quantity;

    form.submit();
}
</script>
</body>
</html>
<?php
session_start();
require_once "config.php";
/* Cart item count */
$cart_count = 0;

if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

    $count_stmt = $conn->prepare(
        "SELECT COALESCE(SUM(quantity), 0) AS cart_count
         FROM cart
         WHERE user_id = ?"
    );

    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();

    $count_result = $count_stmt->get_result();

    if ($count_result) {
        $count_row = $count_result->fetch_assoc();
        $cart_count = (int)$count_row['cart_count'];
    }

    $count_stmt->close();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$sql = "
    SELECT wishlist.id AS wishlist_id,
           products.id,
           products.name,
           products.category,
           products.price,
           products.old_price,
           products.image,
           products.badge
    FROM wishlist
    INNER JOIN products
        ON wishlist.product_id = products.id
    WHERE wishlist.user_id = ?
    ORDER BY wishlist.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if (!$result) {
    die("Database error: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Wishlist | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
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

    <?php if (isset($_SESSION['user_id'])): ?>

    <a href="account.php" aria-label="Account" class="nav-icon">
        <i class="fa-regular fa-user"></i>
    </a>

<?php else: ?>

    <a href="login.php" aria-label="Account" class="nav-icon">
        <i class="fa-regular fa-user"></i>
    </a>

<?php endif; ?>

    <a href="wishlist.php" aria-label="Wishlist" class="nav-icon">
        <i class="fa-regular fa-heart"></i>
    </a>

    <a href="cart.php" aria-label="Cart" class="nav-icon">
        <i class="fa-solid fa-bag-shopping"></i>
        <span class="cart-count"><?= $cart_count ?></span>
    </a>

</div>

</header>


<!-- WISHLIST HEADER -->

<section class="shop-header">

    <span>FASHLADY</span>

    <h1>My Wishlist</h1>

    <p>
        Your favourite styles, saved in one place.
    </p>

</section>


<!-- WISHLIST PRODUCTS -->

<section class="shop-section">

    <div class="shop-products">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($product = $result->fetch_assoc()): ?>

                <div class="shop-product-card">

                    <div class="shop-product-image">

                        <?php if (!empty($product['badge'])): ?>

                            <span>
                                <?= htmlspecialchars($product['badge']) ?>
                            </span>

                        <?php endif; ?>


                        <?php if (!empty($product['image'])): ?>

                            <img
                                src="images/<?= htmlspecialchars($product['image']) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                            >

                        <?php else: ?>

                            <div>Product Image</div>

                        <?php endif; ?>

                    </div>


                    <div class="shop-product-info">

                        <small>
                            <?= htmlspecialchars($product['category']) ?>
                        </small>

                        <h3>
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>


                        <div class="product-pricing">

                            <strong>
                                ₹<?= number_format($product['price'], 0) ?>
                            </strong>


                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>

                                <del>
                                    ₹<?= number_format($product['old_price'], 0) ?>
                                </del>

                                <?php

                                $discount = round(
                                    (($product['old_price'] - $product['price'])
                                    / $product['old_price']) * 100
                                );

                                ?>

                                <span class="discount">
                                    <?= $discount ?>% OFF
                                </span>

                            <?php endif; ?>

                        </div>


                        <button class="shop-cart"
        type="button"
        onclick="addToCart(<?= (int)$product['id'] ?>)">
    ADD TO CART
</button>
<form action="remove_wishlist.php" method="POST"
      onsubmit="return confirm('Remove this product from wishlist?');">

    <input type="hidden"
           name="wishlist_id"
           value="<?= (int)$product['wishlist_id'] ?>">

    <button type="submit" class="remove-wishlist">
        REMOVE
    </button>

</form>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-products">

                <h3>Your wishlist is empty</h3>

                <p>
                    Save your favourite products here.
                </p>

                <a href="shop.php">
                    Continue Shopping
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>

<script>
function addToCart(productId) {

    const formData = new FormData();
    formData.append("product_id", productId);

    fetch("add_cart.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Product added to cart 🛒");
    })
    .catch(error => {
        alert("Something went wrong.");
    });
}
</script>
</body>
</html>

<?php
session_start();
require_once "config.php";

/* Product ID */
$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header("Location: shop.php");
    exit;
}

/* Get product */
$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shop.php");
    exit;
}

$product = $result->fetch_assoc();

$stmt->close();
/* Get product variants */

$variant_stmt = $conn->prepare("
    SELECT *
    FROM product_variants
    WHERE product_id = ?
    ORDER BY id ASC
");

$variant_stmt->bind_param("i", $product_id);
$variant_stmt->execute();

$variant_result = $variant_stmt->get_result();

$variants = [];

while ($variant = $variant_result->fetch_assoc()) {
    $variants[] = $variant;
}

$variant_stmt->close();

/* Discount */
$discount = 0;

if (
    !empty($product['old_price']) &&
    $product['old_price'] > $product['price']
) {
    $discount = round(
        (($product['old_price'] - $product['price'])
        / $product['old_price']) * 100
    );
}

/* Cart count */
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
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($product['name']) ?> | FASHLADY
    </title>

    <link rel="stylesheet"
          href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        .product-page {
            padding: 70px 7%;
            background: #fff9f5;
        }

        .product-container {
            max-width: 1150px;
            margin: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 70px;
            align-items: start;
        }

        .product-image-box {
            background: #f3dde1;
            border-radius: 18px;
            overflow: hidden;
            position: relative;
        }

        .product-image-box img {
            width: 100%;
            height: 650px;
            object-fit: cover;
            display: block;
        }

        .product-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #6B2638;
            color: white;
            padding: 8px 14px;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            z-index: 2;
        }

        .product-info {
            padding-top: 20px;
        }

        .product-category {
            color: #C9828F;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .product-info h1 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 42px;
            line-height: 1.2;
            margin: 15px 0;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 20px 0;
        }

        .product-price strong {
            color: #242124;
            font-size: 24px;
        }

        .product-price del {
            color: #999;
            font-size: 16px;
        }

        .product-discount {
            color: #6B2638;
            font-size: 12px;
            font-weight: 600;
        }

        .product-description {
            color: #666;
            line-height: 1.8;
            font-size: 15px;
            margin: 25px 0;
        }

        .product-divider {
            height: 1px;
            background: #eadde0;
            margin: 25px 0;
        }

        .quantity-title {
            color: #242124;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .quantity-box {
            display: flex;
            width: 130px;
            height: 42px;
            border: 1px solid #d9c4c8;
            margin-bottom: 22px;
        }

        .quantity-box button {
            width: 40px;
            border: none;
            background: white;
            color: #6B2638;
            font-size: 18px;
            cursor: pointer;
        }

        .quantity-box input {
            width: 50px;
            border: none;
            border-left: 1px solid #eadde0;
            border-right: 1px solid #eadde0;
            text-align: center;
            outline: none;
            font-family: "DM Sans", sans-serif;
        }

        .product-actions {
            display: flex;
            gap: 12px;
        }

        .add-cart-btn {
            flex: 1;
            padding: 15px;
            background: #6B2638;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.2px;
        }

        .add-cart-btn:hover {
            background: #C9828F;
        }

        .wishlist-btn {
            width: 55px;
            border: 1px solid #6B2638;
            background: white;
            color: #6B2638;
            cursor: pointer;
            font-size: 18px;
        }

        .wishlist-btn:hover {
            background: #6B2638;
            color: white;
        }

        .back-shop {
            display: inline-block;
            margin-top: 30px;
            color: #6B2638;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .back-shop:hover {
            color: #C9828F;
        }

        @media (max-width: 800px) {

            .product-page {
                padding: 40px 5%;
            }

            .product-container {
                grid-template-columns: 1fr;
                gap: 35px;
            }

            .product-image-box img {
                height: 500px;
            }

            .product-info h1 {
                font-size: 34px;
            }

        }
.color-options {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 12px;
}

.color-option {
    width: 42px;
    height: 42px;
    padding: 3px;
    border: 2px solid transparent;
    border-radius: 50%;
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.color-option:hover {
    border-color: #C9828F;
}

.color-option.active {
    border-color: #6B2638;
}

.color-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: block;
    border: 1px solid rgba(0, 0, 0, 0.12);
}

    </style>

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

            <a href="account.php"
               aria-label="Account"
               class="nav-icon">
                <i class="fa-regular fa-user"></i>
            </a>

        <?php else: ?>

            <a href="login.php"
               aria-label="Account"
               class="nav-icon">
                <i class="fa-regular fa-user"></i>
            </a>

        <?php endif; ?>

        <a href="wishlist.php"
           aria-label="Wishlist"
           class="nav-icon">
            <i class="fa-regular fa-heart"></i>
        </a>

        <a href="cart.php"
           aria-label="Cart"
           class="nav-icon">

            <i class="fa-solid fa-bag-shopping"></i>

            <span class="cart-count">
                <?= $cart_count ?>
            </span>

        </a>

    </div>

</header>


<!-- PRODUCT -->

<section class="product-page">

    <div class="product-container">


        <!-- IMAGE -->

        <div class="product-image-box">

            <?php if (!empty($product['badge'])): ?>

                <span class="product-badge">
                    <?= htmlspecialchars($product['badge']) ?>
                </span>

            <?php endif; ?>


            <?php if (!empty($product['image'])): ?>

                <img
                    src="images/<?= htmlspecialchars($product['image']) ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>"
                >

            <?php else: ?>

                <div style="padding:100px;text-align:center;">
                    Product Image
                </div>

            <?php endif; ?>

        </div>


        <!-- INFO -->

        <div class="product-info">

            <span class="product-category">
                <?= htmlspecialchars($product['category']) ?>
            </span>


            <h1>
                <?= htmlspecialchars($product['name']) ?>
            </h1>


            <div class="product-price">

                <strong>
                    ₹<?= number_format($product['price'], 0) ?>
                </strong>


                <?php if ($discount > 0): ?>

                    <del>
                        ₹<?= number_format($product['old_price'], 0) ?>
                    </del>

                    <span class="product-discount">
                        <?= $discount ?>% OFF
                    </span>

                <?php endif; ?>

            </div>


<!-- PRODUCT VARIANTS -->

<?php if (!empty($variants)): ?>

    <div class="variant-selection">

        <div class="quantity-title">
            COLOR
        </div>

        <div class="color-options">

            <?php foreach ($variants as $variant): ?>

              <button
    type="button"
    class="color-option"
    data-variant-id="<?= (int)$variant['id'] ?>"
    data-front="<?= htmlspecialchars($variant['image_front'] ?? '') ?>"
    data-back="<?= htmlspecialchars($variant['image_back'] ?? '') ?>"
    data-detail="<?= htmlspecialchars($variant['image_detail'] ?? '') ?>"
    onclick="selectVariant(this)"
    aria-label="<?= htmlspecialchars($variant['color_name']) ?>"
    title="<?= htmlspecialchars($variant['color_name']) ?>"
>
    <span
        class="color-dot"
        style="background: <?= htmlspecialchars($variant['color_code']) ?>;">
    </span>
</button>

            <?php endforeach; ?>

        </div>


        <div class="quantity-title" style="margin-top:20px;">
            SIZE
        </div>

        <select id="size" class="size-select">

            <option value="">
                Select Size
            </option>

        </select>

        <input
            type="hidden"
            id="variant_id"
            value="">

    </div>

<?php endif; ?>
            <div class="product-divider"></div>


            <p class="product-description">

                <?php if (!empty($product['description'])): ?>

                    <?= nl2br(htmlspecialchars($product['description'])) ?>

                <?php else: ?>

                    Discover elegant fashion designed for
                    the modern woman. Crafted with care and
                    designed for effortless everyday style.

                <?php endif; ?>

            </p>


            <div class="product-divider"></div>


            <!-- QUANTITY -->

            <div class="quantity-title">
                QUANTITY
            </div>

            <div class="quantity-box">

                <button type="button"
                        onclick="changeQuantity(-1)">
                    −
                </button>

                <input type="number"
                       id="quantity"
                       value="1"
                       min="1"
                       readonly>

                <button type="button"
                        onclick="changeQuantity(1)">
                    +
                </button>

            </div>


            <!-- ACTIONS -->

            <div class="product-actions">

                <button type="button"
                        class="add-cart-btn"
                        onclick="addToCart()">

                    ADD TO CART

                </button>


                <button type="button"
                        class="wishlist-btn"
                        onclick="addToWishlist()"
                        aria-label="Add to wishlist">

                    <i class="fa-regular fa-heart"></i>

                </button>

            </div>


            <a href="shop.php" class="back-shop">
                ← Back to Shop
            </a>

        </div>

    </div>

</section>


<script>

function changeQuantity(change) {

    const input = document.getElementById("quantity");

    let quantity = parseInt(input.value);

    quantity += change;

    if (quantity < 1) {
        quantity = 1;
    }

    input.value = quantity;
}
/* Select product variant */

function selectVariant(button) {

    const variantId = button.getAttribute("data-variant-id");

    document.getElementById("variant_id").value = variantId;

    // Remove previous selection
    document.querySelectorAll(".color-option").forEach(function(btn) {
        btn.classList.remove("active");
    });

    // Select current colour
    button.classList.add("active");

    // Get sizes from PHP
    const variants = <?= json_encode($variants) ?>;

    const selectedVariant = variants.find(function(variant) {
        return parseInt(variant.id) === parseInt(variantId);
    });
// Change product images according to selected colour

if (selectedVariant) {

    const mainImage = document.querySelector(".product-image-box img");

    if (mainImage && selectedVariant.image_front) {
        mainImage.src = "images/" + selectedVariant.image_front;
    }

}

    const sizeSelect = document.getElementById("size");

    sizeSelect.innerHTML = '<option value="">Select Size</option>';

    if (selectedVariant && selectedVariant.sizes) {

        const sizes = selectedVariant.sizes.split(",");

        sizes.forEach(function(size) {

            size = size.trim();

            if (size !== "") {

                const option = document.createElement("option");

                option.value = size;
                option.textContent = size;

                sizeSelect.appendChild(option);
            }
        });
    }
}

/* Add to cart */

function addToCart() {

    const quantity =
        parseInt(document.getElementById("quantity").value);

    const variantId =
        document.getElementById("variant_id").value;

    const size =
        document.getElementById("size").value;

    <?php if (!empty($variants)): ?>

    if (!variantId) {
        alert("Please select a colour.");
        return;
    }

    if (!size) {
        alert("Please select a size.");
        return;
    }

    <?php endif; ?>

    for (let i = 0; i < quantity; i++) {

        const formData = new FormData();

        formData.append(
            "product_id",
            <?= (int)$product['id'] ?>
        );

        formData.append(
            "variant_id",
            variantId
        );

        formData.append(
            "size",
            size
        );

        fetch("add_cart.php", {
            method: "POST",
            body: formData
        });
    }

    alert("Product added to cart 🛒");
}

/* Add to wishlist */

function addToWishlist() {

    const formData = new FormData();

    formData.append(
        "product_id",
        <?= (int)$product['id'] ?>
    );

    fetch("add_wishlist.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {

        alert("Product added to wishlist ❤️");

    })
    .catch(error => {

        alert("Something went wrong.");

    });

}

</script>

</body>
</html>
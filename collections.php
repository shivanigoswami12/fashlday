<?php
session_start();
require_once "config.php";

/* CART COUNT */
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

    if ($count_row = $count_result->fetch_assoc()) {
        $cart_count = (int)$count_row['cart_count'];
    }

    $count_stmt->close();
}

/* GET PRODUCTS */
$sql = "
    SELECT id, name, category, price, old_price, image, badge
    FROM products
    ORDER BY created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Collections | FASHLADY</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

    <style>

        /* ==============================
           COLLECTIONS PAGE
        ============================== */

        .collections-hero {
            background: #FFF9F5;
            text-align: center;
            padding: 75px 20px 65px;
        }

        .collections-hero span {
            color: #C9828F;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
        }

        .collections-hero h1 {
            font-family: "Playfair Display", serif;
            font-size: 52px;
            font-weight: 500;
            color: #242124;
            margin: 15px 0;
        }

        .collections-hero p {
            max-width: 600px;
            margin: auto;
            color: #666;
            line-height: 1.8;
            font-size: 15px;
        }

        /* COLLECTION CATEGORIES */

        .collection-links {
            padding: 55px 7%;
            background: #fff;
        }

        .collection-links h2 {
            text-align: center;
            font-family: "Playfair Display", serif;
            font-size: 34px;
            font-weight: 500;
            color: #242124;
            margin-bottom: 35px;
        }

        .collection-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
            max-width: 1250px;
            margin: auto;
        }

        .collection-box {
            padding: 22px 10px;
            text-align: center;
            background: #F3DDE1;
            text-decoration: none;
            color: #6B2638;
            transition: 0.3s ease;
        }

        .collection-box i {
            font-size: 22px;
            margin-bottom: 12px;
        }

        .collection-box h3 {
            font-family: "Playfair Display", serif;
            font-size: 17px;
            margin: 5px 0;
            font-weight: 500;
        }

        .collection-box p {
            font-size: 10px;
            color: #777;
            margin: 0;
        }

        .collection-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(107, 38, 56, 0.10);
        }

        /* PRODUCTS */

        .collection-products {
            padding: 65px 7%;
            background: #FFF9F5;
        }

        .collection-heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .collection-heading span {
            color: #C9828F;
            font-size: 11px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        .collection-heading h2 {
            font-family: "Playfair Display", serif;
            font-size: 38px;
            font-weight: 500;
            color: #242124;
            margin: 10px 0;
        }

        .collection-heading p {
            color: #777;
            font-size: 14px;
        }

        .collection-product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            max-width: 1250px;
            margin: auto;
        }

        .collection-product-card {
            background: #fff;
            position: relative;
            overflow: hidden;
            transition: 0.3s ease;
        }

        .collection-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .collection-product-image {
            width: 100%;
            aspect-ratio: 3 / 4;
            overflow: hidden;
            background: #f5eeee;
        }

        .collection-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .collection-product-card:hover img {
            transform: scale(1.04);
        }

        .collection-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #6B2638;
            color: #fff;
            padding: 6px 10px;
            font-size: 9px;
            letter-spacing: 1px;
        }

        .collection-product-info {
            padding: 18px;
        }

        .collection-product-info h3 {
            margin: 0 0 7px;
            font-family: "Playfair Display", serif;
            font-size: 19px;
            font-weight: 500;
        }

        .collection-product-info h3 a {
            color: #242124;
            text-decoration: none;
        }

        .collection-product-info h3 a:hover {
            color: #C9828F;
        }

        .collection-category {
            color: #999;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .collection-price {
            margin-top: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #6B2638;
        }

        .collection-old-price {
            color: #aaa;
            text-decoration: line-through;
            font-weight: 400;
            margin-left: 7px;
        }

        .collection-view-btn {
            display: block;
            width: fit-content;
            margin: 40px auto 0;
            padding: 13px 30px;
            background: #6B2638;
            color: #fff;
            text-decoration: none;
            font-size: 11px;
            letter-spacing: 2px;
            transition: 0.3s ease;
        }

        .collection-view-btn:hover {
            background: #C9828F;
        }

        /* FOOTER */

        .collections-footer {
            background: #6B2638;
            color: #fff;
            text-align: center;
            padding: 35px 20px;
        }

        .collections-footer h3 {
            font-family: "Playfair Display", serif;
            font-size: 25px;
            font-weight: 500;
            margin: 0 0 8px;
        }

        .collections-footer p {
            font-size: 12px;
            opacity: 0.8;
            margin: 0;
        }

        /* MOBILE */

        @media (max-width: 900px) {

            .collection-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .collection-product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .collections-hero {
                padding: 55px 20px 45px;
            }

            .collections-hero h1 {
                font-size: 38px;
            }

            .collection-links,
            .collection-products {
                padding: 45px 15px;
            }

            .collection-links h2,
            .collection-heading h2 {
                font-size: 30px;
            }

            .collection-grid {
                grid-template-columns: 1fr;
            }

            .collection-product-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

        }

    </style>
</head>

<body>

<!-- ================= NAVBAR ================= -->

<header class="navbar">

    <a href="index.php" class="logo">FASHLADY</a>

    <nav>
    <a href="index.php">Home</a>
    <a href="shop.php">Shop</a>
    <a href="collections.php" class="active">Collections</a>
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

            <?php if ($cart_count > 0): ?>
                <span class="cart-count">
                    <?= $cart_count ?>
                </span>
            <?php endif; ?>

        </a>

    </div>

</header>


<!-- ================= HERO ================= -->

<section class="collections-hero">

    <span>THE FASHLADY EDIT</span>

    <h1>Our Collections</h1>

    <p>
        Discover thoughtfully curated styles made for every mood,
        every moment and every version of you.
    </p>

</section>


<!-- ================= COLLECTION CATEGORIES ================= -->

<section class="collection-links">

    <h2>Explore The Edit</h2>

    <div class="collection-grid">

        <a href="shop.php?category=New%20Arrivals"
           class="collection-box">

            <i class="fa-regular fa-star"></i>

            <h3>New Arrivals</h3>

            <p>Fresh styles, just for you.</p>

        </a>


        <a href="shop.php?category=Ethnic%20Wear"
           class="collection-box">

            <i class="fa-solid fa-feather"></i>

            <h3>Ethnic Edit</h3>

            <p>Timeless Indian elegance.</p>

        </a>


        <a href="shop.php?category=Co-ords"
           class="collection-box">

            <i class="fa-solid fa-layer-group"></i>

            <h3>Co-ord Sets</h3>

            <p>Effortless matching looks.</p>

        </a>


        <a href="shop.php?category=Kurtis"
           class="collection-box">

            <i class="fa-solid fa-shirt"></i>

            <h3>Kurtis</h3>

            <p>Easy everyday elegance.</p>

        </a>

        <a href="shop.php?category=Tops"
           class="collection-box">

            <i class="fa-solid fa-shirt"></i>

            <h3>Tops</h3>

            <p>Chic everyday styles.</p>

        </a>

        <a href="shop.php?category=Kurta%20Sets"
           class="collection-box">

            <i class="fa-solid fa-layer-group"></i>

            <h3>Kurta Sets</h3>

            <p>Elegant coordinated looks.</p>

        </a>

    </div>

</section>


<!-- ================= PRODUCTS ================= -->

<section class="collection-products">

    <div class="collection-heading">

        <span>CURATED FOR YOU</span>

        <h2>Featured Styles</h2>

        <p>
            A selection of pieces from our latest collection.
        </p>

    </div>


    <div class="collection-product-grid">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($product = $result->fetch_assoc()): ?>

                <div class="collection-product-card">

                    <?php if (!empty($product['badge'])): ?>

                        <span class="collection-badge">
                            <?= htmlspecialchars($product['badge']) ?>
                        </span>

                    <?php endif; ?>


                    <div class="collection-product-image">

                        <a href="product.php?id=<?= (int)$product['id'] ?>">

                            <?php if (!empty($product['image'])): ?>

                                <img
                                    src="images/<?= htmlspecialchars($product['image']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                >

                            <?php else: ?>

                                <img
                                    src="images/placeholder.jpg"
                                    alt="FASHLADY Product"
                                >

                            <?php endif; ?>

                        </a>

                    </div>


                    <div class="collection-product-info">

                        <div class="collection-category">
                            <?= htmlspecialchars($product['category']) ?>
                        </div>

                        <h3>
                            <a href="product.php?id=<?= (int)$product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h3>


                        <div class="collection-price">

                            ₹<?= number_format($product['price'], 0) ?>

                            <?php if (!empty($product['old_price'])): ?>

                                <span class="collection-old-price">
                                    ₹<?= number_format($product['old_price'], 0) ?>
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p style="grid-column:1/-1; text-align:center;">
                No products available yet.
            </p>

        <?php endif; ?>

    </div>


    <a href="shop.php" class="collection-view-btn">
        VIEW ALL PRODUCTS
    </a>

</section>


<!-- ================= FOOTER ================= -->

<footer class="collections-footer">

    <h3>FASHLADY</h3>

    <p>
        Fashion that feels like you.
    </p>

</footer>


</body>
</html>
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FASHLADY | Fashion for Every Woman</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .welcome-user {
            color: #6B2638;
            font-size: 13px;
            font-weight: 500;
            margin-left: 18px;
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            .welcome-user {
                font-size: 12px;
                margin-left: 10px;
            }
        }


        /* FASHLADY FINAL HERO */
        .fashlady-hero {
            width: 100%;
            min-height: 560px;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
           background: #FFF9F5 url("images/fashlady_hero_final.jpg") 70% center / cover no-repeat;
        }

        .fashlady-hero .hero-content {
    width: 52%;
    max-width: 650px;
    padding: 75px 0 75px 4%;
}

        .fashlady-hero .hero-description {
            max-width: 500px;
        }

        @media (max-width: 900px) {
            .fashlady-hero {
                min-height: 500px;
                background-position: 60% center;
            }

            .fashlady-hero .hero-content {
                width: 58%;
                padding-left: 5%;
            }
        }

        @media (max-width: 600px) {
            .fashlady-hero {
                min-height: 600px;
                align-items: flex-start;
                background-position: 68% center;
            }

            .fashlady-hero .hero-content {
                width: 100%;
                padding: 55px 25px;
                background: linear-gradient(
                    90deg,
                    rgba(255,249,245,.96) 0%,
                    rgba(255,249,245,.82) 55%,
                    rgba(255,249,245,0) 100%
                );
            }
        }
/* FEATURED PRODUCT IMAGE FIX */
.featured-section .product-image-link {
    display: block;
    width: 100%;
    height: 420px;
    overflow: hidden;
}

.featured-section .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

@media (max-width: 600px) {
    .featured-section .product-image-link {
        height: 360px;
    }
}

    </style>

</head>

<body>

<!-- ================= NAVBAR ================= -->

<header class="navbar">

    <div class="logo">
        FASH<span>LADY</span>
    </div>

    <nav>
        <a href="index.php" class="active">Home</a>
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

    <?php if (isset($_SESSION['user_name'])): ?>

        <span class="welcome-user">
            Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>
        </span>

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


<!-- ================= HERO ================= -->

<section class="hero fashlady-hero">

    <div class="hero-content">

        <p class="eyebrow">NEW COLLECTION 2026</p>

        <h1>
            Elegance<br>
            <em>Made For You.</em>
        </h1>

        <p class="hero-description">
            Discover timeless ethnic wear, modern co-ords
            and effortlessly beautiful styles designed for
            every woman.
        </p>

        <div class="hero-buttons">
            <a href="collections.php" class="primary-btn">
                Shop Collection
                <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="shop.php" class="secondary-btn">
                Explore More
            </a>
        </div>

    </div>

</section>

<!-- SHOP BY CATEGORY -->
<section class="categories-section">
    <div class="section-heading">
        <span>EXPLORE OUR COLLECTION</span>
        <h2>Shop By Category</h2>
        <p>Find your perfect style for every occasion.</p>
    </div>

    <div class="category-grid">

        <a href="#" class="category-card">
            <div class="category-placeholder">👗</div>
            <h3>Ethnic Wear</h3>
            <p>Elegant traditional styles</p>
        </a>

        <a href="#" class="category-card">
            <div class="category-placeholder">✨</div>
            <h3>Co-ords</h3>
            <p>Modern & effortless looks</p>
        </a>

        <a href="#" class="category-card">
            <div class="category-placeholder">🌸</div>
            <h3>Kurtis</h3>
            <p>Everyday beautiful styles</p>
        </a>

        <a href="#" class="category-card">
            <div class="category-placeholder">💫</div>
            <h3>New Arrivals</h3>
            <p>Fresh styles you'll love</p>
        </a>

    </div>
</section>


<!-- FEATURED COLLECTION -->
<section class="featured-section">

    <div class="section-heading">
        <span>FASHLADY EDIT</span>
        <h2>Featured Collection</h2>
        <p>Our most loved styles, selected for you.</p>
    </div>

    <div class="product-grid">

        <?php
$featured_sql = "
    SELECT id, name, price, old_price, image, badge
    FROM products
    ORDER BY id DESC
    LIMIT 4
";

$featured_result = $conn->query($featured_sql);

if ($featured_result && $featured_result->num_rows > 0):
    while ($product = $featured_result->fetch_assoc()):
?>

    <div class="product-card">

        <a href="product.php?id=<?= $product['id'] ?>" class="product-image-link">

            <?php if (!empty($product['image'])): ?>

                <img
                    src="images/<?= htmlspecialchars($product['image']) ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    class="product-image"
                >

            <?php else: ?>

                <div class="product-placeholder">
                    Product Image
                </div>

            <?php endif; ?>

        </a>

        <div class="product-info">

            <?php if (!empty($product['badge'])): ?>
                <span><?= htmlspecialchars($product['badge']) ?></span>
            <?php endif; ?>

            <h3>
                <a href="product.php?id=<?= $product['id'] ?>">
                    <?= htmlspecialchars($product['name']) ?>
                </a>
            </h3>

            <p class="price">
                ₹<?= number_format($product['price'], 0) ?>

                <?php if (!empty($product['old_price'])): ?>
                    <del>₹<?= number_format($product['old_price'], 0) ?></del>
                <?php endif; ?>
            </p>

          <a
    href="product.php?id=<?= $product['id'] ?>"
    class="add-cart-btn">
    Select Options
</a>

        </div>

    </div>

<?php
    endwhile;
else:
?>

    <p style="text-align:center;">
        No featured products available.
    </p>

<?php endif; ?>

    </div>

</section>


<!-- ================= FEATURES ================= -->

<section class="features">

    <div class="feature">
        <i class="fa-solid fa-truck-fast"></i>
        <div>
            <h4>Fast Delivery</h4>
            <p>Quick & reliable shipping</p>
        </div>
    </div>

    <div class="feature">
        <i class="fa-solid fa-rotate-left"></i>
        <div>
            <h4>Easy Returns</h4>
            <p>Simple return experience</p>
        </div>
    </div>

    <div class="feature">
        <i class="fa-solid fa-shield-halved"></i>
        <div>
            <h4>Secure Payment</h4>
            <p>100% safe checkout</p>
        </div>
    </div>

    <div class="feature">
        <i class="fa-solid fa-heart"></i>
        <div>
            <h4>Premium Quality</h4>
            <p>Styles you'll love</p>
        </div>
    </div>

</section>
<!-- OFFER BANNER -->
<section class="offer-banner">
    <div>
        <span>LIMITED TIME OFFER</span>
        <h2>Get 10% Off On Your First Order</h2>
        <p>Discover your new favourite style with FASHLADY.</p>
        <a href="shop.php"class="offer-btn">SHOP NOW</a>
    </div>
</section>


<!-- WHY FASHLADY -->
<section class="why-section">

    <div class="section-heading">
        <span>WHY FASHLADY</span>
        <h2>Made For The Modern Woman</h2>
        <p>Everything you need for a beautiful shopping experience.</p>
    </div>

    <div class="why-grid">

        <div class="why-card">
            <div>✦</div>
            <h3>Premium Quality</h3>
            <p>Beautiful styles selected with quality and comfort in mind.</p>
        </div>

        <div class="why-card">
            <div>♡</div>
            <h3>Made With Love</h3>
            <p>Every collection is chosen to make you feel confident.</p>
        </div>

        <div class="why-card">
            <div>↗</div>
            <h3>Easy Shopping</h3>
            <p>A simple and smooth shopping experience for you.</p>
        </div>

        <div class="why-card">
            <div>✓</div>
            <h3>Trusted Service</h3>
            <p>Fast delivery and helpful customer support.</p>
        </div>

    </div>
</section>


<!-- NEWSLETTER -->
<section class="newsletter">
    <div>
        <span>STAY IN STYLE</span>
        <h2>Join The FASHLADY Family</h2>
        <p>Get updates about new collections and exclusive offers.</p>

        <div class="newsletter-form">
            <input type="email" placeholder="Enter your email address">
            <button>SUBSCRIBE</button>
        </div>
    </div>
</section>

<!-- PREMIUM DARK FOOTER -->
<footer class="premium-footer">

    <div class="footer-main">

        <!-- BRAND -->
        <div class="footer-brand-new">
            <h2>FASHLADY</h2>
            <p class="footer-tagline">Elegance made for you.</p>

            <p class="footer-description">
                Timeless ethnic wear, modern co-ords
                and everyday styles for the modern woman.
            </p>

            <div class="social-icons">
                <a href="#">◎</a>
                <a href="#">f</a>
                <a href="#">p</a>
                <a href="#">◉</a>
            </div>
        </div>


        <!-- SHOP -->
        <div class="footer-column">
            <h4>SHOP</h4>
            <a href="shop.php">All Products</a>
<a href="shop.php?category=New%20Arrivals">New Arrivals</a>
<a href="shop.php?category=Ethnic%20Wear">Ethnic Wear</a>
<a href="shop.php?category=Co-ords">Co-ords</a>
<a href="shop.php?category=Kurtis">Kurtis</a>
<a href="shop.php?category=Tops">Tops</a>
<a href="shop.php?category=Kurta%20Sets">Kurta Sets</a>
        </div>


        <!-- HELP -->
        <div class="footer-column">
            <h4>HELP</h4>
            <a href="contact.php">Contact Us</a>
            <a href="shipping.php">Shipping & Delivery</a>
            <a href="returns.php">Returns</a>
           <a href="faq.php">FAQs</a>
            <a href="size-guide.php">Size Guide</a>
        </div>


        <!-- MORE -->
        <div class="footer-column">
            <h4>MORE</h4>
            <a href="about.php">About Us</a>
           <a href="about.php">Our Story</a>
           <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms & Conditions</a>
        </div>


        <!-- SIDE MESSAGE -->
        <div class="footer-message">
            <p>For the</p>
            <strong>Modern Woman</strong>
            <span>♡</span>
        </div>

    </div>


    <div class="footer-divider"></div>


    <div class="footer-bottom-new">

        <span>© 2026 FASHLADY. All Rights Reserved.</span>

        <span class="footer-heart">♡</span>

        <span>Made with ♡ for modern women.</span>

    </div>

</footer>
<script>
/* ================= HERO SLIDER JS ================= */

let currentHeroSlide = 0;
let heroTimer;

function showHeroSlide(index) {

    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".hero-dots button");

    if (!slides.length) return;

    if (index >= slides.length) {
        index = 0;
    }

    if (index < 0) {
        index = slides.length - 1;
    }

    slides.forEach(function(slide, i) {
        slide.classList.toggle("active", i === index);
    });

    dots.forEach(function(dot, i) {
        dot.classList.toggle("active", i === index);
    });

    currentHeroSlide = index;
}

function changeHeroSlide(direction) {
    showHeroSlide(currentHeroSlide + direction);
    startHeroSlider();
}

function goToHeroSlide(index) {
    showHeroSlide(index);
    startHeroSlider();
}

function startHeroSlider() {

    clearInterval(heroTimer);

    heroTimer = setInterval(function() {
        showHeroSlide(currentHeroSlide + 1);
    }, 5000);
}

document.addEventListener("DOMContentLoaded", function() {

    showHeroSlide(0);
    startHeroSlider();

});
</script>
</body>
</html>
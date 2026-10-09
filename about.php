<?php
session_start();
require_once "config.php";

/* Cart Count */
$cart_count = 0;

if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(quantity), 0) AS cart_count
         FROM cart
         WHERE user_id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {
        $row = $result->fetch_assoc();
        $cart_count = (int)$row['cart_count'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        /* ================= ABOUT HERO ================= */

        .about-hero-new {
            min-height: 520px;
            background:
                linear-gradient(
                    90deg,
                    rgba(255,249,245,0.98) 0%,
                    rgba(255,249,245,0.88) 48%,
                    rgba(243,221,225,0.55) 100%
                );
            display: flex;
            align-items: center;
            padding: 70px 8%;
            box-sizing: border-box;
            overflow: hidden;
        }

        .about-hero-content-new {
            width: 55%;
            z-index: 2;
        }

        .about-label {
            color: #C9828F;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            margin-bottom: 18px;
        }

        .about-hero-content-new h1 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 64px;
            line-height: 1.08;
            margin: 0 0 22px;
        }

        .about-hero-content-new h1 em {
            color: #C9828F;
            font-style: italic;
        }

        .about-hero-content-new p {
            color: #666;
            font-size: 16px;
            line-height: 1.8;
            max-width: 570px;
            margin-bottom: 30px;
        }

        .about-hero-btn {
            display: inline-block;
            padding: 14px 28px;
            background: #6B2638;
            color: white;
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 1.5px;
            transition: 0.3s;
        }

        .about-hero-btn:hover {
            background: #4B2933;
            transform: translateY(-2px);
        }


        /* ================= HERO DECORATION ================= */

        .about-hero-visual {
            width: 45%;
            min-height: 430px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .about-arch {
            width: 310px;
            height: 390px;
            border-radius: 170px 170px 0 0;
            background: #F3DDE1;
            position: relative;
        }

        .about-arch::before {
            content: "";
            position: absolute;
            width: 250px;
            height: 330px;
            background: #C9828F;
            opacity: 0.18;
            border-radius: 140px 140px 0 0;
            top: 30px;
            left: 30px;
        }

        .about-fashion-icon {
            position: absolute;
            z-index: 2;
            font-size: 100px;
            color: #6B2638;
        }

        .about-flower {
            position: absolute;
            color: #C9828F;
            font-size: 28px;
        }

        .flower-one {
            top: 45px;
            right: 50px;
        }

        .flower-two {
            bottom: 65px;
            left: 20px;
        }

        .flower-three {
            top: 130px;
            right: 0;
        }


        /* ================= STORY ================= */

        .about-story-new {
            max-width: 1150px;
            margin: auto;
            padding: 100px 25px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 75px;
            align-items: center;
        }

        .story-image-box {
            min-height: 430px;
            background: #F3DDE1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .story-image-box::after {
            content: "FASHLADY";
            position: absolute;
            bottom: 25px;
            right: 25px;
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 25px;
            opacity: 0.35;
        }

        .story-image-box i {
            font-size: 95px;
            color: #6B2638;
        }

        .story-content-new .about-label {
            margin-bottom: 12px;
        }

        .story-content-new h2 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 45px;
            line-height: 1.2;
            margin: 0 0 22px;
        }

        .story-content-new p {
            color: #666;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 15px;
        }


        /* ================= VALUES ================= */

        .values-section-new {
            background: #FFF9F5;
            padding: 90px 25px;
        }

        .values-heading-new {
            text-align: center;
            max-width: 650px;
            margin: auto auto 50px;
        }

        .values-heading-new h2 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 44px;
            margin: 10px 0 15px;
        }

        .values-heading-new p {
            color: #777;
            line-height: 1.7;
        }

        .values-grid-new {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .value-card-new {
            background: white;
            padding: 40px 25px;
            text-align: center;
            border: 1px solid #ead9dc;
            transition: 0.3s;
        }

        .value-card-new:hover {
            transform: translateY(-7px);
            box-shadow: 0 15px 35px rgba(107,38,56,0.10);
        }

        .value-card-new i {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: #F3DDE1;
            color: #6B2638;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 18px;
        }

        .value-card-new h3 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 23px;
            margin: 5px 0 12px;
        }

        .value-card-new p {
            color: #777;
            font-size: 14px;
            line-height: 1.7;
        }


        /* ================= PHILOSOPHY ================= */

        .philosophy-new {
            background: #6B2638;
            color: white;
            padding: 95px 20px;
            text-align: center;
        }

        .philosophy-new .about-label {
            color: #F3DDE1;
        }

        .philosophy-new h2 {
            max-width: 850px;
            margin: 10px auto 20px;
            font-family: "Playfair Display", serif;
            font-size: 46px;
            line-height: 1.3;
        }

        .philosophy-new p {
            max-width: 650px;
            margin: auto;
            color: #F3DDE1;
            line-height: 1.8;
            font-size: 15px;
        }


        /* ================= CTA ================= */

        .about-cta-new {
            padding: 90px 20px;
            text-align: center;
            background: #F3DDE1;
        }

        .about-cta-new h2 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 45px;
            margin: 0 0 12px;
        }

        .about-cta-new p {
            color: #666;
            margin-bottom: 28px;
        }

        .about-cta-new a {
            display: inline-block;
            padding: 15px 32px;
            background: #6B2638;
            color: white;
            text-decoration: none;
            font-size: 12px;
            letter-spacing: 1.5px;
        }

        .about-cta-new a:hover {
            background: #4B2933;
        }


        /* ================= MOBILE ================= */

        @media (max-width: 768px) {

            .about-hero-new {
                flex-direction: column;
                text-align: center;
                padding: 55px 20px;
            }

            .about-hero-content-new {
                width: 100%;
            }

            .about-hero-content-new h1 {
                font-size: 43px;
            }

            .about-hero-content-new p {
                font-size: 14px;
            }

            .about-hero-visual {
                width: 100%;
                min-height: 330px;
                margin-top: 35px;
            }

            .about-arch {
                width: 245px;
                height: 310px;
            }

            .about-fashion-icon {
                font-size: 75px;
            }

            .about-story-new {
                grid-template-columns: 1fr;
                gap: 45px;
                padding: 65px 20px;
            }

            .story-image-box {
                min-height: 320px;
            }

            .story-content-new h2 {
                font-size: 35px;
            }

            .values-section-new {
                padding: 65px 20px;
            }

            .values-heading-new h2 {
                font-size: 35px;
            }

            .values-grid-new {
                grid-template-columns: 1fr;
            }

            .philosophy-new {
                padding: 70px 20px;
            }

            .philosophy-new h2 {
                font-size: 34px;
            }

            .about-cta-new {
                padding: 65px 20px;
            }

            .about-cta-new h2 {
                font-size: 34px;
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

        <a href="index.php">Home</a>

        <a href="shop.php">Shop</a>

        <a href="collections.php">Collections</a>

        <a href="about.php" class="active">About</a>

        <a href="contact.php">Contact</a>

    </nav>


    <div class="nav-icons">

        <a href="#" class="nav-icon" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>


        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="account.php" class="nav-icon" aria-label="Account">
                <i class="fa-regular fa-user"></i>
            </a>

        <?php else: ?>

            <a href="login.php" class="nav-icon" aria-label="Account">
                <i class="fa-regular fa-user"></i>
            </a>

        <?php endif; ?>


        <a href="wishlist.php" class="nav-icon" aria-label="Wishlist">
            <i class="fa-regular fa-heart"></i>
        </a>


        <a href="cart.php" class="nav-icon" aria-label="Cart">

            <i class="fa-solid fa-bag-shopping"></i>

            <span class="cart-count">
                <?= $cart_count ?>
            </span>

        </a>

    </div>

</header>


<!-- ================= HERO ================= -->

<section class="about-hero-new">

    <div class="about-hero-content-new">

        <div class="about-label">
            ABOUT FASHLADY
        </div>

        <h1>
            Fashion That Feels
            <em>Like You.</em>
        </h1>

        <p>
            FASHLADY is a modern fashion destination
            created for women who love elegance,
            confidence and effortless style.
        </p>

        <a href="shop.php" class="about-hero-btn">
            EXPLORE COLLECTION
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>


    <div class="about-hero-visual">

        <div class="about-arch"></div>

        <i class="fa-solid fa-shirt about-fashion-icon"></i>

        <i class="fa-solid fa-leaf about-flower flower-one"></i>

        <i class="fa-solid fa-seedling about-flower flower-two"></i>

        <i class="fa-solid fa-leaf about-flower flower-three"></i>

    </div>

</section>


<!-- ================= STORY ================= -->

<section class="about-story-new">

    <div class="story-image-box">

        <i class="fa-solid fa-shirt"></i>

    </div>


    <div class="story-content-new">

        <div class="about-label">
            OUR STORY
        </div>

        <h2>
            Where Style Meets
            Simplicity.
        </h2>

        <p>
            FASHLADY began with one simple thought:
            every woman deserves to feel beautiful
            in what she wears.
        </p>

        <p>
            We bring together elegant ethnic wear,
            modern co-ords and everyday fashion
            designed to fit effortlessly into your life.
        </p>

        <p>
            From special occasions to everyday moments,
            our collections are chosen to help you
            express your own unique style.
        </p>

    </div>

</section>


<!-- ================= VALUES ================= -->

<section class="values-section-new">

    <div class="values-heading-new">

        <div class="about-label">
            WHY FASHLADY
        </div>

        <h2>
            Made With You In Mind
        </h2>

        <p>
            Beautiful fashion, thoughtful choices
            and a shopping experience designed around you.
        </p>

    </div>


    <div class="values-grid-new">

        <div class="value-card-new">

            <i class="fa-solid fa-gem"></i>

            <h3>
                Premium Quality
            </h3>

            <p>
                Carefully selected styles with
                quality, comfort and beautiful details.
            </p>

        </div>


        <div class="value-card-new">

            <i class="fa-regular fa-heart"></i>

            <h3>
                Made With Love
            </h3>

            <p>
                Every collection is chosen to make
                you feel confident and special.
            </p>

        </div>


        <div class="value-card-new">

            <i class="fa-solid fa-wand-magic-sparkles"></i>

            <h3>
                Effortless Style
            </h3>

            <p>
                Timeless pieces that make dressing
                beautifully feel simple.
            </p>

        </div>

    </div>

</section>


<!-- ================= PHILOSOPHY ================= -->

<section class="philosophy-new">

    <div class="about-label">
        OUR PHILOSOPHY
    </div>

    <h2>
        “Your style should feel
        completely yours.”
    </h2>

    <p>
        We believe fashion is more than clothing.
        It is confidence, expression and the little
        details that make you feel like yourself.
    </p>

</section>


<!-- ================= CTA ================= -->

<section class="about-cta-new">

    <h2>
        Find Something Beautiful
    </h2>

    <p>
        Explore the latest FASHLADY collection.
    </p>

    <a href="shop.php">
        SHOP NOW
        <i class="fa-solid fa-arrow-right"></i>
    </a>

</section>


<!-- ================= FOOTER ================= -->

<footer class="premium-footer">

    <div class="footer-main">

        <div class="footer-brand-new">

            <h2>FASHLADY</h2>

            <p class="footer-tagline">
                Elegance made for you.
            </p>

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


        <div class="footer-column">

            <h4>SHOP</h4>

            <a href="shop.php">All Products</a>
            <a href="#">New Arrivals</a>
            <a href="#">Ethnic Wear</a>
            <a href="#">Co-ords</a>
            <a href="#">Kurtis</a>

        </div>


        <div class="footer-column">

            <h4>HELP</h4>

            <a href="#">Contact Us</a>
            <a href="#">Shipping & Delivery</a>
            <a href="#">Returns</a>
            <a href="#">FAQs</a>
            <a href="#">Size Guide</a>

        </div>


        <div class="footer-column">

            <h4>MORE</h4>

            <a href="about.php">About Us</a>
            <a href="#">Our Story</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms & Conditions</a>

        </div>


        <div class="footer-message">

            <p>For the</p>

            <strong>Modern Woman</strong>

            <span>♡</span>

        </div>

    </div>


    <div class="footer-divider"></div>


    <div class="footer-bottom-new">

        <span>
            © 2026 FASHLADY. All Rights Reserved.
        </span>

        <span class="footer-heart">
            ♡
        </span>

        <span>
            Made with ♡ for modern women.
        </span>

    </div>

</footer>


</body>
</html>
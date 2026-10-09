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
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Contact | FASHLADY</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet"
          href="css/style.css">

    <style>

        /* =========================
           CONTACT HERO
        ========================= */

        .contact-hero {
            background: #FFF9F5;
            text-align: center;
            padding: 75px 20px 65px;
        }

        .contact-hero span {
            color: #C9828F;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
        }

        .contact-hero h1 {
            font-family: "Playfair Display", serif;
            font-size: 52px;
            font-weight: 500;
            color: #242124;
            margin: 15px 0;
        }

        .contact-hero p {
            max-width: 600px;
            margin: auto;
            color: #666;
            line-height: 1.8;
            font-size: 15px;
        }


        /* =========================
           CONTACT SECTION
        ========================= */

        .contact-section {
            background: #fff;
            padding: 70px 7%;
        }

        .contact-container {
            max-width: 1150px;
            margin: auto;

            display: grid;
            grid-template-columns: 0.85fr 1.15fr;

            gap: 60px;
            align-items: start;
        }


        /* =========================
           CONTACT INFO
        ========================= */

        .contact-info {
            padding: 15px 0;
        }

        .contact-info span {
            color: #C9828F;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
        }

        .contact-info h2 {
            font-family: "Playfair Display", serif;
            font-size: 38px;
            font-weight: 500;
            color: #242124;
            margin: 12px 0 18px;
        }

        .contact-info > p {
            color: #777;
            font-size: 14px;
            line-height: 1.8;
            max-width: 430px;
        }


        /* CONTACT DETAILS */

        .contact-details {
            margin-top: 35px;
        }

        .contact-detail {
            display: flex;
            align-items: flex-start;
            gap: 17px;
            margin-bottom: 25px;
        }

        .contact-detail-icon {
            width: 42px;
            height: 42px;

            background: #F3DDE1;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #6B2638;
            flex-shrink: 0;
        }

        .contact-detail h4 {
            margin: 0 0 5px;

            font-family: "Playfair Display", serif;
            font-size: 17px;
            font-weight: 500;

            color: #242124;
        }

        .contact-detail p {
            margin: 0;

            color: #777;
            font-size: 13px;
            line-height: 1.6;
        }


        /* SOCIAL ICONS */

        .contact-social {
            margin-top: 35px;
        }

        .contact-social a {
            width: 38px;
            height: 38px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            margin-right: 8px;

            background: #FFF9F5;
            color: #6B2638;

            text-decoration: none;

            transition: 0.3s ease;
        }

        .contact-social a:hover {
            background: #6B2638;
            color: #fff;
        }


        /* =========================
           CONTACT FORM
        ========================= */

        .contact-form-box {
            background: #FFF9F5;
            padding: 45px;
        }

        .contact-form-box h3 {
            font-family: "Playfair Display", serif;
            font-size: 30px;
            font-weight: 500;

            color: #242124;

            margin: 0 0 8px;
        }

        .contact-form-box > p {
            color: #888;
            font-size: 13px;
            margin-bottom: 30px;
        }


        .contact-form {
            display: grid;
            gap: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .contact-form label {
            display: block;

            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;

            color: #555;

            margin-bottom: 7px;
        }

        .contact-form input,
        .contact-form textarea {

            width: 100%;
            box-sizing: border-box;

            border: 1px solid #e5d8d8;

            background: #fff;

            padding: 13px 14px;

            font-family: "DM Sans", sans-serif;
            font-size: 13px;

            outline: none;

            transition: 0.3s ease;
        }

        .contact-form input {
            height: 45px;
        }

        .contact-form textarea {
            min-height: 130px;
            resize: vertical;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            border-color: #C9828F;
        }


        .contact-submit {
            width: 100%;

            border: none;

            padding: 14px;

            background: #6B2638;
            color: #fff;

            font-size: 11px;
            font-weight: 600;

            letter-spacing: 2px;

            cursor: pointer;

            transition: 0.3s ease;
        }

        .contact-submit:hover {
            background: #C9828F;
        }


        /* =========================
           FOOTER
        ========================= */

        .contact-footer {
            background: #6B2638;
            color: #fff;

            text-align: center;

            padding: 35px 20px;
        }

        .contact-footer h3 {
            font-family: "Playfair Display", serif;
            font-size: 25px;
            font-weight: 500;

            margin: 0 0 8px;
        }

        .contact-footer p {
            font-size: 12px;
            opacity: 0.8;
            margin: 0;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 800px) {

            .contact-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

        }


        @media (max-width: 600px) {

            .contact-hero {
                padding: 55px 20px 45px;
            }

            .contact-hero h1 {
                font-size: 38px;
            }

            .contact-section {
                padding: 45px 15px;
            }

            .contact-info h2 {
                font-size: 30px;
            }

            .contact-form-box {
                padding: 30px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 18px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<header class="navbar">

    <a href="index.php" class="logo">
        FASHLADY
    </a>


    <nav>
    <a href="index.php">Home</a>
    <a href="shop.php">Shop</a>
    <a href="collections.php">Collections</a>
    <a href="about.php">About</a>
    <a href="contact.php" class="active">Contact</a>
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

            <?php if ($cart_count > 0): ?>

                <span class="cart-count">
                    <?= $cart_count ?>
                </span>

            <?php endif; ?>

        </a>

    </div>

</header>



<!-- =========================
     HERO
========================= -->

<section class="contact-hero">

    <span>WE'D LOVE TO HEAR FROM YOU</span>

    <h1>Get In Touch</h1>

    <p>
        Have a question about an order, a product or anything FASHLADY?
        We're here to help.
    </p>

</section>



<!-- =========================
     CONTACT SECTION
========================= -->

<section class="contact-section">

    <div class="contact-container">


        <!-- CONTACT INFORMATION -->

        <div class="contact-info">

            <span>CONTACT FASHLADY</span>

            <h2>
                Let's talk
            </h2>

            <p>
                Whether you need help choosing the perfect outfit
                or have a question about your order, feel free to
                reach out to us.
            </p>


            <div class="contact-details">


                <div class="contact-detail">

                    <div class="contact-detail-icon">
                        <i class="fa-regular fa-envelope"></i>
                    </div>

                    <div>

                        <h4>Email</h4>

                        <p>
                            support@fashlady.com
                        </p>

                    </div>

                </div>



                <div class="contact-detail">

                    <div class="contact-detail-icon">
                        <i class="fa-solid fa-phone"></i>
                    </div>

                    <div>

                        <h4>Phone</h4>

                        <p>
                            +91 XXXXX XXXXX
                        </p>

                    </div>

                </div>



                <div class="contact-detail">

                    <div class="contact-detail-icon">
                        <i class="fa-regular fa-clock"></i>
                    </div>

                    <div>

                        <h4>Working Hours</h4>

                        <p>
                            Monday – Saturday<br>
                            10:00 AM – 6:00 PM
                        </p>

                    </div>

                </div>

            </div>


            <!-- SOCIAL -->

            <div class="contact-social">

                <a href="#" aria-label="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>

                <a href="#" aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="#" aria-label="Pinterest">
                    <i class="fa-brands fa-pinterest-p"></i>
                </a>

            </div>

        </div>



        <!-- CONTACT FORM -->

        <div class="contact-form-box">

            <h3>Send us a message</h3>

            <p>
                Fill in the details below and we'll get back to you.
            </p>


            <form class="contact-form"
                  action="#"
                  method="post">


                <div class="form-row">

                    <div>

                        <label for="name">
                            Your Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your name"
                            required
                        >

                    </div>


                    <div>

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            required
                        >

                    </div>

                </div>



                <div>

                    <label for="subject">
                        Subject
                    </label>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        placeholder="How can we help?"
                        required
                    >

                </div>



                <div>

                    <label for="message">
                        Message
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        placeholder="Write your message here..."
                        required
                    ></textarea>

                </div>



                <button
                    type="submit"
                    class="contact-submit">

                    SEND MESSAGE

                </button>

            </form>

        </div>

    </div>

</section>



<!-- =========================
     FOOTER
========================= -->

<footer class="contact-footer">

    <h3>FASHLADY</h3>

    <p>
        Fashion that feels like you.
    </p>

</footer>


</body>

</html>
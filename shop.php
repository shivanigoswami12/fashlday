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

$category = $_GET['category'] ?? 'All';
$sort = $_GET['sort'] ?? 'newest';
$search = trim($_GET['search'] ?? '');
if ($sort === 'low') {
    $orderBy = "price ASC";
} elseif ($sort === 'high') {
    $orderBy = "price DESC";
} else {
    $orderBy = "id DESC";
}

if ($category === 'All') {

    if ($search !== '') {
        $searchTerm = "%" . $search . "%";
        $stmt = $conn->prepare(
            "SELECT * FROM products
             WHERE name LIKE ? OR category LIKE ?
             ORDER BY $orderBy"
        );
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query(
            "SELECT * FROM products ORDER BY $orderBy"
        );
    }

} else {

    if ($search !== '') {
        $searchTerm = "%" . $search . "%";
        $stmt = $conn->prepare(
            "SELECT * FROM products
             WHERE category = ?
             AND (name LIKE ? OR category LIKE ?)
             ORDER BY $orderBy"
        );
        $stmt->bind_param("sss", $category, $searchTerm, $searchTerm);
    } else {
        $stmt = $conn->prepare(
            "SELECT * FROM products
             WHERE category = ?
             ORDER BY $orderBy"
        );
        $stmt->bind_param("s", $category);
    }

    $stmt->execute();
    $result = $stmt->get_result();
}

if (!$result) {
    die("Database error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shop | FASHLADY</title>

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
        <a href="shop.php" class="active">Shop</a>
        <a href="collections.php">Collections</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>
<div class="nav-icons">

    <button type="button" aria-label="Search" class="nav-icon search-toggle"
            onclick="toggleSearch()">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

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

<!-- SEARCH PANEL -->
<div class="shop-search-panel" id="shopSearchPanel">
    <form action="shop.php" method="GET" class="shop-search-form">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Search products..." autocomplete="off">
        <?php if ($category !== 'All'): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <?php endif; ?>
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <button type="submit">SEARCH</button>
        <?php if ($search !== ''): ?>
            <a href="shop.php" class="clear-search">Clear</a>
        <?php endif; ?>
    </form>
</div>


<!-- SHOP HEADER -->
<section class="shop-header">

    <span>FASHLADY COLLECTION</span>

    <h1>Shop All</h1>

    <p>
        Discover elegant styles curated for the modern woman.
    </p>

</section>


<!-- SHOP CONTENT -->
<section class="shop-section">

    <!-- FILTER -->
    <div class="shop-top">

        <div class="categories-filter">

    <a href="shop.php"
       class="<?= $category === 'All' ? 'filter-active' : '' ?>">
        All
    </a>

    <a href="shop.php?category=New Arrivals"
       class="<?= $category === 'New Arrivals' ? 'filter-active' : '' ?>">
        New Arrivals
    </a>

    <a href="shop.php?category=Ethnic Wear"
       class="<?= $category === 'Ethnic Wear' ? 'filter-active' : '' ?>">
        Ethnic Wear
    </a>

    <a href="shop.php?category=Co-ords"
       class="<?= $category === 'Co-ords' ? 'filter-active' : '' ?>">
        Co-ords
    </a>

    <a href="shop.php?category=Kurtis"
       class="<?= $category === 'Kurtis' ? 'filter-active' : '' ?>">
        Kurtis
    </a>
<a href="shop.php?category=Tops"
   class="<?= $category === 'Tops' ? 'filter-active' : '' ?>">
    Tops
</a>

<a href="shop.php?category=Kurta Sets"
   class="<?= $category === 'Kurta Sets' ? 'filter-active' : '' ?>">
    Kurta Sets
</a>

</div>
       <select onchange="sortProducts(this.value)">
    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>
        Sort By
    </option>

    <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>
        Price: Low to High
    </option>

    <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>
        Price: High to Low
    </option>
</select>

    </div>


    <!-- PRODUCTS -->
   <div class="shop-products">

<?php if ($result && $result->num_rows > 0): ?>

    <?php while ($product = $result->fetch_assoc()): ?>
        <?php
        /* Load colour variants for this product */
        $variant_stmt = $conn->prepare("
            SELECT id, color_name, color_code, image_front, image_back, image_detail, sizes, stock
            FROM product_variants
            WHERE product_id = ?
            ORDER BY id ASC
        ");
        $variant_stmt->bind_param("i", $product['id']);
        $variant_stmt->execute();
        $variant_result = $variant_stmt->get_result();

        $product_variants = [];
        while ($variant_row = $variant_result->fetch_assoc()) {
            $product_variants[] = $variant_row;
        }
        $variant_stmt->close();

        /* If variants exist, the first variant is shown initially. */
        $initial_images = [
            $product['image'] ?? '',
            $product['image_2'] ?? '',
            $product['image_3'] ?? ''
        ];
        $initial_color = '';
        $initial_color_code = '';
        $initial_sizes = '';
        $initial_stock = null;

        if (!empty($product_variants)) {
            $v = $product_variants[0];
            $initial_images = [
                $v['image_front'] ?: ($product['image'] ?? ''),
                $v['image_back'] ?: ($product['image_2'] ?? ''),
                $v['image_detail'] ?: ($product['image_3'] ?? '')
            ];
            $initial_color = $v['color_name'] ?? '';
            $initial_color_code = $v['color_code'] ?? '';
            $initial_sizes = $v['sizes'] ?? '';
            $initial_stock = (int)$v['stock'];
        }

        $initial_images = array_values(array_filter($initial_images, function($img) {
            return !empty($img);
        }));

        if (empty($initial_images)) {
            $initial_images = [''];
        }

        $product_js_variants = [];
        foreach ($product_variants as $v) {
            $product_js_variants[] = [
                'id' => (int)$v['id'],
                'name' => $v['color_name'],
                'code' => $v['color_code'] ?: '#ddd',
                'sizes' => $v['sizes'] ?: 'Not specified',
                'stock' => (int)$v['stock'],
                'images' => array_values(array_filter([
                    $v['image_front'] ?: ($product['image'] ?? ''),
                    $v['image_back'] ?: ($product['image_2'] ?? ''),
                    $v['image_detail'] ?: ($product['image_3'] ?? '')
                ]))
            ];
        }

        $product_data = [
            'id' => (int)$product['id'],
            'images' => $initial_images,
            'color' => $initial_color,
            'colorCode' => $initial_color_code ?: '#ddd',
            'sizes' => $initial_sizes ?: 'Not specified',
            'stock' => $initial_stock,
            'variants' => $product_js_variants
        ];
        ?>

        <div class="shop-product-card"
             data-product-id="<?= (int)$product['id'] ?>"
             data-product-data='<?= htmlspecialchars(json_encode($product_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'>

            <div class="shop-product-image shop-slider"
                 id="shopSlider<?= (int)$product['id'] ?>">

                <?php if (!empty($product['badge'])): ?>
                    <span class="shop-product-badge">
                        <?= htmlspecialchars($product['badge']) ?>
                    </span>
                <?php endif; ?>

                <a href="product.php?id=<?= (int)$product['id'] ?>" class="shop-image-link">
                    <?php foreach ($initial_images as $index => $image_name): ?>
                        <?php if (!empty($image_name)): ?>
                            <img
                                class="shop-slide <?= $index === 0 ? 'active' : '' ?>"
                                src="images/<?= htmlspecialchars($image_name) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                data-slide="<?= $index ?>"
                            >
                        <?php endif; ?>
                    <?php endforeach; ?>
                </a>

                <?php if (count($initial_images) > 1): ?>
                    <button type="button" class="shop-slide-arrow shop-prev"
                            onclick="changeShopSlide(<?= (int)$product['id'] ?>, -1)"
                            aria-label="Previous image">‹</button>
                    <button type="button" class="shop-slide-arrow shop-next"
                            onclick="changeShopSlide(<?= (int)$product['id'] ?>, 1)"
                            aria-label="Next image">›</button>

                    <div class="shop-slide-dots" id="shopDots<?= (int)$product['id'] ?>">
                        <?php foreach ($initial_images as $index => $image_name): ?>
                            <button type="button"
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    onclick="goToShopSlide(<?= (int)$product['id'] ?>, <?= $index ?>)"
                                    aria-label="Image <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="shop-slide-label" id="shopLabel<?= (int)$product['id'] ?>">
                        <?= count($initial_images) >= 3 ? 'FRONT' : 'FRONT' ?>
                    </div>
                <?php endif; ?>

                <button class="heart" type="button"
                        onclick="addToWishlist(<?= (int)$product['id'] ?>)">
                    ♡
                </button>
            </div>

            <div class="shop-product-info">
                <small><?= htmlspecialchars($product['category']) ?></small>

                <h3>
                    <a href="product.php?id=<?= (int)$product['id'] ?>">
                        <?= htmlspecialchars($product['name']) ?>
                    </a>
                </h3>

                <?php if (!empty($product_variants)): ?>
                    <div class="shop-variant-info"
                         id="variantInfo<?= (int)$product['id'] ?>">

                        <div class="shop-variant-row">
                            <span class="shop-variant-label">Colour</span>
                            <span class="shop-variant-color-name"
                                  id="colorName<?= (int)$product['id'] ?>">
                                <?= htmlspecialchars($initial_color ?: 'Select colour') ?>
                            </span>
                            <?php if ($initial_color !== ''): ?>
                                <span class="shop-color-preview"
                                      id="colorPreview<?= (int)$product['id'] ?>"
                                      style="background:<?= htmlspecialchars($initial_color_code ?: '#ddd') ?>;"></span>
                            <?php endif; ?>
                        </div>

                        <div class="shop-color-options"
                             id="colorOptions<?= (int)$product['id'] ?>">
                            <?php foreach ($product_variants as $variant_index => $variant): ?>
                                <button type="button"
                                        class="shop-color-dot <?= $variant_index === 0 ? 'selected' : '' ?>"
                                        title="<?= htmlspecialchars($variant['color_name']) ?>"
                                        aria-label="<?= htmlspecialchars($variant['color_name']) ?>"
                                        style="background:<?= htmlspecialchars($variant['color_code'] ?: '#ddd') ?>;"
                                        onclick="selectShopVariant(<?= (int)$product['id'] ?>, <?= $variant_index ?>)">
                                </button>
                            <?php endforeach; ?>
                        </div>

                       <div class="shop-size-section">

    <span class="shop-size-label">SIZE</span>

    <div class="shop-size-options"
         id="sizeOptions<?= (int)$product['id'] ?>">

        <?php
        $size_list = array_filter(
            array_map('trim', explode(',', $initial_sizes))
        );
        ?>

        <?php foreach ($size_list as $size): ?>

            <button
                type="button"
                class="shop-size-btn"
                onclick="selectShopSize(<?= (int)$product['id'] ?>, '<?= htmlspecialchars($size, ENT_QUOTES) ?>')"
            >
                <?= htmlspecialchars($size) ?>
            </button>

        <?php endforeach; ?>

    </div>

    <input
        type="hidden"
        id="selectedSize<?= (int)$product['id'] ?>"
        value=""
    >

</div>

<div class="shop-stock">
    <strong>Stock:</strong>
    <span id="stockText<?= (int)$product['id'] ?>">
        <?= $initial_stock !== null
            ? ($initial_stock > 0 ? 'Available' : 'Out of stock')
            : 'Available' ?>
    </span>
</div>
                <?php endif; ?>

                <div class="product-pricing">
                    <strong>₹<?= number_format($product['price'], 0) ?></strong>

                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <del>₹<?= number_format($product['old_price'], 0) ?></del>

                        <?php
                        $discount = round(
                            (($product['old_price'] - $product['price'])
                            / $product['old_price']) * 100
                        );
                        ?>

                        <span class="discount"><?= $discount ?>% OFF</span>
                    <?php endif; ?>
                </div>

                <button class="shop-cart"
                        type="button"
                        onclick="addToCart(<?= (int)$product['id'] ?>)">
                    ADD TO CART
                </button>
            </div>
        </div>
    <?php endwhile; ?>

<?php else: ?>

    <div class="no-products">
        <h3>No products available</h3>
        <p>New collection coming soon.</p>
    </div>

<?php endif; ?>

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

<style>
/* ================= SHOP PRODUCT SLIDER + VARIANTS ================= */
.shop-product-image {
    position: relative;
    overflow: hidden;
}

.shop-image-link {
    display: block;
    width: 100%;
    height: auto;
}

.shop-slide {
    display: none;
    width: 100%;
    height: auto;
    object-fit: contain;
    object-position: center;
    background: #FFF9F5;
}

.shop-slide.active {
    display: block;
}

.shop-product-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 5;
    background: #6B2638;
    color: #fff;
    padding: 6px 9px;
    font-size: 9px;
    letter-spacing: 1px;
}

.shop-slide-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 5;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 50%;
    background: rgba(255,255,255,.88);
    color: #6B2638;
    cursor: pointer;
    font-size: 23px;
    line-height: 26px;
    padding: 0;
    box-shadow: 0 3px 12px rgba(0,0,0,.10);
}

.shop-slide-arrow:hover {
    background: #6B2638;
    color: #fff;
}

.shop-prev { left: 10px; }
.shop-next { right: 10px; }

.shop-slide-dots {
    position: absolute;
    left: 50%;
    bottom: 12px;
    transform: translateX(-50%);
    z-index: 5;
    display: flex;
    gap: 6px;
}

.shop-slide-dots button {
    width: 7px;
    height: 7px;
    padding: 0;
    border: 1px solid #fff;
    border-radius: 50%;
    background: rgba(255,255,255,.55);
    cursor: pointer;
}

.shop-slide-dots button.active {
    background: #6B2638;
}

.shop-slide-label {
    position: absolute;
    right: 10px;
    bottom: 10px;
    z-index: 5;
    padding: 5px 8px;
    background: rgba(255,255,255,.88);
    color: #6B2638;
    font-size: 8px;
    letter-spacing: 1px;
    font-weight: 600;
}

.shop-variant-info {
    margin: 10px 0 13px;
    padding: 10px 0 0;
    border-top: 1px solid #ead9dc;
}

.shop-variant-row {
    display: flex;
    align-items: center;
    gap: 7px;
    min-height: 20px;
}

.shop-variant-label {
    color: #777;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .8px;
}

.shop-variant-color-name {
    color: #242124;
    font-size: 11px;
    font-weight: 600;
}

.shop-color-preview {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid #ddd;
    display: inline-block;
}

.shop-color-options {
    display: flex;
    gap: 7px;
    margin: 8px 0 9px;
    flex-wrap: wrap;
}

.shop-color-dot {
    width: 19px;
    height: 19px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px #cfc5c7;
    padding: 0;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease;
}

.shop-color-dot:hover,
.shop-color-dot.selected {
    transform: scale(1.08);
    box-shadow: 0 0 0 2px #6B2638;
}

.shop-variant-details {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 15px;
    color: #777;
    font-size: 10px;
    line-height: 1.5;
}

.shop-variant-details strong {
    color: #6B2638;
    font-weight: 600;
}

@media (max-width: 600px) {
    .shop-slide-arrow {
        width: 27px;
        height: 27px;
        font-size: 20px;
    }

    .shop-variant-details {
        gap: 5px 12px;
    }
}
.shop-size-section {
    margin-top: 14px;
}

.shop-size-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: #6B2638;
    margin-bottom: 9px;
}

.shop-size-options {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.shop-size-btn {
    min-width: 42px;
    height: 34px;
    padding: 0 10px;
    border: 1px solid #D8C4C8;
    background: #FFF9F5;
    color: #4B2933;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
}

.shop-size-btn:hover {
    border-color: #C9828F;
    color: #6B2638;
}

.shop-size-btn.active {
    background: #6B2638;
    border-color: #6B2638;
    color: #FFFFFF;
}

.shop-stock {
    margin-top: 10px;
    font-size: 12px;
    color: #666;
}

.shop-stock strong {
    color: #4B2933;
}
</style>

<style>
.search-toggle {
    border: 0;
    background: transparent;
    padding: 0;
    cursor: pointer;
}
.shop-search-panel {
    display: none;
    padding: 18px 7%;
    background: #fff9f5;
    border-top: 1px solid #f0dfe2;
    border-bottom: 1px solid #ead1d5;
}
.shop-search-panel.show { display: block; }
.shop-search-form {
    max-width: 850px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e5c9ce;
    padding: 9px 12px;
    box-shadow: 0 8px 25px rgba(107,38,56,.06);
}
.shop-search-form > i { color:#6B2638; font-size:15px; margin-left:5px; }
.shop-search-form input[type="text"] {
    flex:1; border:none; outline:none; background:transparent;
    color:#242124; font-family:"DM Sans",sans-serif; font-size:14px; padding:10px 2px;
}
.shop-search-form input[type="text"]::placeholder { color:#9a8d90; }
.shop-search-form button {
    border:none; background:#6B2638; color:#fff; padding:11px 20px;
    font-family:"DM Sans",sans-serif; font-size:11px; font-weight:600;
    letter-spacing:1.2px; cursor:pointer;
}
.shop-search-form button:hover { background:#C9828F; }
.clear-search { color:#6B2638; text-decoration:none; font-size:12px; padding:0 5px; }
.clear-search:hover { color:#C9828F; }
@media (max-width:600px) {
    .shop-search-panel { padding:14px 5%; }
    .shop-search-form { gap:8px; }
    .shop-search-form button { padding:10px 12px; }
}
</style>


<script>
/* ================= SHOP IMAGE SLIDER + COLOUR VARIANTS ================= */
const shopSliderState = {};
const shopVariantState = {};

function getShopProductData(productId) {
    const card = document.querySelector('.shop-product-card[data-product-id="' + productId + '"]');
    if (!card) return null;

    try {
        return JSON.parse(card.getAttribute('data-product-data'));
    } catch (e) {
        return null;
    }
}

function renderShopImages(productId, images) {
    const slider = document.getElementById('shopSlider' + productId);
    if (!slider) return;

    const link = slider.querySelector('.shop-image-link');
    if (!link) return;

    link.innerHTML = '';

    images.forEach(function(imageName, index) {
        if (!imageName) return;

        const img = document.createElement('img');
        img.className = 'shop-slide' + (index === 0 ? ' active' : '');
        img.src = 'images/' + imageName;
        img.alt = 'Product image';
        img.dataset.slide = index;
        link.appendChild(img);
    });

    shopSliderState[productId] = 0;

    const dots = document.getElementById('shopDots' + productId);
    if (dots) {
        dots.innerHTML = '';
        images.forEach(function(_, index) {
            const button = document.createElement('button');
            button.type = 'button';
            if (index === 0) button.classList.add('active');
            button.setAttribute('aria-label', 'Image ' + (index + 1));
            button.onclick = function() {
                goToShopSlide(productId, index);
            };
            dots.appendChild(button);
        });
    }

    const label = document.getElementById('shopLabel' + productId);
    if (label) label.textContent = 'FRONT';
}

function goToShopSlide(productId, index) {

    const slider = document.getElementById('shopSlider' + productId);

    if (!slider) return;

    const slides = slider.querySelectorAll('.shop-slide');

    if (!slides.length) return;

    if (index < 0) {
        index = slides.length - 1;
    }

    if (index >= slides.length) {
        index = 0;
    }

    /* SHOW ONLY SELECTED IMAGE */
    slides.forEach(function(slide, i) {

        if (i === index) {
            slide.style.display = 'block';
            slide.classList.add('active');
        } else {
            slide.style.display = 'none';
            slide.classList.remove('active');
        }

    });

    /* UPDATE DOTS */
    const dots = document.querySelectorAll(
        '#shopDots' + productId + ' button'
    );

    dots.forEach(function(dot, i) {
        dot.classList.toggle('active', i === index);
    });

    /* UPDATE IMAGE LABEL */
    const labels = ['FRONT', 'BACK', 'DETAIL'];

    const label = document.getElementById(
        'shopLabel' + productId
    );

    if (label) {
        label.textContent =
            labels[index] || 'IMAGE ' + (index + 1);
    }

    shopSliderState[productId] = index;
}

function changeShopSlide(productId, direction) {
    const current = shopSliderState[productId] || 0;
    goToShopSlide(productId, current + direction);
}
function selectShopVariant(productId, variantIndex) {

    const data = getShopProductData(productId);

    if (!data || !data.variants || !data.variants[variantIndex]) {
        return;
    }

    const variant = data.variants[variantIndex];

    /* Remember selected variant */
    shopVariantState[productId] = variant.id;

    /* Change product images */
    renderShopImages(productId, variant.images || data.images);

    /* Update colour name */
    const colorName = document.getElementById(
        'colorName' + productId
    );

    if (colorName) {
        colorName.textContent = variant.name || 'Select colour';
    }

    /* Update colour preview */
    const preview = document.getElementById(
        'colorPreview' + productId
    );

    if (preview) {
        preview.style.background = variant.code || '#ddd';
    }

    /* Select colour circle */
    const dots = document.querySelectorAll(
        '#colorOptions' + productId + ' .shop-color-dot'
    );

    dots.forEach(function(dot, index) {
        dot.classList.toggle(
            'selected',
            index === variantIndex
        );
    });

    /* Update sizes */
    const sizeOptions = document.getElementById(
        'sizeOptions' + productId
    );

    const selectedSizeInput = document.getElementById(
        'selectedSize' + productId
    );

    if (sizeOptions) {

        sizeOptions.innerHTML = '';

        const sizes = (variant.sizes || '')
            .split(',')
            .map(function(size) {
                return size.trim();
            })
            .filter(function(size) {
                return size !== '';
            });

        sizes.forEach(function(size) {

            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'shop-size-btn';
            button.textContent = size;

            button.onclick = function() {
                selectShopSize(productId, size);
            };

            sizeOptions.appendChild(button);
        });
    }

    /* Reset selected size */
    if (selectedSizeInput) {
        selectedSizeInput.value = '';
    }

    /* Update stock */
    const stockText = document.getElementById(
        'stockText' + productId
    );

    if (stockText) {
        stockText.textContent =
            parseInt(variant.stock) > 0
                ? 'Available'
                : 'Out of stock';
    }
}
</script>

<script>
function toggleSearch() {
    const panel = document.getElementById("shopSearchPanel");
    panel.classList.toggle("show");
    if (panel.classList.contains("show")) {
        const input = panel.querySelector('input[name="search"]');
        if (input) setTimeout(() => input.focus(), 50);
    }
}

function sortProducts(value) {
    const url = new URL(window.location.href);
    url.searchParams.set("sort", value);
    window.location.href = url.toString();
}
function selectShopSize(productId, size) {

    const sizeButtons = document.querySelectorAll(
        '#sizeOptions' + productId + ' .shop-size-btn'
    );

    sizeButtons.forEach(function(button) {
        button.classList.remove('active');

        if (button.textContent.trim() === size) {
            button.classList.add('active');
        }
    });

    const selectedSizeInput = document.getElementById(
        'selectedSize' + productId
    );

    if (selectedSizeInput) {
        selectedSizeInput.value = size;
    }
}
function addToWishlist(productId) {

    const formData = new FormData();
    formData.append("product_id", productId);

    fetch("add_wishlist.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Server response: " + data);
    })
    .catch(error => {
        alert("Error: " + error);
    });
}

function addToCart(productId) {

    const data = getShopProductData(productId);

    if (!data) {
        alert("Product not found.");
        return;
    }

    let variantId = shopVariantState[productId] || "";

    /* If colour is not clicked, use the first colour */
    if (!variantId && data.variants && data.variants.length > 0) {
        variantId = data.variants[0].id;
    }

    const sizeInput = document.getElementById(
        "selectedSize" + productId
    );

    const size = sizeInput ? sizeInput.value : "";

    /* Variant products must have colour + size */
    if (data.variants && data.variants.length > 0) {

        if (!variantId) {
            alert("Please select a colour.");
            return;
        }

        if (!size) {
            alert("Please select a size.");
            return;
        }
    }

    const formData = new FormData();

    formData.append("product_id", productId);
    formData.append("variant_id", variantId);
    formData.append("size", size);

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
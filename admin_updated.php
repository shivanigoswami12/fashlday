<?php
session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once "config.php";

$message = "";
$error = "";

/* IMAGE UPLOAD HELPER */
function upload_admin_image($field, &$error, $prefix = "product_") {
    if (!isset($_FILES[$field]) || $_FILES[$field]["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]["error"] !== UPLOAD_ERR_OK) {
        $error = "Upload Error Code: " . ($_FILES[$field]["error"] ?? "unknown");
        return null;
    }

    $allowed = ["jpg", "jpeg", "png", "webp"];
    $extension = strtolower(pathinfo($_FILES[$field]["name"], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed, true)) {
        $error = "Only JPG, JPEG, PNG or WEBP images are allowed.";
        return null;
    }

    $upload_folder = __DIR__ . "/images/";
    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    $image_name = uniqid($prefix, true) . "." . $extension;
    $destination = $upload_folder . $image_name;

    if (!move_uploaded_file($_FILES[$field]["tmp_name"], $destination)) {
        $error = "Could not save the image.";
        return null;
    }

    return $image_name;
}

/* Remove files that were uploaded during a failed/partial save. */
function cleanup_uploaded_files($files) {
    foreach ((array)$files as $file_name) {
        if (!empty($file_name)) {
            $file_path = __DIR__ . "/images/" . basename($file_name);
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }
    }
}

/* ADD PRODUCT VARIANT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "variant_add") {
    $variant_product_id = (int)($_POST["variant_product_id"] ?? 0);
    $color_name = trim($_POST["color_name"] ?? "");
    $color_code = trim($_POST["color_code"] ?? "");
    $variant_sizes = trim($_POST["variant_sizes"] ?? "");
    $variant_stock = max(0, (int)($_POST["variant_stock"] ?? 0));

    if ($variant_product_id <= 0 || $color_name === "") {
        $error = "Please select a product and enter a colour name.";
    }

    $front = $back = $detail = null;
    $uploaded_variant_files = [];

    if ($error === "") {
        $front = upload_admin_image("variant_front", $error, "variant_front_");
        if ($front) $uploaded_variant_files[] = $front;
    }
    if ($error === "") {
        $back = upload_admin_image("variant_back", $error, "variant_back_");
        if ($back) $uploaded_variant_files[] = $back;
    }
    if ($error === "") {
        $detail = upload_admin_image("variant_detail", $error, "variant_detail_");
        if ($detail) $uploaded_variant_files[] = $detail;
    }

    /* If any one of the three uploads fails, do not leave the earlier
       successful uploads sitting in /images as orphan files. */
    if ($error !== "") {
        cleanup_uploaded_files($uploaded_variant_files);
    }

    if ($error === "") {
        $stmt = $conn->prepare("
            INSERT INTO product_variants
            (product_id, color_name, color_code, image_front, image_back, image_detail, sizes, stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issssssi",
            $variant_product_id,
            $color_name,
            $color_code,
            $front,
            $back,
            $detail,
            $variant_sizes,
            $variant_stock
        );

        if ($stmt->execute()) {
            $message = "Colour variant added successfully! ✨";
        } else {
            $error = "Could not save the colour variant.";
            foreach ($uploaded_variant_files as $file_name) {
                $file_path = __DIR__ . "/images/" . basename($file_name);
                if (is_file($file_path)) unlink($file_path);
            }
        }
        $stmt->close();
    }
}

/* DELETE PRODUCT VARIANT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "variant_delete") {
    $variant_id = (int)($_POST["variant_id"] ?? 0);

    if ($variant_id > 0) {
        $stmt = $conn->prepare("SELECT image_front, image_back, image_detail FROM product_variants WHERE id=?");
        $stmt->bind_param("i", $variant_id);
        $stmt->execute();
        $stmt->bind_result($v_front, $v_back, $v_detail);
        $found_variant = $stmt->fetch();
        $stmt->close();

        if ($found_variant) {
            $stmt = $conn->prepare("DELETE FROM product_variants WHERE id=?");
            $stmt->bind_param("i", $variant_id);
            if ($stmt->execute()) {
                foreach ([$v_front, $v_back, $v_detail] as $file_name) {
                    if (!empty($file_name)) {
                        $file_path = __DIR__ . "/images/" . basename($file_name);
                        if (is_file($file_path)) unlink($file_path);
                    }
                }
                $message = "Colour variant deleted.";
            } else {
                $error = "Could not delete the colour variant.";
            }
            $stmt->close();
        }
    }
}

$edit_id = (int)($_GET["edit"] ?? 0);
$edit_product = null;

/* DELETE PRODUCT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {

    $delete_id = (int)($_POST["id"] ?? 0);

    if ($delete_id > 0) {
        $stmt = $conn->prepare("SELECT image, image_2, image_3 FROM products WHERE id=?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->bind_result($delete_image, $delete_image_2, $delete_image_3);
        $found = $stmt->fetch();
        $stmt->close();

        if ($found) {
            $variant_files = [];
            $variant_stmt = $conn->prepare("SELECT image_front, image_back, image_detail FROM product_variants WHERE product_id=?");
            $variant_stmt->bind_param("i", $delete_id);
            $variant_stmt->execute();
            $variant_result = $variant_stmt->get_result();
            while ($variant_row = $variant_result->fetch_assoc()) {
                foreach (["image_front", "image_back", "image_detail"] as $key) {
                    if (!empty($variant_row[$key])) $variant_files[] = $variant_row[$key];
                }
            }
            $variant_stmt->close();

            $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id=?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
            $stmt->bind_param("i", $delete_id);

            if ($stmt->execute()) {
                foreach ([$delete_image, $delete_image_2, $delete_image_3] as $file_name) {
                    if (!empty($file_name)) {
                        $file = __DIR__ . "/images/" . basename($file_name);
                        if (is_file($file)) unlink($file);
                    }
                }
                foreach ($variant_files as $file_name) {
                    $file = __DIR__ . "/images/" . basename($file_name);
                    if (is_file($file)) unlink($file);
                }
                $message = "Product deleted successfully.";
            } else {
                $error = "Could not delete the product.";
            }

            $stmt->close();
        }
    }
}

/* ADD / UPDATE PRODUCT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && in_array(($_POST["action"] ?? ""), ["", "product_save"], true)) {

    $product_id = (int)($_POST["product_id"] ?? 0);

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = $_POST["price"] ?? "";
    $old_price = $_POST["old_price"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $fabric = trim($_POST["fabric"] ?? "");
    $features = trim($_POST["features"] ?? "");
    $badge = trim($_POST["badge"] ?? "");

    if ($name === "" || $category === "" || $price === "") {
        $error = "Please fill all required fields.";
    }

    $image_name = null;
    $new_image_uploaded = false;

    /* IMAGE UPLOAD */
    if ($error === "" && isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            $error = "Upload Error Code: " . ($_FILES["image"]["error"] ?? "unknown");
        } else {

            $allowed = ["jpg", "jpeg", "png", "webp"];
            $extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowed)) {
                $error = "Only JPG, JPEG, PNG or WEBP images are allowed.";
            } else {

                $upload_folder = __DIR__ . "/images/";

                if (!is_dir($upload_folder)) {
                    mkdir($upload_folder, 0777, true);
                }

                $image_name = uniqid("product_", true) . "." . $extension;
                $destination = $upload_folder . $image_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $destination)) {
                    $new_image_uploaded = true;
                } else {
                    $error = "Could not save the image.";
                }
            }
        }
    }

    $image_2_name = null;
    $image_3_name = null;
    $new_image_2_uploaded = false;
    $new_image_3_uploaded = false;

    if ($error === "" && isset($_FILES["image_2"]) && $_FILES["image_2"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $image_2_name = upload_admin_image("image_2", $error, "product_back_");
        $new_image_2_uploaded = ($image_2_name !== null);
    }

    if ($error === "" && isset($_FILES["image_3"]) && $_FILES["image_3"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $image_3_name = upload_admin_image("image_3", $error, "product_detail_");
        $new_image_3_uploaded = ($image_3_name !== null);
    }

    /* If one of the three product image uploads fails, remove any images
       already saved during this same request. */
    if ($error !== "") {
        cleanup_uploaded_files([
            $new_image_uploaded ? $image_name : null,
            $new_image_2_uploaded ? $image_2_name : null,
            $new_image_3_uploaded ? $image_3_name : null
        ]);
        $image_name = $image_2_name = $image_3_name = null;
        $new_image_uploaded = $new_image_2_uploaded = $new_image_3_uploaded = false;
    }

    /* UPDATE EXISTING PRODUCT */
    if ($error === "" && $product_id > 0) {

        $old_price_value = ($old_price !== "") ? $old_price : null;

        $stmt = $conn->prepare("SELECT image, image_2, image_3 FROM products WHERE id=?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($existing_image, $existing_image_2, $existing_image_3);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found) {
            $error = "Product not found.";
        } else {

            $final_image = $new_image_uploaded ? $image_name : $existing_image;
            $final_image_2 = $new_image_2_uploaded ? $image_2_name : $existing_image_2;
            $final_image_3 = $new_image_3_uploaded ? $image_3_name : $existing_image_3;

            $stmt = $conn->prepare("
                UPDATE products
                SET name=?, category=?, price=?, old_price=?,
                    description=?, fabric=?, features=?, image=?, image_2=?, image_3=?, badge=?
                WHERE id=?
            ");

            $stmt->bind_param(
                "ssddsssssssi",
                $name,
                $category,
                $price,
                $old_price_value,
                $description,
                $fabric,
                $features,
                $final_image,
                $final_image_2,
                $final_image_3,
                $badge,
                $product_id
            );

            if ($stmt->execute()) {

                if ($new_image_uploaded && !empty($existing_image)) {
                    $old_file = __DIR__ . "/images/" . basename($existing_image);
                    if (is_file($old_file)) unlink($old_file);
                }
                if ($new_image_2_uploaded && !empty($existing_image_2)) {
                    $old_file = __DIR__ . "/images/" . basename($existing_image_2);
                    if (is_file($old_file)) unlink($old_file);
                }
                if ($new_image_3_uploaded && !empty($existing_image_3)) {
                    $old_file = __DIR__ . "/images/" . basename($existing_image_3);
                    if (is_file($old_file)) unlink($old_file);
                }

                $message = "Product updated successfully! ✨";
                $edit_id = 0;

            } else {
                $error = "Could not update the product.";

                foreach ([$new_image_uploaded ? $image_name : null, $new_image_2_uploaded ? $image_2_name : null, $new_image_3_uploaded ? $image_3_name : null] as $file_name) {
                    if ($file_name && is_file(__DIR__ . "/images/" . $file_name)) {
                        unlink(__DIR__ . "/images/" . $file_name);
                    }
                }
            }

            $stmt->close();
        }

    /* ADD NEW PRODUCT */
    } elseif ($error === "") {

        $old_price_value = ($old_price !== "") ? $old_price : null;

        $stmt = $conn->prepare("
            INSERT INTO products
            (name, category, price, old_price, description, fabric, features, image, image_2, image_3, badge)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssddsssssss",
            $name,
            $category,
            $price,
            $old_price_value,
            $description,
            $fabric,
            $features,
            $image_name,
            $image_2_name,
            $image_3_name,
            $badge
        );

        if ($stmt->execute()) {
            $message = "Product added successfully! 🎉";
        } else {
            $error = "Something went wrong while saving the product.";

            foreach ([$image_name, $image_2_name, $image_3_name] as $file_name) {
                if ($file_name && is_file(__DIR__ . "/images/" . $file_name)) {
                    unlink(__DIR__ . "/images/" . $file_name);
                }
            }
        }

        $stmt->close();
    }
}

/* LOAD PRODUCT FOR EDIT */
if ($edit_id > 0) {

    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();

    $stmt->close();

    if (!$edit_product) {
        $error = "Product not found.";
        $edit_id = 0;
    }
}

/* LOAD ALL PRODUCTS */
$manage_result = $conn->query("SELECT * FROM products ORDER BY id DESC");

/* DASHBOARD STATS */
$total_products = 0;
$total_orders = 0;
$total_sales = 0;
$pending_orders = 0;
$delivered_orders = 0;

$stats_result = $conn->query(
    "SELECT COUNT(*) AS total_products FROM products"
);

if ($stats_result) {
    $stats_row = $stats_result->fetch_assoc();
    $total_products = (int)$stats_row["total_products"];
}

$stats_result = $conn->query(
    "SELECT COUNT(*) AS total_orders,
            COALESCE(SUM(total_amount), 0) AS total_sales
     FROM orders"
);

if ($stats_result) {
    $stats_row = $stats_result->fetch_assoc();
    $total_orders = (int)$stats_row["total_orders"];
    $total_sales = (float)$stats_row["total_sales"];
}

$stats_result = $conn->query(
    "SELECT COUNT(*) AS pending_orders
     FROM orders
     WHERE order_status = 'Pending'"
);

if ($stats_result) {
    $stats_row = $stats_result->fetch_assoc();
    $pending_orders = (int)$stats_row["pending_orders"];
}

$stats_result = $conn->query(
    "SELECT COUNT(*) AS delivered_orders
     FROM orders
     WHERE order_status = 'Delivered'"
);

if ($stats_result) {
    $stats_row = $stats_result->fetch_assoc();
    $delivered_orders = (int)$stats_row["delivered_orders"];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin | FASHLADY</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap"
          rel="stylesheet">

    <style>

        .admin-page {
            min-height: 100vh;
            background: #FFF9F5;
            padding: 70px 20px;
        }

        .admin-box {
            max-width: 750px;
            margin: auto;
            background: white;
            padding: 55px;
            box-shadow: 0 10px 40px rgba(75, 41, 51, 0.08);
        }

        .admin-heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-heading span {
            color: #C9828F;
            font-size: 11px;
            letter-spacing: 3px;
        }

        .admin-heading h1 {
            font-family: "Playfair Display", serif;
            color: #6B2638;
            font-size: 42px;
            margin: 10px 0;
        }

        .admin-heading p {
            color: #777;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            color: #6B2638;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 13px;
            border: 1px solid #ddd;
            background: #FFF9F5;
            outline: none;
            font-family: "DM Sans", sans-serif;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .price-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .image-help {
            color: #999;
            font-size: 11px;
            margin-top: 7px;
        }

        .add-product-button {
            width: 100%;
            padding: 15px;
            background: #6B2638;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 13px;
            letter-spacing: 1px;
        }

        .add-product-button:hover {
            background: #4B2933;
        }

        .success-message {
            background: #edf7ed;
            color: #36743a;
            padding: 13px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 13px;
        }

        .error-message {
            background: #fff0f0;
            color: #a33a3a;
            padding: 13px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 600px) {

            .admin-box {
                padding: 30px 20px;
            }

            .admin-heading h1 {
                font-size: 34px;
            }

            .price-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
.admin-page {
    padding: 35px 15px;
}

.admin-box {
    padding: 30px 20px;
}

.admin-heading h1 {
    font-size: 30px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
}

.price-row {
    grid-template-columns: 1fr;
    gap: 0;
}

.admin-content {
    gap: 20px;
}
        }

    </style>


<style>
.manage-products-box {
    margin-top: 35px;
}

.manage-list {
    display: grid;
    gap: 14px;
}

.manage-product {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    align-items: center;
    gap: 18px;
    padding: 14px;
    border: 1px solid #ead9dc;
    background: #fff9f5;
}

.manage-image {
    width: 80px;
    height: 95px;
    background: #f3dde1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: #6b2638;
    font-size: 11px;
    text-align: center;
}

.manage-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.manage-info small {
    color: #c9828f;
    letter-spacing: 1px;
    font-size: 10px;
}

.manage-info h3 {
    margin: 6px 0;
    color: #242124;
    font-family: "Playfair Display", serif;
}

.manage-info p {
    margin: 0;
    color: #6b2638;
    font-weight: 600;
}

.manage-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.edit-btn,
.delete-btn {
    padding: 10px 13px;
    border: none;
    text-decoration: none;
    cursor: pointer;
    font-size: 11px;
}

.edit-btn {
    background: #f3dde1;
    color: #6b2638;
}

.delete-btn {
    background: #6b2638;
    color: white;
}

@media (max-width: 650px) {
    .manage-product {
        grid-template-columns: 65px 1fr;
    }
    .manage-image {
        width: 65px;
        height: 80px;
    }
    .manage-actions {
        grid-column: 1 / -1;
    }
}
</style>


<style>
/* ================= DASHBOARD LAYOUT FIX ================= */
.admin-page {
    display: block !important;
    width: 100%;
    overflow-x: hidden;
}

.dashboard-stats {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto 35px;
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    box-sizing: border-box;
}

.stat-card {
    background: #fffdfb;
    border: 1px solid #e5c9ce;
    padding: 22px 12px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(107, 38, 56, 0.06);
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(107, 38, 56, 0.10);
}

.stat-card span {
    display: block;
    color: #777;
    font-size: 10px;
    letter-spacing: 1px;
    margin-bottom: 9px;
}

.stat-card strong {
    display: block;
    color: #6B2638;
    font-family: "Playfair Display", serif;
    font-size: 25px;
}

.admin-content {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 35px;
    align-items: start;
    box-sizing: border-box;
}

.admin-content > .admin-box {
    width: 100%;
    max-width: none;
    margin: 0;
    box-sizing: border-box;
}

.manage-products-box {
    margin: 0;
}

@media (max-width: 1000px) {
    .admin-content {
        grid-template-columns: 1fr;
    }

    .dashboard-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .dashboard-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 400px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
}

.variant-box {
    width: 100%;
    max-width: 1200px;
    margin: 35px auto 0;
    box-sizing: border-box;
}

.variant-list {
    margin-top: 30px;
    display: grid;
    gap: 12px;
}

.variant-item {
    display: grid;
    grid-template-columns: 1.3fr 1fr auto;
    align-items: center;
    gap: 15px;
    padding: 14px;
    border: 1px solid #ead9dc;
    background: #fff9f5;
}

.variant-colour {
    display: flex;
    align-items: center;
    gap: 10px;
}

.colour-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #ddd;
    display: inline-block;
    flex: 0 0 auto;
}

.variant-colour strong {
    display: block;
    color: #242124;
    font-family: "Playfair Display", serif;
}

.variant-colour small,
.variant-meta span {
    display: block;
    color: #777;
    font-size: 11px;
    margin-top: 3px;
}

.variant-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

@media (max-width: 700px) {
    .variant-item {
        grid-template-columns: 1fr;
    }
}
</style>

</head>

<body>

<div class="admin-page">

    <div class="dashboard-stats">

        <div class="stat-card">
            <span>Total Products</span>
            <strong><?= $total_products ?></strong>
        </div>

        <div class="stat-card">
            <span>Total Orders</span>
            <strong><?= $total_orders ?></strong>
        </div>

        <div class="stat-card">
            <span>Total Sales</span>
            <strong>₹<?= number_format($total_sales, 0) ?></strong>
        </div>

        <div class="stat-card">
            <span>Pending Orders</span>
            <strong><?= $pending_orders ?></strong>
        </div>

        <div class="stat-card">
            <span>Delivered Orders</span>
            <strong><?= $delivered_orders ?></strong>
        </div>

    </div>
<div style="text-align:center; margin-bottom:35px;">

    <a href="admin_orders.php"
       style="
            display:inline-block;
            padding:13px 28px;
            background:#6B2638;
            color:white;
            text-decoration:none;
            font-size:12px;
            font-weight:600;
            letter-spacing:1.5px;
       ">
        VIEW ORDERS
    </a>
<a href="admin_logout.php"
   style="
        display:inline-block;
        padding:13px 28px;
        background:#f3dde1;
        color:#6B2638;
        text-decoration:none;
        font-size:12px;
        font-weight:600;
        letter-spacing:1.5px;
        margin-left:10px;
   ">
    LOGOUT
</a>

</div>

    <div class="admin-content">

    <div class="admin-box">

        <div class="admin-heading">

            <span>FASHLADY ADMIN</span>

            <h1><?= $edit_product ? 'Edit Product' : 'Add Product' ?></h1>

            <p><?= $edit_product ? 'Update your product details below.' : 'Add a new product to your online store.' ?></p>

        </div>


        <?php if ($message !== ""): ?>

            <div class="success-message">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST"
              enctype="multipart/form-data">
         <input type="hidden" name="product_id" value="<?= $edit_product ? (int)$edit_product['id'] : 0 ?>">

            <!-- PRODUCT NAME -->

            <div class="form-group">

                <label>Product Name *</label>

                <input
    type="text"
    name="name"
    placeholder="Example: Floral Printed Kurta"
    value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>"
    required
>

            </div>


            <!-- CATEGORY -->

            <div class="form-group">

                <label>Category *</label>

                <select name="category" required>

                    <option value="">Select Category</option>

                   <option value="Ethnic Wear"
    <?= ($edit_product && $edit_product['category'] == 'Ethnic Wear') ? 'selected' : '' ?>>
    Ethnic Wear
</option>

<option value="Co-ords"
    <?= ($edit_product && $edit_product['category'] == 'Co-ords') ? 'selected' : '' ?>>
    Co-ords
</option>

<option value="Kurtis"
    <?= ($edit_product && $edit_product['category'] == 'Kurtis') ? 'selected' : '' ?>>
    Kurtis
</option>
<option value="Tops"
    <?= ($edit_product && $edit_product['category'] == 'Tops') ? 'selected' : '' ?>>
    Tops
</option>

<option value="Kurta Sets"
    <?= ($edit_product && $edit_product['category'] == 'Kurta Sets') ? 'selected' : '' ?>>
    Kurta Sets
</option>

<option value="New Arrivals"
    <?= ($edit_product && $edit_product['category'] == 'New Arrivals') ? 'selected' : '' ?>>
    New Arrivals
</option>

                </select>

            </div>


            <!-- PRICE -->

            <div class="price-row">

                <div class="form-group">

                    <label>Price *</label>

                    <input
    type="number"
    name="price"
    placeholder="1299"
    value="<?= $edit_product ? htmlspecialchars($edit_product['price']) : '' ?>"
    min="0"
    step="0.01"
    required
>

                </div>


                <div class="form-group">

                    <label>Old Price</label>

                   <input
    type="number"
    name="old_price"
    placeholder="1599"
    value="<?= ($edit_product && $edit_product['old_price'] !== null) ? htmlspecialchars($edit_product['old_price']) : '' ?>"
    min="0"
    step="0.01"
>

                </div>

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group">

                <label>Description</label>

               <textarea
    name="description"
    placeholder="Describe the product..."
><?= $edit_product ? htmlspecialchars($edit_product['description'] ?? '') : '' ?></textarea>

            </div>


            <!-- FABRIC -->

            <div class="form-group">
                <label>Fabric</label>
                <input
                    type="text"
                    name="fabric"
                    placeholder="Example: Cotton / Rayon / Georgette"
                    value="<?= $edit_product ? htmlspecialchars($edit_product['fabric'] ?? '') : '' ?>"
                >
            </div>

            <!-- FEATURES -->

            <div class="form-group">
                <label>Features / Highlights</label>
                <textarea
                    name="features"
                    placeholder="Example: Embroidered neckline, matching dupatta, straight-fit kurta..."
                ><?= $edit_product ? htmlspecialchars($edit_product['features'] ?? '') : '' ?></textarea>
            </div>

            <!-- BADGE -->

            <div class="form-group">

                <label>Badge</label>

                <select name="badge">

                    <option value="">No Badge</option>

<option value="NEW"
    <?= ($edit_product && $edit_product['badge'] == 'NEW') ? 'selected' : '' ?>>
    NEW
</option>

<option value="BESTSELLER"
    <?= ($edit_product && $edit_product['badge'] == 'BESTSELLER') ? 'selected' : '' ?>>
    BESTSELLER
</option>

<option value="TRENDING"
    <?= ($edit_product && $edit_product['badge'] == 'TRENDING') ? 'selected' : '' ?>>
    TRENDING
</option>

<option value="SALE"
    <?= ($edit_product && $edit_product['badge'] == 'SALE') ? 'selected' : '' ?>>
    SALE
</option>

                </select>

            </div>


            <!-- PRODUCT IMAGES -->

            <div class="form-group">
                <label>Front / Main Image</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                <div class="image-help">Main product image. Leave empty while editing to keep the current image.</div>
            </div>

            <div class="form-group">
                <label>Back Image</label>
                <input type="file" name="image_2" accept=".jpg,.jpeg,.png,.webp">
                <div class="image-help">Optional • Back view of the product.</div>
            </div>

            <div class="form-group">
                <label>Detail / Feature Image</label>
                <input type="file" name="image_3" accept=".jpg,.jpeg,.png,.webp">
                <div class="image-help">Optional • Embroidery, neckline or fabric-detail image.</div>
            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="add-product-button"
            >
                <?= $edit_product ? 'UPDATE PRODUCT' : 'ADD PRODUCT' ?>
            </button>

            <?php if ($edit_product): ?>
                <a href="admin_updated.php"
                   style="display:block;text-align:center;margin-top:15px;color:#6B2638;text-decoration:none;font-size:13px;">
                    ← Cancel Edit
                </a>
            <?php endif; ?>


        </form>

    </div>

    <!-- MANAGE PRODUCTS -->
    <div class="admin-box manage-products-box">
        <div class="admin-heading">
            <span>YOUR STORE</span>
            <h1>Manage Products</h1>
            <p>View, edit or remove products from your online store.</p>
        </div>

        <div class="manage-list">
            <?php if ($manage_result && $manage_result->num_rows > 0): ?>
                <?php while ($product = $manage_result->fetch_assoc()): ?>
                    <div class="manage-product">
                        <div class="manage-image">
                            <?php if (!empty($product["image"])): ?>
                                <img src="images/<?= htmlspecialchars($product["image"]) ?>"
                                     alt="<?= htmlspecialchars($product["name"]) ?>">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </div>

                        <div class="manage-info">
                            <small><?= htmlspecialchars($product["category"]) ?></small>
                            <h3><?= htmlspecialchars($product["name"]) ?></h3>
                            <p>
    ₹<?= number_format($product["price"], 0) ?>

    <?php if (!empty($product["old_price"]) && $product["old_price"] > $product["price"]): ?>

        <del style="color:#999; font-size:11px; font-weight:400; margin-left:6px;">
            ₹<?= number_format($product["old_price"], 0) ?>
        </del>

        <?php
        $discount = round(
            (($product["old_price"] - $product["price"])
            / $product["old_price"]) * 100
        );
        ?>

        <span style="color:#C9828F; font-size:10px; margin-left:5px;">
            <?= $discount ?>% OFF
        </span>

    <?php endif; ?>
</p>
                        </div>

                        <div class="manage-actions">
                            <a class="edit-btn"
                               href="admin_updated.php?edit=<?= (int)$product["id"] ?>">
                                ✏ EDIT
                            </a>

                            <form method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$product["id"] ?>">
                                <button class="delete-btn" type="submit">
                                    🗑 DELETE
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center;color:#777;">No products added yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- PRODUCT VARIANTS -->
    <div class="admin-box variant-box">
        <div class="admin-heading">
            <span>COLOUR & SIZE MANAGEMENT</span>
            <h1>Product Variants</h1>
            <p>Add a separate colour with its own images, sizes and stock.</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="variant_add">

            <div class="form-group">
                <label>Product *</label>
                <select name="variant_product_id" required>
                    <option value="">Select Product</option>
                    <?php
                    $variant_products = $conn->query("SELECT id, name FROM products ORDER BY id DESC");
                    if ($variant_products):
                        while ($vp = $variant_products->fetch_assoc()):
                    ?>
                        <option value="<?= (int)$vp['id'] ?>">
                            <?= htmlspecialchars($vp['name']) ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>
            </div>

            <div class="price-row">
                <div class="form-group">
                    <label>Colour Name *</label>
                    <input type="text" name="color_name" placeholder="Example: Rose Pink" required>
                </div>

                <div class="form-group">
                    <label>Colour Code</label>
                    <input type="text" name="color_code" placeholder="#C9828F">
                    <div class="image-help">Use a HEX colour code for the colour circle.</div>
                </div>
            </div>

            <div class="form-group">
                <label>Available Sizes</label>
                <input type="text" name="variant_sizes" placeholder="S, M, L, XL, XXL">
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="variant_stock" min="0" value="0">
            </div>

            <div class="price-row">
                <div class="form-group">
                    <label>Front Image</label>
                    <input type="file" name="variant_front" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="form-group">
                    <label>Back Image</label>
                    <input type="file" name="variant_back" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="form-group">
                <label>Detail Image</label>
                <input type="file" name="variant_detail" accept=".jpg,.jpeg,.png,.webp">
            </div>

            <button type="submit" class="add-product-button">ADD COLOUR VARIANT</button>
        </form>

        <div class="variant-list">
            <?php
            $variant_result = $conn->query("
                SELECT pv.*, p.name AS product_name
                FROM product_variants pv
                INNER JOIN products p ON p.id = pv.product_id
                ORDER BY pv.id DESC
            ");
            ?>
            <?php if ($variant_result && $variant_result->num_rows > 0): ?>
                <?php while ($variant = $variant_result->fetch_assoc()): ?>
                    <div class="variant-item">
                        <div class="variant-colour">
                            <span
                                class="colour-dot"
                                style="background:<?= htmlspecialchars($variant['color_code'] ?: '#ddd') ?>;"
                            ></span>
                            <div>
                                <strong><?= htmlspecialchars($variant['color_name']) ?></strong>
                                <small><?= htmlspecialchars($variant['product_name']) ?></small>
                            </div>
                        </div>
                        <div class="variant-meta">
                            <span>Sizes: <?= htmlspecialchars($variant['sizes'] ?: 'Not set') ?></span>
                            <span>Stock: <?= (int)$variant['stock'] ?></span>
                        </div>
                        <form method="POST" onsubmit="return confirm('Delete this colour variant?');">
                            <input type="hidden" name="action" value="variant_delete">
                            <input type="hidden" name="variant_id" value="<?= (int)$variant['id'] ?>">
                            <button type="submit" class="delete-btn">🗑 DELETE</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center;color:#777;margin-top:25px;">No colour variants added yet.</p>
            <?php endif; ?>
        </div>
    </div>

    </div>

</div>

\n<script>\n/* Compress large product/variant images before sending them to PHP. */\n(function () {\n    const forms = document.querySelectorAll('form[enctype="multipart/form-data"]');\n    if (!forms.length) return;\n\n    function compressImage(file) {\n        if (!file || !file.type.startsWith('image/') || file.size <= 1400 * 1024) {\n            return Promise.resolve(file);\n        }\n\n        return new Promise(function(resolve) {\n            const img = new Image();\n            const url = URL.createObjectURL(file);\n\n            img.onload = function() {\n                URL.revokeObjectURL(url);\n                const maxSide = 1600;\n                const scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight));\n                const canvas = document.createElement('canvas');\n                canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));\n                canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));\n                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);\n\n                let quality = 0.82;\n                function makeBlob() {\n                    canvas.toBlob(function(blob) {\n                        if (!blob) return resolve(file);\n                        if (blob.size > 1400 * 1024 && quality > 0.42) {\n                            quality -= 0.08;\n                            makeBlob();\n                            return;\n                        }\n                        const newName = file.name.replace(/\\.[^.]+$/, '') + '.jpg';\n                        resolve(new File([blob], newName, {type:'image/jpeg', lastModified:Date.now()}));\n                    }, 'image/jpeg', quality);\n                }\n                makeBlob();\n            };\n\n            img.onerror = function() {\n                URL.revokeObjectURL(url);\n                resolve(file);\n            };\n            img.src = url;\n        });\n    }\n\n    forms.forEach(function(form) {\n        form.addEventListener('submit', async function(event) {\n            if (form.dataset.imagePrepared === '1') return;\n            const inputs = Array.from(form.querySelectorAll('input[type="file"]'));\n            if (!inputs.some(i => i.files && i.files.length)) return;\n\n            event.preventDefault();\n            for (const input of inputs) {\n                if (input.files && input.files[0]) {\n                    const compressed = await compressImage(input.files[0]);\n                    const dt = new DataTransfer();\n                    dt.items.add(compressed);\n                    input.files = dt.files;\n                }\n            }\n            form.dataset.imagePrepared = '1';\n            HTMLFormElement.prototype.submit.call(form);\n        });\n    });\n})();\n</script>\n
</body>

</html>
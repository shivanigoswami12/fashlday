<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

   $product_id = (int)($_POST["product_id"] ?? 0);
$variant_id = (int)($_POST["variant_id"] ?? 0);
$size = trim($_POST["size"] ?? "");

if ($product_id > 0) {

        // Check if product already exists in this user's cart
    $check = $conn->prepare(
    "SELECT id, quantity
     FROM cart
     WHERE product_id = ?
       AND variant_id = ?
       AND size = ?
       AND user_id = ?
     LIMIT 1"
);

$check->bind_param(
    "iisi",
    $product_id,
    $variant_id,
    $size,
    $user_id
);
      
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            // Product already in this user's cart → increase quantity
            $cart = $result->fetch_assoc();

            $new_quantity = (int)$cart['quantity'] + 1;

            $update = $conn->prepare(
                "UPDATE cart
                 SET quantity = ?
                 WHERE id = ? AND user_id = ?"
            );

            $update->bind_param(
                "iii",
                $new_quantity,
                $cart['id'],
                $user_id
            );

            $update->execute();
            $update->close();

        } else {

            // Product not in this user's cart → add new item
        $insert = $conn->prepare(
    "INSERT INTO cart (user_id, product_id, variant_id, size, quantity)
     VALUES (?, ?, ?, ?, 1)"
);

$insert->bind_param(
    "iiis",
    $user_id,
    $product_id,
    $variant_id,
    $size
);
            $insert->execute();
            $insert->close();
        }

        $check->close();
    }
}

echo "success";
?>
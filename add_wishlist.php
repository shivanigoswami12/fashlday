<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_id = (int)($_POST["product_id"] ?? 0);
    $user_id = (int)$_SESSION["user_id"];

    if ($product_id > 0) {

        $stmt = $conn->prepare(
            "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)"
        );

        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }

        $stmt->close();
        exit;
    }
}

echo "error: invalid product";
?>
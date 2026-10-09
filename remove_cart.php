<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $cart_id = (int)($_POST["cart_id"] ?? 0);

    if ($cart_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM cart WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();

        $stmt->close();
    }
}

header("Location: cart.php");
exit;

?>
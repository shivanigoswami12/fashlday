<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $wishlist_id = (int)($_POST["wishlist_id"] ?? 0);
    $user_id = (int)$_SESSION["user_id"];

    if ($wishlist_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM wishlist WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param("ii", $wishlist_id, $user_id);
        $stmt->execute();

        $stmt->close();
    }
}

header("Location: wishlist.php");
exit;

?>
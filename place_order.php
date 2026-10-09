<?php

session_start();
require_once "config.php";

/* User must be logged in */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* Only POST request allowed */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit;
}


/* CUSTOMER DETAILS */

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

if (
    $name === '' ||
    $phone === '' ||
    $email === '' ||
    $address === '' ||
    $city === '' ||
    $pincode === ''
) {
    die("Please fill all required details.");
}


/* GET CURRENT USER'S CART */

$sql = "
    SELECT
        cart.product_id,
        cart.variant_id,
        cart.size,
        cart.quantity,
        products.price
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
";

$cart_stmt = $conn->prepare($sql);

if (!$cart_stmt) {
    die("Could not prepare cart query.");
}

$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();

$result = $cart_stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $cart_stmt->close();
    die("Your cart is empty.");
}


/* CALCULATE TOTAL */

$grand_total = 0;
$items = [];

while ($row = $result->fetch_assoc()) {

    $item_total = (float)$row['price'] * (int)$row['quantity'];

    $grand_total += $item_total;

    $items[] = $row;
}

$cart_stmt->close();


/* CREATE ORDER */

$stmt = $conn->prepare("
    INSERT INTO orders
    (user_id, customer_name, phone, email, address, city, pincode, total_amount)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Could not prepare order query.");
}

$stmt->bind_param(
    "issssssd",
    $user_id,
    $name,
    $phone,
    $email,
    $address,
    $city,
    $pincode,
    $grand_total
);

if (!$stmt->execute()) {
    $stmt->close();
    die("Could not create order.");
}

$order_id = $stmt->insert_id;

$stmt->close();


/* SAVE ORDER ITEMS */

$item_stmt = $conn->prepare("
    INSERT INTO order_items
    (order_id, product_id, variant_id, size, quantity, price)
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$item_stmt) {
    die("Could not prepare order items query.");
}

foreach ($items as $item) {

    $variant_id = !empty($item['variant_id'])
        ? (int)$item['variant_id']
        : null;

    $size = $item['size'] ?? '';

    $quantity = (int)$item['quantity'];

    $price = (float)$item['price'];

    $item_stmt->bind_param(
        "iiisid",
        $order_id,
        $item['product_id'],
        $variant_id,
        $size,
        $quantity,
        $price
    );

    if (!$item_stmt->execute()) {
        $item_stmt->close();
        die("Could not save order items.");
    }
}

$item_stmt->close();


/* CLEAR ONLY CURRENT USER'S CART */

$clear_stmt = $conn->prepare(
    "DELETE FROM cart WHERE user_id = ?"
);

if ($clear_stmt) {
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
    $clear_stmt->close();
}


/* GO TO CONFIRMATION */

header("Location: order_success.php?order_id=" . $order_id);
exit;

?>
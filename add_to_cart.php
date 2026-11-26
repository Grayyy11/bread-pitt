<?php
// Adds a product to the customer's cart
include_once "includes/db.php";
include_once "includes/auth.php";

// only customers can use the cart
requireRole(["customer"]);

// run only when the button is clicked
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: menu.php");
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$user_id = $_SESSION['user_id'];

// find the product
$stmt = $pdo->prepare("SELECT name, is_available, stock_qty FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['message'] = "Product not found.";
} elseif ($product['is_available'] == 0 || $product['stock_qty'] < 1) {
    $_SESSION['message'] = $product['name'] . " is sold out.";
} else {
    // add it, or add 1 to the quantity if it is already in the cart
    $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity)
                           VALUES (?, ?, 1)
                           ON DUPLICATE KEY UPDATE quantity = quantity + 1");
    $stmt->execute([$user_id, $product_id]);

    logAction($conn, $user_id, "add_to_cart", "Added to cart: " . $product['name']);
    $_SESSION['message'] = $product['name'] . " was added to your cart.";
}

header("Location: menu.php");
exit;
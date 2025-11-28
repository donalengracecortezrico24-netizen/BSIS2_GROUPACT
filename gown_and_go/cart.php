<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Update quantities / remove items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $item_id => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$item_id]);
            } else {
                $_SESSION['cart'][$item_id] = $qty;
            }
        }
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
    }
}

$cart = $_SESSION['cart'];
$items_data = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = $conn->query("SELECT * FROM items WHERE item_id IN ($ids)");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items_data[$row['item_id']] = $row;
        }
    }

    foreach ($cart as $item_id => $qty) {
        if (isset($items_data[$item_id])) {
            $total += $items_data[$item_id]['purchase_price'] * $qty;
        }
    }
}

$cart_count = array_sum($cart);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart - GOWN&GO</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="styles/cart.css" rel="stylesheet"> 
</head>
<body>
  <header class="topbar">
    <div class="logo">GOWN&GO</div>
    <div class="nav-links">
      <span>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <a href="client_home.php">Shop</a>
      <a href="cart.php">Cart (<?php echo $cart_count; ?>)</a>
      <a href="orders.php">My Orders</a>
      <a href="logout.php">Logout</a>
    </div>
  </header>

  <main class="main-container">
    <h2>Your Cart</h2>

    <?php if (empty($cart)): ?>
      <p>Your cart is empty. <a href="client_home.php">Browse items</a>.</p>
    <?php else: ?>
      <form method="POST">
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Price (₱)</th>
              <th>Qty</th>
              <th>Subtotal (₱)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart as $item_id => $qty): ?>
              <?php if (!isset($items_data[$item_id])) continue; ?>
              <?php $item = $items_data[$item_id]; ?>
              <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo number_format($item['purchase_price'], 2); ?></td>
                <td>
                  <input type="number" name="qty[<?php echo $item_id; ?>]" min="0" value="<?php echo (int)$qty; ?>" style="width:60px;">
                </td>
                <td><?php echo number_format($item['purchase_price'] * $qty, 2); ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="total-row">
              <td colspan="3" style="text-align:right;">Total:</td>
              <td>₱<?php echo number_format($total, 2); ?></td>
            </tr>
          </tbody>
        </table>

        <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
        <button type="submit" name="clear_cart" class="btn btn-secondary">Clear Cart</button>
        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>

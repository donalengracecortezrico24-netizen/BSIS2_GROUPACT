<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch customer info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result();
$user = $user_res->fetch_assoc();

// Prepare cart data
$cart = $_SESSION['cart'];
$items_data = [];
$total = 0;

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

$order_error = "";
$order_success = "";

// Handle placing order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $delivery_address = trim($_POST['delivery_address']);
    if ($delivery_address === "") {
        $order_error = "Delivery address is required.";
    } else {
        $conn->begin_transaction();
        try {
            // Insert into orders
            $stmt_order = $conn->prepare("
                INSERT INTO orders (user_id, order_status, order_type, total_amount, delivery_address)
                VALUES (?, 'Pending', 'Purchase', ?, ?)
            ");
            $stmt_order->bind_param("ids", $user_id, $total, $delivery_address);
            $stmt_order->execute();
            $order_id = $stmt_order->insert_id;

            // Insert order details
            $stmt_detail = $conn->prepare("
                INSERT INTO order_details (order_id, item_id, order_type, quantity, rental_period_days, unit_price, subtotal)
                VALUES (?, ?, 'Purchase', ?, NULL, ?, ?)
            ");

            foreach ($cart as $item_id => $qty) {
                if (!isset($items_data[$item_id])) continue;
                $price = $items_data[$item_id]['purchase_price'];
                $subtotal = $price * $qty;

                $stmt_detail->bind_param("iiidd", $order_id, $item_id, $qty, $price, $subtotal);
                $stmt_detail->execute();

                // Reduce stock
                $stmt_stock = $conn->prepare("UPDATE items SET stock = stock - ? WHERE item_id = ?");
                $stmt_stock->bind_param("ii", $qty, $item_id);
                $stmt_stock->execute();
            }

            // Insert payment record
            $stmt_pay = $conn->prepare("
                INSERT INTO payments (order_id, payment_method, payment_status, amount)
                VALUES (?, 'Cash on Delivery', 'Pending', ?)
            ");
            $stmt_pay->bind_param("id", $order_id, $total);
            $stmt_pay->execute();

            $conn->commit();

            $_SESSION['cart'] = [];

            // OPTION C — Success message + Link + Auto Redirect
            $order_success = "
                Order placed successfully!<br><br>
                <a href='invoice.php?order_id={$order_id}' 
                   style='color:#d86ca1;font-weight:bold;'>
                    View Invoice / Receipt
                </a>
                <script>
                    setTimeout(function(){
                        window.location.href = 'invoice.php?order_id={$order_id}';
                    }, 3000);
                </script>
            ";

        } catch (Exception $e) {
            $conn->rollback();
            $order_error = "Failed to place order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - GOWN&GO</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="styles/checkout.css" rel="stylesheet"> 
</head>
<body>
  <header class="topbar">
    <div class="logo">GOWN&GO</div>
    <div class="nav-links">
      <span>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <a href="client_home.php">Shop</a>
      <a href="cart.php">Cart</a>
      <a href="orders.php">My Orders</a>
      <a href="logout.php">Logout</a>
    </div>
  </header>

  <main class="main-container">
    <h2>Order Summary</h2>

    <?php if (!empty($order_error)): ?>
      <div class="message error-message"><?php echo $order_error; ?></div>
    <?php endif; ?>
    <?php if (!empty($order_success)): ?>
      <div class="message success-message"><?php echo $order_success; ?></div>
    <?php endif; ?>

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
            <td><?php echo (int)$qty; ?></td>
            <td><?php echo number_format($item['purchase_price'] * $qty, 2); ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td colspan="3" style="text-align:right;">Total:</td>
          <td>₱<?php echo number_format($total, 2); ?></td>
        </tr>
      </tbody>
    </table>

    <h3>Delivery Details</h3>
    <form method="POST">
      <textarea name="delivery_address" rows="3"><?php
        echo htmlspecialchars($user['address'] ?? '');
      ?></textarea>
      <br>
      <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
      <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
    </form>
  </main>
</body>
</html>

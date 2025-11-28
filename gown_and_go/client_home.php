<?php
session_start();
include 'config.php';

// Require login as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // item_id => quantity
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $item_id  = (int)$_POST['item_id'];
    $quantity = max(1, (int)$_POST['quantity']);

    if (!isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] = 0;
    }
    $_SESSION['cart'][$item_id] += $quantity;

    $message = "Item added to cart!";
}

// Fetch available items
$items = [];
$result = $conn->query("SELECT * FROM items WHERE status = 'Available' AND stock > 0 ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Home - GOWN&GO</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="styles/clienthome.css" rel="stylesheet"> 
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
    <h2 class="welcome">Browse our collection</h2>
    <p class="subtitle">Rent or purchase elegant gowns and formal wear on the go.</p>

    <?php if (!empty($message)): ?>
      <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
      <p>No items available at the moment.</p>
    <?php else: ?>
      <section class="items-grid">
        <?php foreach ($items as $item): ?>
          <article class="item-card">
            <?php if (!empty($item['image'])): ?>
              <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Item image" class="item-image">
            <?php else: ?>
              <div class="item-image"></div>
            <?php endif; ?>
            <div class="item-body">
              <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
              <div class="item-desc"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
              <div class="item-price">
                <div>Purchase: <strong>₱<?php echo number_format($item['purchase_price'], 2); ?></strong></div>
                <div>Rental: <strong>₱<?php echo number_format($item['rental_price'], 2); ?></strong></div>
              </div>
              <div class="item-stock">In stock: <?php echo (int)$item['stock']; ?></div>
              <form method="POST" class="item-form">
                <input type="hidden" name="item_id" value="<?php echo (int)$item['item_id']; ?>">
                <input type="number" name="quantity" min="1" max="<?php echo (int)$item['stock']; ?>" value="1">
                <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>

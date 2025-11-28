<?php
session_start();
include '../config.php';

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ------------------------------------------------------
   SUMMARY STATISTICS
------------------------------------------------------ */

// Total customers
$total_customers = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='customer'");
if ($res && $row = $res->fetch_assoc()) $total_customers = $row['c'];

// Total items
$total_items = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM items");
if ($res && $row = $res->fetch_assoc()) $total_items = $row['c'];

// Total orders
$total_orders = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM orders");
if ($res && $row = $res->fetch_assoc()) $total_orders = $row['c'];

// Total revenue (PAID)
$total_revenue = 0;
$res = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total 
    FROM payments 
    WHERE payment_status = 'Paid'
");
if ($res && $row = $res->fetch_assoc()) $total_revenue = $row['total'];

/* ------------------------------------------------------
   NEW! SALES & INVENTORY REPORT
------------------------------------------------------ */

// Pending payments
$pending_payments = 0;
$res = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total 
    FROM payments 
    WHERE payment_status = 'Pending'
");
if ($res && $row = $res->fetch_assoc()) $pending_payments = $row['total'];

// Inventory total value
$inventory_value = 0;
$res = $conn->query("
    SELECT COALESCE(SUM(stock * purchase_price), 0) AS total 
    FROM items
");
if ($res && $row = $res->fetch_assoc()) $inventory_value = $row['total'];

// Out of stock
$out_of_stock = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM items WHERE stock = 0");
if ($res && $row = $res->fetch_assoc()) $out_of_stock = $row['c'];

// Low stock (< 5)
$low_stock = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM items WHERE stock < 5");
if ($res && $row = $res->fetch_assoc()) $low_stock = $row['c'];

/* ------------------------------------------------------
   RECENT ORDERS
------------------------------------------------------ */
$recent_orders = [];
$sql = "
    SELECT o.order_id, o.order_date, o.order_status, o.total_amount, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
    LIMIT 5
";
$res = $conn->query($sql);
if ($res) while ($row = $res->fetch_assoc()) $recent_orders[] = $row;

/* ------------------------------------------------------
   INVENTORY LIST
------------------------------------------------------ */
$items = [];
$res = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
if ($res) while ($row = $res->fetch_assoc()) $items[] = $row;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - GOWN&GO</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="styles/dashboard.css" rel="stylesheet"> 
</head>
<body>

<header class="topbar">
  <div class="logo">GOWN&GO Admin</div>
  <div class="nav-links">
    Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>
    &nbsp; | &nbsp;
    <a href="../index.php">View Site</a>
    &nbsp; | &nbsp;
    <a href="../logout.php">Logout</a>
  </div>
</header>

<main class="main-container">

  <!-- SUMMARY CARDS -->
  <section class="grid">

    <div class="card">
      <h3>Total Customers</h3>
      <div class="value"><?php echo $total_customers; ?></div>
    </div>

    <div class="card">
      <h3>Total Items</h3>
      <div class="value"><?php echo $total_items; ?></div>
    </div>

    <div class="card">
      <h3>Total Orders</h3>
      <div class="value"><?php echo $total_orders; ?></div>
    </div>

    <div class="card">
      <h3>Total Revenue (Paid)</h3>
      <div class="value">₱<?php echo number_format($total_revenue, 2); ?></div>
    </div>

    <div class="card">
      <h3>Pending Payments</h3>
      <div class="value">₱<?php echo number_format($pending_payments, 2); ?></div>
    </div>

    <div class="card">
      <h3>Inventory Value</h3>
      <div class="value">₱<?php echo number_format($inventory_value, 2); ?></div>
    </div>

    <div class="card">
      <h3>Out of Stock</h3>
      <div class="value"><?php echo $out_of_stock; ?></div>
    </div>

    <div class="card">
      <h3>Low Stock</h3>
      <div class="value"><?php echo $low_stock; ?></div>
    </div>

  </section>

  <!-- RECENT ORDERS -->
  <h2>Recent Orders</h2>
  <table>
    <thead>
      <tr>
        <th>Order #</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Status</th>
        <th>Total (₱)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recent_orders as $o): ?>
      <tr>
        <td>#<?php echo $o['order_id']; ?></td>
        <td><?php echo htmlspecialchars($o['username']); ?></td>
        <td><?php echo $o['order_date']; ?></td>
        <td><?php echo $o['order_status']; ?></td>
        <td><?php echo number_format($o['total_amount'], 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- INVENTORY -->
  <h2>Inventory Overview</h2>
  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th>Stock</th>
        <th>Purchase Price</th>
        <th>Rental Price</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i): ?>
      <tr>
        <td><?php echo htmlspecialchars($i['name']); ?></td>
        <td>
          <?php echo $i['stock']; ?>
          <?php if ($i['stock'] <= 2): ?>
            <span class="badge-low">Low</span>
          <?php endif; ?>
        </td>
        <td>₱<?php echo number_format($i['purchase_price'], 2); ?></td>
        <td>₱<?php echo number_format($i['rental_price'], 2); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</main>
</body>
</html>

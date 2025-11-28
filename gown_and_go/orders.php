<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        o.order_id,
        o.order_date,
        o.order_status,
        o.total_amount,
        GROUP_CONCAT(CONCAT(i.name, ' (x', od.quantity, ')') SEPARATOR ', ') AS items
    FROM orders o
    JOIN order_details od ON o.order_id = od.order_id
    JOIN items i ON od.item_id = i.item_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders - GOWN&GO</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="styles/orders.css" rel="stylesheet"> 
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
<h2>My Orders</h2>

<?php if (empty($orders)): ?>
    <p>You have no orders yet.</p>
<?php else: ?>

<table>
<thead>
<tr>
    <th>Order #</th>
    <th>Date</th>
    <th>Status</th>
    <th>Items</th>
    <th>Total (₱)</th>
    <th>Invoice</th>
</tr>
</thead>

<tbody>
<?php foreach ($orders as $o): ?>
<tr>
    <td>#<?php echo $o['order_id']; ?></td>
    <td><?php echo $o['order_date']; ?></td>
    <td><?php echo $o['order_status']; ?></td>
    <td><?php echo $o['items']; ?></td>
    <td><?php echo number_format($o['total_amount'], 2); ?></td>
    <td><a href="invoice.php?order_id=<?php echo $o['order_id']; ?>">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>
</main>

</body>
</html>
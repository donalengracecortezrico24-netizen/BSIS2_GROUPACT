<?php
session_start();
include 'config.php';

// Fetch ALL items
$items_q = mysqli_query($conn, "
    SELECT item_id, name, description, rental_price, purchase_price, stock, image
    FROM items
    ORDER BY created_at DESC
");

// Fetch first 3 featured products
$featured_q = mysqli_query($conn, "
    SELECT item_id, name, description, rental_price, purchase_price, image
    FROM items
    ORDER BY created_at DESC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GOWN&GO - Shop</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link href="styles/shop.css" rel="stylesheet"> 

</head>
<body>

<header>
    <h1>GOWN&GO</h1>
    <nav>
        <a href="index.php">Home</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin/dashboard.php">Admin Panel</a>
            <?php else: ?>
                <a href="client_home.php">Client Home</a>
            <?php endif; ?>
            <a href="logout.php" style="color:#b3548a;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<!-- FEATURED PRODUCTS -->
<h2 class="section-title">Featured Gowns</h2>

<div class="featured-container">
<?php while($f = mysqli_fetch_assoc($featured_q)): ?>
    <div class="featured-card">
        <?php
        $img = (!empty($f['image'])) ? "uploads/" . $f['image'] :
        "https://via.placeholder.com/300x260?text=No+Image";
        ?>
        <img src="<?php echo $img; ?>">

        <h4><?php echo htmlspecialchars($f['name']); ?></h4>

        <p><?php echo substr(htmlspecialchars($f['description']), 0, 80) . "..."; ?></p>

        <a href="view_item.php?id=<?php echo $f['item_id']; ?>" class="cta">See Details</a>
    </div>
<?php endwhile; ?>
</div>

<!-- ABOUT THE PROJECT -->
<div class="info-section">
    <h3>About Gown&Go</h3>
    <p>
        Gown&Go is a simple and user-friendly rental and purchase platform dedicated to making
        formal wear accessible to everyone. Whether you're attending a wedding, a prom,
        a graduation, or a special occasion, our system ensures a smooth browsing and ordering experience.
    </p>
</div>

<!-- MEET THE TEAM -->
<div class="info-section">
    <h3>Meet the Team</h3>
    <p>Members of the Group IDK</p>

    <div class="team-grid">
        <div class="team-card">
            <h4>Batalla, Francheska Faith</h4>
        </div>
        <div class="team-card">
            <h4>Juarez, Annaliza</h4>
        </div>
        <div class="team-card">
            <h4>Lozano, Eunice</h4>
        </div>
        <div class="team-card">
            <h4>Rico, Donalen Grace</h4>
        </div>
    </div>
</div>

<footer>
    © <?php echo date("Y"); ?> GOWN&GO — Fashion for All.
</footer>

</body>
</html>

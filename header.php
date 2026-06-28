<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = count($_SESSION['cart'] ?? []);
?>
<header class="site-header">
    <div class="header-brand">
        <img src="images/logo.jpg" alt="SimpleShop Logo" class="site-logo">
    </div>

    <nav class="site-nav">
        <a href="home.php">Home</a>

        <a href="cart.php" class="cart-link">
            Cart <span class="cart-count">(<?php echo $cartCount; ?>)</span>
        </a>

        <a href="about.php">About Us</a>
        <a href="orders.php">Orders</a>

        <form action="user/logout.php" method="post" class="logout-form">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>
</header>
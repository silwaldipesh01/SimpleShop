<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

$cartCount = count($_SESSION['cart'] ?? []);

$firstName = "Guest";

if (isset($_SESSION['user'])) {

    $stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $firstName = explode(' ', trim($user['name']))[0];
    }
}
?>

<header class="site-header">
    <div class="header-brand">
        <img src="images/logo.jpg" alt="SimpleShop Logo" class="site-logo">
        <div class="welcome-user">
            Welcome, <?php echo htmlspecialchars($firstName); ?>
        </div>
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
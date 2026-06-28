<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: /SimpleShop/user/auth.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart
if (isset($_POST['add'])) {
    $id = (int)$_POST['product_id'];

    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['stock'] > 0) {
        $_SESSION['cart'][] = [
            "product_id" => $row['product_id'],
            "name" => $row['name'],
            "price" => $row['price'],
            "quantity" => 1,
            "stock" => $row['stock'],
            "category" => $row['category']
        ];
        $_SESSION['flash'] = "Product added to cart successfully!";
    } else {
        $_SESSION['lowstock'] = "Product is out of Stock";
    }

    header("Location: home.php");
    exit;
}

// Filtering and sorting
$category = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'none';

$sql = "SELECT * FROM products";
$params = [];
$where = [];

if ($category !== 'all' && $category !== '') {
    $where[] = "category = ?";
    $params[] = $category;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

if ($sort === 'asc') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort === 'desc') {
    $sql .= " ORDER BY price DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Shop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
</head>
<body>
<div class="page-wrapper">
    <?php include 'header.php'; ?>

    <main>
        <!-- Banner Slider -->
        <div class="swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="images/banner1.jpg" alt="Banner 1"></div>
                <div class="swiper-slide"><img src="images/banner2.jpg" alt="Banner 2"></div>
                <div class="swiper-slide"><img src="images/banner3.jpg" alt="Banner 3"></div>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

        <?php
        if (isset($_SESSION['flash'])) {
            echo "<div class='popup-message'>" . htmlspecialchars($_SESSION['flash']) . "</div>";
            unset($_SESSION['flash']);
        }

        if (isset($_SESSION['lowstock'])) {
            echo "<div class='popup-message error'>" . htmlspecialchars($_SESSION['lowstock']) . "</div>";
            unset($_SESSION['lowstock']);
        }
        ?>

        <!-- Filter Form -->
        <form method="get" class="filter-form">
            <select name="category">
                <option value="all" <?php if ($category === 'all') echo 'selected'; ?>>All</option>
                <option value="Top" <?php if ($category === 'Top') echo 'selected'; ?>>Top</option>
                <option value="Bottom" <?php if ($category === 'Bottom') echo 'selected'; ?>>Bottom</option>
                <option value="Shoe" <?php if ($category === 'Shoe') echo 'selected'; ?>>Shoe</option>
                <option value="Accessories" <?php if ($category === 'Accessories') echo 'selected'; ?>>Accessories</option>
            </select>

            <select name="sort">
                <option value="none" <?php if ($sort === 'none') echo 'selected'; ?>>Sort by Price</option>
                <option value="asc" <?php if ($sort === 'asc') echo 'selected'; ?>>Ascending</option>
                <option value="desc" <?php if ($sort === 'desc') echo 'selected'; ?>>Descending</option>
            </select>

            <button type="submit">Apply</button>
        </form>

        <!-- Products -->
        <div class="products">
            <?php if ($products): ?>
                <?php foreach ($products as $p): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="card-img">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p>$<?php echo number_format((float)$p['price'], 2); ?></p>
                            <p class="stock <?php echo ((int)$p['stock'] > 0) ? 'in-stock' : 'out-stock'; ?>">
                                Stock: <?php echo (int)$p['stock']; ?>
                            </p>

                            <?php if ((int)$p['stock'] > 0): ?>
                                <form action="home.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$p['product_id']; ?>">
                                    <button type="submit" name="add">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
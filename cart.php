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

// Remove item from cart
if (isset($_POST['delete'])) {
    $index = (int)$_POST['index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $_SESSION['flash'] = "Item removed from cart.";
    }
    header("Location: cart.php");
    exit;
}

// Checkout
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $_SESSION['flash'] = "Cart is empty. Please add items before checkout!";
        header("Location: cart.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Re-check stock for every item
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
            $stmt->execute([$item['product_id']]);
            $stockRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $qty = $item['quantity'] ?? 1;
            if (!$stockRow || (int)$stockRow['stock'] < $qty) {
                throw new Exception("Insufficient stock for {$item['name']}.");
            }
        }

        // Create order ID
        $orderId = uniqid('ORD-');

        // Insert into orders
        $stmt = $pdo->prepare("INSERT INTO orders (order_id, user_id) VALUES (?, ?)");
        $stmt->execute([$orderId, $_SESSION['user']]);

        // Insert items and update stock
        foreach ($_SESSION['cart'] as $item) {
            $qty = $item['quantity'] ?? 1;

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $qty]);

            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $stmt->execute([$qty, $item['product_id']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];
        $_SESSION['flash'] = "Order successfully placed! Thank you.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash'] = "Checkout failed: " . $e->getMessage();
    }

    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - SimpleShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-wrapper">
    <?php include 'header.php'; ?>

    <main class="cart-container">
        <h1>Your Cart</h1>

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_SESSION['flash']); ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="cart-items">
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $i => $item):
                    $quantity = $item['quantity'] ?? 1;
                    $subtotal = $item['price'] * $quantity;
                    $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <div class="cart-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>$<?php echo number_format((float)$item['price'], 2); ?> x <?php echo (int)$quantity; ?></p>
                            <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal, 2); ?></p>
                        </div>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="index" value="<?php echo $i; ?>">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
                <form method="post">
                    <button type="submit" name="checkout" class="btn-checkout">Checkout</button>
                </form>
            </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</div>
</body>
</html>
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_date,
        p.name AS product_name,
        p.price,
        oi.quantity
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$_SESSION['user']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupedOrders = [];
foreach ($rows as $row) {
    $groupedOrders[$row['order_id']]['order_date'] = $row['order_date'];
    $groupedOrders[$row['order_id']]['items'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - SimpleShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-wrapper">
    <?php include 'header.php'; ?>

    <main class="orders-page">
        <h1>Your Orders</h1>

        <?php if (empty($groupedOrders)): ?>
            <p class="message">No orders found.</p>
        <?php else: ?>
            <?php foreach ($groupedOrders as $orderId => $order): ?>
                <section class="order-block">
                    <h2>Order ID: <?php echo htmlspecialchars($orderId); ?></h2>
                    <p>Transaction Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                    <p class="delivery-countdown" data-delivery="<?php echo date('Y-m-d H:i:s', strtotime($order['order_date'] . ' +3 days')); ?>"></p>

                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</div>
<script src="script.js"></script>
</body>
</html>
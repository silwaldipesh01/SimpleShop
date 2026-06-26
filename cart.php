<?php
  // Start the PHP session so we can store and access cart data across pages.
  session_start(); 

  // Require database connection
  require 'db.php';

  // Session check
  if (!isset($_SESSION['user'])) {
      // No session? Try restoring via auth.php
      header("Location: /SimpleShop/user/auth.php");
      exit;
  }  

  // --- Initialize cart ---
  // If the cart doesn't exist yet in the session, create an empty array for it.
  if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

  // --- Delete product ---
  // If the "delete" button was clicked on the cart page:
  // --- Delete product ---
  if (isset($_POST['delete'])) {
      $index = (int)$_POST['index'];
      if (isset($_SESSION['cart'][$index])) {
          // Remove the product at the given index from the cart array.
          unset($_SESSION['cart'][$index]);
          // Re-index the array so the keys are sequential again.
          $_SESSION['cart'] = array_values($_SESSION['cart']);
          $_SESSION['flash'] = "Item removed from cart.";
      }
      header("Location: cart.php");
      exit;
  }
 
  // --- Checkout ---
  // If the "checkout" button was clicked:
  if (isset($_POST['checkout'])) {
    // If the cart is empty, show an error message.
    if (empty($_SESSION['cart'])) {
        $message = "<div class='message error'>Cart is empty. Please add items before checkout!</div>";
    } else {
      // Adding Commit and Roll-back
      try {
        // Begin transaction
        $pdo->beginTransaction();

        // Generate a unique order_id (UUID style)
        $orderId = uniqid('ORD-'); // e.g. ORD-6670f1a2c3d4e
          
        // Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (order_id, user_id) VALUES (?, ?)");
        $stmt->execute([$orderId, $_SESSION['user']]);

        // Insert each cart item into order_items
        foreach ($_SESSION['cart'] as $item) {
          $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
          $stmt->execute([$orderId, $item['product_id'], $item['quantity'] ?? 1]);

          // Update product stock
          $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
          $stmt->execute([$item['quantity'] ?? 1, $item['product_id']]);
        }     

        // Commit transaction
        $pdo->commit();

        // Clear cart
        $_SESSION['cart'] = [];
        $_SESSION['flash'] = "Order successfully placed! Thank you.";

      } catch (Exception $e) {
        // Rollback transaction if anything in the try block fails
        $pdo->rollBack();
        $_SESSION['flash'] = "Checkout failed: " . $e->getMessage();
      }
    }
    
    header("Location: cart.php");
    exit;
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <!-- Link to the CSS file for styling -->
    <link rel="stylesheet" href="style.css">
  </head>
 
  <body>
    <div class="page-wrapper">
      <!-- Navigation bar with links back to home and cart -->
      <nav>
        <a href="home.php">Home</a>
        <!-- Link to the cart page. If cart has items it will get alert class else empty -->
        <a href="cart.php" id="cart-link" class="<?= !empty($_SESSION['cart'])?'alert':''?>">
          View Cart 
          <?= '('.count($_SESSION['cart']).')'?> <!-- Count of items -->
        </a>
      </nav>

      <section class="cart-container">
        <h1>Your Cart</h1>

        <!-- If the cart is not empty, display the items -->
        <?php if (!empty($_SESSION['cart'])): ?>
        <div class="cart-items">
          <?php 
            // Initialize total cost variable
            $total = 0;
            // Loop through each item in the cart
            foreach ($_SESSION['cart'] as $i => $item):
              // Add the item price to the total 
              $quantity = $item['quantity'] ?? 1;
              $total += $item['price'] * $quantity;
          ?>
            <div class="cart-item">
              <div class="cart-details">
                <!-- Display product name and price -->
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p>$<?= number_format($item['price'], 2) ?> (x<?= $quantity ?>)</p>
              </div>
              <!-- Delete button for this item -->
              <form method="post" style="display:inline;">
                <!-- Hidden input stores the index of the item to delete -->
                <input type="hidden" name="index" value="<?= $i ?>">
                <button type="submit" name="delete">Delete</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Cart summary section -->
        <div class="cart-summary">
          <!-- Display total cost -->
          <p><strong>Total: $<?= $total ?></strong></p>
          <!-- Checkout button -->
          <form method="post">
            <button type="submit" name="checkout" class="btn-checkout">Checkout</button>
          </form>
        </div>

        <!-- If the cart is empty, show a message -->
        <?php else: ?>
          <p>Your cart is empty.</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="message">
          <?= htmlspecialchars($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
      <?php endif; ?>
    </section>
  </body>
</html>

<?php 
  // Start a PHP session. This allows us to store data 
  // across different pages while the user browses.
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
  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }
  
  // --- Add product ---
  // If the "add" button was clicked on index.php, add that product to the cart.
  if (isset($_POST['add'])) {
    $id = (int)$_POST['product_id'];
    
    // Fetch product details securely from DB
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['stock'] > 0) {
      // Each product is stored as an associative array with name and price.
      $_SESSION['cart'][] = [
        "product_id" => $row['product_id'],
        "name" => $row['name'], 
        "price" => $row['price'], 
        "stock" => $row['stock'], 
        "category" => $row['category']];
      // Store a temporary flag/message in session
      $_SESSION['flash'] = "Product added to cart successfully!";
    } else {
      $_SESSION['lowstock'] = "Product is out of Stock";
    }
    
    header("Location: home.php");
    exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8"> <!-- Set character encoding so text displays correctly -->
    <title>Simple Shop</title> <!-- Title shown in browser tab -->
    
    <!-- Link to our custom stylesheet for layout and design -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Link to Swiper’s CSS (from CDN) so the slider has default styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  </head>
  <body>
    <!-- Navigation Bar -->
    <nav>
      <!-- Link back to the homepage -->
      <a href="index.php">Home</a>
      <!-- Link to the cart page. If cart has items it will get alert class else empty -->
      <a href="cart.php" id="cart-link" class="<?= !empty($_SESSION['cart'])?'alert':''?>">
        View Cart 
        <?= '('.count($_SESSION['cart']).')'?> <!-- Count of items -->
      </a>
    </nav>
    
    <!-- Banner Slider -->
    <div class="swiper"> <!-- Swiper container -->
      <div class="swiper-wrapper"> <!-- Holds all slides -->
        <!-- Each slide contains an image -->
        <div class="swiper-slide"><img src="images/banner1.jpg" alt="Banner 1"></div>
        <div class="swiper-slide"><img src="images/banner2.jpg" alt="Banner 2"></div>
        <div class="swiper-slide"><img src="images/banner3.jpg" alt="Banner 3"></div>
      </div>
      
      <!-- Pagination dots (appear below the slider) -->
      <div class="swiper-pagination"></div>
      
      <!-- Navigation arrows (left/right) -->
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>

    <!-- Flash Popup Message-->
    <?php
      if (isset($_SESSION['flash'])) {
        echo "<div class='popup-message'>{$_SESSION['flash']}</div>";
        unset($_SESSION['flash']);
      }

      if (isset($_SESSION['lowstock'])) {
        echo "<div class='popup-message error'>{$_SESSION['lowstock']}</div>";
        unset($_SESSION['lowstock']);
      }

    ?>

    <!-- Product Cards Section -->
    <div class="products">
      <?php
        // Replace array with database fetch
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
          foreach ($products as $p) {
            echo "<div class='card'>
                    <img src='{$p['image']}' alt='{$p['name']}' class='card-img'>
                    <div class='card-content'>
                      <h3>\${$p['price']}</h3>
                      <p>{$p['name']}</p>
                      <form action='home.php' method='post'>
                        <input type='hidden' name='product_id' value='{$p['product_id']}'>
                        <button type='submit' name='add'>Add to Cart</button>
                      </form>
                    </div>
                  </div>";
          }
        } else {
            echo "<p>No products found.</p>";
        }
      ?>
    </div>

    <!-- Swiper JS (from CDN) provides slider functionality -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Our custom script.js file initializes Swiper and handles cart link styling -->
    <script src="script.js"></script>
  </body>
</html>

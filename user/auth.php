<?php
     // Start a PHP session. This allows us to store data (like cart items) 
    // across different pages while the user browses.
    session_start(); 

    // Require database connection
    require '../db.php';

    // Step 1: If session is already set, then redirect to home.php.
    try{
        if (isset($_SESSION['user'])) {
            // Preload user fields
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user']]);
            $state = $stmt->fetch();

            if ($state) {
                $_SESSION['name'] = $state['name'];
                $_SESSION['email'] = $state['email'];
            }

            $_SESSION['allow_logout'] = true;
            header("Location: /SimpleShop/home.php");
            exit;
        }
    } catch (Exception $e) {
        // Store the error message to display on index.php
        $_SESSION['auth_error'] = $e->getMessage();
        // Clear session variables to prevent partial login
        unset($_SESSION['user']);
        // Redirect to index
        header("Location: /SimpleShop/index.php");
        exit;
    }

    // Step 2: If session is not set, check for a valid cookie to restore session
    try{
        if (isset($_COOKIE['remember_token'])) {
            $token = trim($_COOKIE['remember_token']); // Sanitize token from cookie
            
            // Clean up expired tokens
            $pdo->prepare("DELETE FROM user_tokens WHERE expires_at < NOW()")->execute();

            // Look up token in user_tokens table
            $stmt = $pdo->prepare("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $match = $stmt->fetch();

            // Restore session
            if ($match) {
                $_SESSION['user'] = $match['user_id'];
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$_SESSION['user']]);
                $state = $stmt->fetch();
                
                if ($state) {
                    $_SESSION['name'] = $state['name'];
                    $_SESSION['email'] = $state['email'];
                }
                $_SESSION['allow_logout'] = true;
                
                // Redirect to home
                header("Location: /SimpleShop/home.php");
                exit;
            }
        }
    } catch (Exception $e) {
        // Store the error message to display on index.php
        $_SESSION['auth_error'] = $e->getMessage();
        // Clear session variables to prevent partial login
        unset($_SESSION['user']);
        setcookie('remember_token', '', time() - 3600, "/");
        // Redirect to index
        header("Location: /SimpleShop/index.php");
        exit;
    }

    // Step 3: If session and token both fail, redirect to index.php   
    // Display a forbidden access message with image and link
    echo '
        <div style="text-align: center;">
            <h1>Forbidden Access Auth.</h1><br>    
            <img src="/SimpleShop/user/forbidden.jpg" alt="Forbidden Access Auth" style="max-width: 300px; margin-bottom: 10px;">
            <br><br>
            <a href="/SimpleShop/index.php">Go to Home Page</a>
        </div>
    ';
    // Terminate script execution
    exit;
?>
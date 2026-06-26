<?php
    // Start a PHP session. This allows us to store data (like cart items) 
    // across different pages while the user browses.
    session_start(); 

    // Require database connection
    require '../db.php';

    // 2. Block direct access via browser or GET request
    if (!isset($_SESSION['allow_logout']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
        // Display a forbidden access message with image and link
        echo '
            <div style="text-align: center;">
                <h1>Forbidden Access.</h1>
                <br>    
                <img src="/SimpleShop/user/forbidden.jpg" alt="Forbidden Access" style="max-width: 300px; margin-bottom: 10px;">
                <br> <br>
                <a href="/SimpleShop/index.php">Go to Login/Signup Page</a>
            </div>
        ';
        // Clear the session flag to prevent reuse
        unset($_SESSION['allow_logout']);
        // Terminate script execution
        exit;
    }

    // 3. Handle logout: Proceed only if the form was submitted via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Step 1: Remove token from database if cookie is present
            if (isset($_COOKIE['remember_token'])) {
                $token = trim($_COOKIE['remember_token']);
                // Delete token from user_tokens table
                $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token = ?");
                $stmt->execute([$token]);
                // Expire the cookie
                setcookie('remember_token', '', time() - 3600, "/");
            }

            // Step 2: Remove all tokens for this user (logout from all devices)
            if (isset($_SESSION['user'])) {
                $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE user_id = ?");
                $stmt->execute([$_SESSION['user']]);
            }
            
            // Step 3: Remove session cookie from the browser 
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }            

            // Step 4: Clear session variables
            unset($_SESSION['allow_logout']);
            session_unset();
            session_destroy();        
            
            // Step 5: Redirect to login screen setting the logout sucesss message        
            header("Location: /SimpleShop/index.php?logout_success=1");
            exit;

        } catch (Exception $e) {
            // Redirect back to index.php if any exception catched by the system
            header("Location: /SimpleShop/index.php?logout_error=1");
            exit;
        }
    }
?>
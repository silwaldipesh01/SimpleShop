<?php
    // Start a PHP session. This allows us to store data (like cart items) 
    // across different pages while the user browses.
    session_start(); 

    // Require database connection
    require '../db.php';

    // 2. Block direct access via browser or GET request
    if (!isset($_SESSION['allow_login']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
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
        unset($_SESSION['allow_login']);
        // Terminate script execution
        exit;
    }
    
    // 3. Handle login form: Proceed only if the form was submitted via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Retrieve and sanitize form inputs
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $remember = $_POST['remember'] ?? ''; // "Remember Me" checkbox

            // Ensure both email and password are provided
            if (!$email || !$password) {
                throw new Exception("Both email and password are required.");
            }

            // Step 1: Fetch user info from database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Step 2: Check if email matches
            if (!$user) {
                throw new Exception("Email not found. Make sure you have signed up!");
            }
            // Step 3: Verify password
            if (!password_verify($password, $user['password_hash'])) {
                throw new Exception("Incorrect password.");
            }

            // Step 4: Check confirmation status
            if (!$user['confirm_status']) {
                $_SESSION['allow_confirm'] = true;
                $_SESSION['confirm_error'] = "Check your email for confirmation code or resend code again.";
                header("Location: /SimpleShop/user/confirm.php");
                exit;
            }           
            
            // Step 5: Handle "Remember Me" token
            if ($remember === 'yes') {
                $token = bin2hex(random_bytes(32)); // 64-char token
                $expiryUnix = time() + (86400 * 7); // Unix timestamp for cookie
                $expirySQL  = date('Y-m-d H:i:s', $expiryUnix); // SQL DATETIME for DB

                setcookie('remember_token', $token, $expiryUnix, "/");
                // Remove old tokens for this user
                $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);

                // Insert new token
                $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['user_id'], $token, $expirySQL]);
            }
            
            // Step 6: Finalize login

            // Optional: Set success message for display on portal
            $_SESSION['login_success'] = "Login successful!";           
            // Clear login session flag before redirection           
            unset($_SESSION['allow_login']);
            // Set logout flag to allow access to logout module
            $_SESSION['allow_logout'] = true;
            
            // Set the user in session
            $_SESSION['user'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to home page on success
            header("Location: /SimpleShop/home.php");
            exit;

        } catch (Exception $e) {           
            // Store the error message to display on index.php
            $_SESSION['login_error'] = $e->getMessage();
            
            // Clear session variables to prevent partial login
            unset($_SESSION['user']);
            unset($_SESSION['allow_logout']);
            
            // Redirect back to index.php if any exception catched by the system
            header("Location: /SimpleShop/index.php");
            exit;
        }
    }
?>
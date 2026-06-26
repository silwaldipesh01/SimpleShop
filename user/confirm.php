<?php
    // Normalize timezone across all scripts
    date_default_timezone_set('Asia/Kathmandu');

    // Start a PHP session. This allows us to store data (like cart items) 
    // across different pages while the user browses.
    session_start(); 

    // Require database connection
    require '../db.php';

    // 2. Block direct access via browser or GET request
    if (!isset($_SESSION["allow_confirm"])) {
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
        // Note: Do not clear the session flag yet to allow retry if needed
        // Terminate script execution
        exit;
    }

    // 3. Retrieve messages from session variables

    // Retrieve success message from signup
    $signupSuccess = $_SESSION['signup_success'] ?? '';
    // Clear the message after use to prevent repetition
    unset($_SESSION['signup_success']);

    // Retrieve error message from previous confirmation attempt
    $confirmError = $_SESSION['confirm_error'] ?? '';
    // Clear the error message after use
    unset($_SESSION['confirm_error']);

    // 4. Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Sanitize and validate input
            $email = strtolower(trim($_POST['email'] ?? ''));
            $code = trim($_POST['code'] ?? '');
            $action = $_POST['action'] ?? 'confirm';                      
            
            // Action if resend button was clicked
            if ($action === 'resend') {
                // Ensure if email address is filled
                if (!$email) {
                    throw new Exception("Email is required to resend the code.");
                }

                // Step 1: Lookup user_id for the email in database 
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    throw new Exception("No account found with that email. Please signup!");
                }
                $userId = $user['user_id'];

                // Step 2: Throttle resend attempts (1 minute cooldown)
                $stmt = $pdo->prepare("SELECT created_at FROM confirm_codes WHERE user_id = ?");
                $stmt->execute([$userId]);
                $existing = $stmt->fetch();

                if ($existing && strtotime($existing['created_at']) > time() - 60) {
                    throw new Exception("Please wait a minute before requesting another code.");
                }

                // Step 3: Generate new code
                $newCode = rand(100000, 999999);

                // Step 4: Check if code already exists then update new code and delete old code
                $stmt = $pdo->prepare("SELECT * FROM confirm_codes WHERE user_id = ?");
                $stmt->execute([$userId]);

                if ($stmt->fetch()) {
                    $stmt = $pdo->prepare("UPDATE confirm_codes SET confirmation_code = ?, created_at = NOW() WHERE user_id = ?");
                    $stmt->execute([$newCode, $userId]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO confirm_codes (user_id, confirmation_code) VALUES (?, ?)");
                    $stmt->execute([$userId, $newCode]);
                }

                // Step 5: (Optinal) Send email
                // mail($email, "Your New Confirmation Code", "Your new code is: $newCode");

                // Step 6: Redirect back to confirm page
                $_SESSION['confirm_error'] = "A new confirmation code has been sent to your email.";
                header("Location: confirm.php");
                exit;

            }else if ($action === 'confirm'){
                // Action if confirm button was clicked
                if (!$email || !$code) {
                    throw new Exception("Both email and confirmation code are required.");
                }

                // Step 1: Lookup user_id for the entered email address in database 
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    throw new Exception("No account found with that email.");
                }
                $userId = $user['user_id'];

                // Step 2: Validate confirmation code
                $stmt = $pdo->prepare("SELECT * FROM confirm_codes WHERE user_id = ? AND confirmation_code = ?");
                $stmt->execute([$userId, $code]);
                $match = $stmt->fetch();
                // If no match found, throw error
                if (!$match) {
                    throw new Exception("Invalid email or confirmation code.");
                }

                // Step 3: Update user confirmation status
                $stmt = $pdo->prepare("UPDATE users SET confirm_status = TRUE WHERE user_id = ?");
                $stmt->execute([$userId]);
            
                // Step 4: Remove confirmation code
                $stmt = $pdo->prepare("DELETE FROM confirm_codes WHERE user_id = ?");
                $stmt->execute([$userId]);
            
                // Step 5: Redirect to login with success message
                $_SESSION['login_success'] = "Account confirmed successfully! You can now log in.";
                // Note: Clear confirmation flag (only when confirmation code matches) to prevent reuse
                unset($_SESSION['allow_confirm']);
                header("Location: /SimpleShop/index.php");
                exit;

            }else {
                throw new Exception("Unknown action.");
            }        

        } catch (Exception $e) {
            // Store the error message to display in confirm.php
            $_SESSION['confirm_error'] = $e->getMessage();
            // Redirect back to confirm.php to render the message
            header("Location: /SimpleShop/user/confirm.php");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Account</title>
  <!-- External CSS for styling -->
  <link rel="stylesheet" href="signup.css">
</head>
<body>
    <!-- Confirmation form -->
    <div class="container">
        <div class="form-section">
            <h2>Confirm Your Account</h2>
                
            <!-- Display success message from signup -->
            <?php if ($signupSuccess): ?>
                <div class="success-message"><?php echo htmlspecialchars($signupSuccess); ?></div>
            <?php endif; ?>

            <!-- Display error message from failed confirmation -->
            <?php if ($confirmError): ?>
                <div class="error-message"><?php echo htmlspecialchars($confirmError); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <!-- Email input -->
                <label>Email: <input type="email" name="email" required></label>
                
                <!-- Confirmation code input -->
                <label>Confirmation Code: <input type="text" name="code"></label>
                
                <!-- Submit button -->
                <button type="submit" name="action" value="confirm">Confirm</button>
                <button type="submit" name="action" value="resend">Resend Code</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    // Start a PHP session. This allows us to store data (like cart items) 
    // across different pages while the user browses.
    session_start(); 

    // Require database connection
    require '../db.php';

    // 2. Block direct access via browser or GET request
    if (!isset($_SESSION['allow_signup'])|| $_SERVER['REQUEST_METHOD'] != 'POST') {
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
        unset($_SESSION['allow_signup']); 
        // Terminate script execution
        exit;
    }
      
    // 3. Extract and sanitize input from form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Generate a unique user ID
            $userId = uniqid("user_");
            // Retrieve and sanitize form inputs
            $name = trim($_POST['name'] ?? '');
            $gender = $_POST['gender'] ?? '';
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $agree = $_POST['agree'] ?? '';
     

            // Remove delimiters and line breaks to prevent file format corruption (e.g., if someone enters | in their name)
            $name = str_replace(["|", "\n", "\r"], "", $name);
            $gender = str_replace("|", "", $gender);
            $email = str_replace("|", "", $email);
  
            // 4. Basic Validation

            // Check for missing fields
            if (!$name || !$gender || !$email || !$password || !$confirm || !$agree) {
                throw new Exception("All fields must be filled and confirmed.");
            }

            // Validate name format: only letters and spaces allowed
            if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
                throw new Exception("Name must contain only letters and spaces.");
            }            

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format.");
            }
            // Check for duplicate email in database
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email already exists.");
            }

            // Validate password match
            if ($password !== $confirm) {
                throw new Exception("Passwords do not match.");
            }
            // Hash the password securely prior to saving to file
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                     
            // 5. Insert into database
            $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, password_hash, gender, confirm_status)
             VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $name, $email, $hashedPassword, $gender, "false"]);            
            
             // 6. Generate a random 6-digit confirmation code
            $code = rand(100000, 999999);
            
            // Save confirmation code and email to confirm_codes table
            $stmt = $pdo->prepare("INSERT INTO confirm_codes (user_id, confirmation_code) VALUES (?, ?)");
            $stmt->execute([$userId, $code]);

            // 7. Clear any previous signup session data
            unset($_SESSION['signup_data']);
            unset($_SESSION['signup_error']);
            unset($_SESSION['allow_signup']);

            // 8. Send confirmation email 
            //if (!mail($email, "Confirm Your Signup", "Your confirmation code is: $code")) {
            //    throw new Exception("Failed to send confirmation email.");
            //}

            // 9. Set session flags for confirmation flow
            $_SESSION['allow_confirm'] = true;
            // Store succces message to display on confirm.php
            $_SESSION['signup_success'] = "Signup successful! A confirmation code has been sent to your email.";
            
            // 10. Redirect to confirmation page
            header("Location: /SimpleShop/user/confirm.php");
            exit;

        } catch (Exception $e) {
            // Preserve submitted form data in session
            // This allows index.php to repopulate the form fields after redirect
            $_SESSION['signup_data'] = $_POST;

            // Store error message to display on index.php
            $_SESSION['signup_error'] = $e->getMessage();

            // Redirect back to index.php if any exception catched by the system
            header("Location: /SimpleShop/index.php");
            exit;
        }
    }
?>

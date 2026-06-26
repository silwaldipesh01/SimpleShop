<?php  
    // Start a PHP session. This allows us to store data (like cart items) 
    // across different pages while the user browses.
    session_start(); 
   
    // If user is not logged in, allow access to signup and login modules
    if (!isset($_SESSION['user'])) {
      $_SESSION['allow_signup'] = true;
      $_SESSION['allow_login'] = true;
    }
    else{
      // If user is already logged in, redirect to auth.php for session restoration
      header("Location: user/auth.php"); 
      exit;
    }    

    ########################## SIGN-UP FORM ##########################################
    
    // Retrieve preserved signup form data (if user previously submitted with errors)
    $signupData = $_SESSION['signup_data'] ?? [];
    // Retrieve any error message from signup attempt
    $signupError = $_SESSION['signup_error'] ?? '';
    
    // Extract the values entered by users (prior to submitting with errors)
    $name = $signupData['name'] ?? '';
    $email = $signupData['email'] ?? '';
    $gender = $signupData['gender'] ?? '';
    
    // Clear preserved signup data and error message after use
    unset($_SESSION['signup_data']);
    unset($_SESSION['signup_error']);

    ########################## LOGIN FORM ############################################
    
    // Retrieve any error message from login attempt
    $loginError = $_SESSION['login_error'] ?? '';
    // Clear login error after displaying it
    unset($_SESSION['login_error']);

    ########################## CONFIRM FORM ##########################################
    
    // Retrieve success message from account confirmation
    $loginSuccess = $_SESSION['login_success'] ?? '';
    // Clear success message after displaying it
    unset($_SESSION['login_success']);

    ########################## AUTH.PHP ##############################################
    
    // Retrieve error message from auth.php
    $authError = $_SESSION['auth_error'] ?? '';
    // Clear error message after displaying it
    unset($_SESSION['auth_error']);

    ########################## LOGOUT ################################################
    
    // Retrieve logout success message
    $logoutSuccess = isset($_GET['logout_success']) ? 'Account logged out successfully!' : '';
    // Retrieve logout error message
    $logoutError = isset($_GET['logout_error']) ? 'Logout encountered an issue, but your session has been cleared!' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup / Login</title>
  <!-- External CSS for styling -->
  <link rel="stylesheet" href="user/signup.css"> 
  <!-- Responsive layout -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
</head>
<body>
  <div class="container">
    <!-- Image Banner -->
    <div class="image-section">
      <img src="user/banner.jpg" alt="Banner">
    </div>

    <!-- Form -->
    <div class="form-wrapper">
      <div class="form-section">

        <!-- Toggle buttons to switch between signup and login forms -->
        <div class="toggle-buttons">
          <button onclick="showForm('signup')">Sign Up</button>
          <button onclick="showForm('login')">Log In</button>
        </div>

        <!-- Signup Form (initially hidden) -->
        <div id="signup-form" class="hidden">
          <h2>Create Your Account</h2>

          <!-- Display signup error messages if present -->
          <?php if ($signupError): ?>
            <div class="error-message"><?php echo htmlspecialchars($signupError); ?></div>
          <?php endif; ?>
          
          <!-- Display error success messages if present -->
          <?php if (isset($_GET['logout_error'])): ?>
            <div class="error-message" id="logout-msg"><?php echo $logoutError; ?></div>
          <?php endif; ?>

          <form method="POST" action="user/signup.php" enctype="multipart/form-data">
            <!-- Name input -->
            <label for="name">Name:
              <!-- Set value to the last input from user using php-->              
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" autocomplete="name" required>
            </label>

            <!-- Gender selection -->
            <legend>Gender:</legend>
            <div class="radio-group">
              <label for="gender-male">
                <input type="radio" id="gender-male" name="gender" value="Male" <?php if ($gender === 'Male') echo 'checked'; ?> required> Male
              </label>
              <label for="gender-female">
                <input type="radio" id="gender-female" name="gender" value="Female" <?php if ($gender === 'Female') echo 'checked'; ?>> Female
              </label>
              <label for="gender-other">
                <input type="radio" id="gender-other" name="gender" value="Other" <?php if ($gender === 'Other') echo 'checked'; ?>> Other
              </label>
            </div>

            <!-- Email input -->
            <label for="email">Email:
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" autocomplete="email" required>
            </label>

            <!-- Password and confirm password fields -->
            <label for="password">Password:
              <input type="password" id="password" name="password" required>
            </label>
            <label for="confirm_password">Confirm Password:
              <input type="password" id="confirm_password" name="confirm_password" required>
            </label>

            <!-- Agreement checkbox -->
            <label class="checkbox">
              <input type="checkbox" name="agree" value="yes" required>
              I confirm that the details are correct.
            </label>

            <!-- Submit button -->
            <button type="submit">Sign Up</button>
          </form>
        </div>

        <!-- Login form (visible by default) -->
        <div id="login-form">
          <h2>Login to Your Account</h2>

          <!-- Display login error if present -->
          <?php if ($loginError): ?>
            <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
          <?php endif; ?>
          
          <!-- Display login success (confirm) if present -->
          <?php if ($loginSuccess): ?>
            <div class="success-message"><?php echo htmlspecialchars($loginSuccess); ?></div>
          <?php endif; ?>

          <!-- Display logout error messages if present -->
          <?php if (isset($_GET['logout_error'])): ?>
            <div class="error-message" id="logout-msg"><?php echo $logoutError; ?></div>
          <?php endif; ?>

          <!-- Display logout success messages if present -->
          <?php if (isset($_GET['logout_success'])): ?>
            <div class="success-message" id="logout-msg"><?php echo $logoutSuccess; ?></div>
          <?php endif; ?>

          <!-- Display authentication error messages if present -->
          <?php if (isset($_GET['auth_error'])): ?>
            <div class="error-message" id="auth-msg"><?php echo $authError; ?></div>
          <?php endif; ?>

          <form method="POST" action="user/login.php">
            <!-- Email input -->
            <label for="login-email">Email:
              <input type="email" id="login-email" name="email" required autocomplete="email">
            </label>

            <!-- Password input -->
            <label for="login-password">Password:
              <input type="password" id="login-password" name="password" required autocomplete="current-password">
            </label>
            
            <!-- Remember Me checkbox -->
            <label class="checkbox">
              <input type="checkbox" name="remember" value="yes"> Remember Me
            </label>

            <!-- Submit button -->
            <button type="submit">Login</button>
          </form>
        </div>

      </div>  <!-- form-section close -->
    </div>  <!-- form-wrapper close -->
  </div>  <!-- container close -->

  <!-- JavaScript to handle toggle between signup and login forms -->
  <script>
    function showForm(formType) {
      // Hide both forms
      document.getElementById('signup-form').classList.add('hidden');
      document.getElementById('login-form').classList.add('hidden');
      // Show the selected form
      document.getElementById(formType + '-form').classList.remove('hidden');
    }
    
    // Auto-toggle to signup or login form based on error
    window.onload = function() {
      <?php if ($signupError): ?>
        showForm('signup');
      <?php elseif ($loginError): ?>
        showForm('login');
      <?php else: ?>
        showForm('login');
      <?php endif; ?>
    };

    // Auto dismiss logout message after few seconds
    setTimeout(() => {
      const msg = document.getElementById('logout-msg');
      if (msg) msg.style.display = 'none';
    }, 3000); // 3 seconds

  </script>
  
</body>
</html>